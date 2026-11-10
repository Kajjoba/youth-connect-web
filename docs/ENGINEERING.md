# YCP – Engineering decisions and evidence (defence notes)

This document answers the assessment question: *how does the implementation turn the original YCP problem into a secure, usable, reliable, maintainable and scalable web application?* Every claim points to code or an automated test (`tests/run.php`, 131 checks; `tests/crawl.php`, 81 pages).

## 1. Problem → requirements

| Original problem (spreadsheets, email, chat apps, cloud storage) | Root cause | Requirement | Where it lives |
|---|---|---|---|
| Duplicated records, inconsistent information | No single source of truth | One relational database, foreign keys, unique keys | `database/schema.sql` |
| Inappropriate access to information | No central rules for who sees what | Role-based + ownership-based access in one place | `app/Core/Policy.php`, router middleware |
| Poor project coordination | No shared structure for work | Team → project → milestones → tasks with states | `ProjectController`, `projects` tables |
| Hard to manage project evidence | Files scattered | Evidence attached to project and milestone, authorised download | `ProjectController::addEvidence/downloadEvidence`, `Upload` |
| Poor experience on unreliable connections | Heavy pages, no resilience | Low-bandwidth, offline-tolerant design | §4 |
| No recognition of achievements | Manual certificates | Automatic verifiable certificates and badges | `Progress::recalc`, `Recognition::award`, `/certificate/{code}` |

Non-functional requirements and how they are measured: **security** (policy + CSRF + validation tests), **usability** (mobile-first, plain-language errors, Coursera-style journeys), **reliability** (idempotent writes, transactions), **maintainability** (small classes, no framework magic), **scalability** (stateless servers, indexed queries, pagination).

## 2. Architecture and why

Lightweight MVC with a front controller, written without a framework on purpose: the module assesses *engineering decisions*, so each concern (routing, sessions, CSRF, validation, authorisation) is implemented visibly and can be defended. Trade-off: more code to own than with Laravel; benefit: no hidden behaviour, runs on any PHP/MySQL host.

```
Request → public/index.php → Router → CSRF check → middleware (auth/role) → Controller → Policy → DB → View
```

Key choices and the alternatives rejected:

| Decision | Alternative | Why this |
|---|---|---|
| Server-rendered pages + small JS enhancements | SPA with API | Works on low-end phones and slow links; works with JS disabled; far less data than a JS bundle |
| Sessions in MySQL | Files / Redis | Any web node can serve any user (horizontal scale) with no extra service to host |
| Opaque public ids (16 hex) **plus** policy check | Auto-increment ids only | Ids are defence in depth; the policy is the real control |
| One quiz per week, graded server-side, answer key never sent to browser | Client-side grading | Prevents cheating; a forged option id scores zero (tested) |
| Level Two eligibility = completed courses (configurable) or admin override | Manual approval only | Meets "selected participants" and scales to 4,000 users |

## 3. The CAT 2 incident: analysis, fix and evidence

**A. "One member cannot see the updated feedback"** – Typical causes: feedback copied per user and a copy lost, stale browser/proxy cache, or access rules that differ per member.
Fix: feedback is **one row per project** read by every active member; each change bumps `projects.version`; members get an in-app notification; pages revalidate by ETag; open pages poll a tiny pulse and show "New feedback has been shared". Evidence: test section 5 (all four members from four countries see the same text and have a notification).

**B. "Another user changes a project identifier and sees another team's data"** – This is an *insecure direct object reference* (broken access control, OWASP A01).
Fix: `Policy::project()` is the only way controllers load a project; it requires team membership, assigned staff role or admin, and otherwise answers **404** (no confirmation that the project exists). Child objects (task, milestone, evidence) are always queried *with* the project id and cross-references are validated (assignee must be on the team; milestone must belong to the project). Evidence downloads go through the same policy. Evidence: test section 6 (≈30 checks including numeric ids, edited ids, path tricks, APIs, task/milestone/evidence swapping, outsider file download).

**C. "Refreshing the project page on a slow connection consumes excessive data"** – Every refresh re-downloaded the whole page, and users refreshed because they could not tell whether anything changed.
Fix: (1) gzip; (2) strong ETag so an unchanged page is a header-only **304**; (3) a ~80-byte JSON pulse (itself 304 when unchanged) replaces refreshing; (4) polling backs off exponentially, pauses when the tab is hidden, and is disabled in Lite mode / Save-Data; (5) the page tells users when something changed instead of auto-reloading; (6) chat fetches only new messages. Evidence: test section 7 (304 on refresh, pulse < 150 bytes, retry-safe messages).

## 4. Low-bandwidth, mobile and reliability design

Mobile-first CSS (≈25 KB raw, ≈6 KB gzipped), system fonts, no frameworks or CDNs, lazy video (nothing downloads until tapped), Lite mode, `Save-Data` support. Drafts of quizzes, assignments, tasks and project text autosave to the device and are cleared only after the server confirms success; chat messages queue offline and retry with an idempotency key. A service worker caches static assets and serves an offline page; it never caches authenticated pages.
Reliability: transactions for multi-row changes; unique keys make enrolment, certificate issue and messages safe to retry; project state changes use `UPDATE … WHERE status = expected` so double-clicks cannot double-apply; audit log of security-relevant events.

## 5. Security summary (mapped to OWASP Top 10)

A01 Broken access control → Policy layer + tests. A02 Cryptographic failures → `password_hash`, HTTPS redirect + HSTS option. A03 Injection → PDO prepared statements; output escaped; strict CSP (no inline scripts). A04 Insecure design → state machine, rate limits, least privilege. A05 Misconfiguration → source/config outside document root, security headers, errors logged not shown. A07 Auth failures → throttling per email and IP, session regeneration, generic login errors, role never read from forms. A08 Integrity → CSRF tokens on every POST. Uploads → extension + real MIME whitelist, random names, outside web root, authorised download.

## 6. Responding to changing requirements and growth

*Changing requirements*: rules live in config (`level2.*`), reference lists in `Lists`, status flow in one method (`ProjectController::transition`), eligibility in `Level2`. Adding a stage or role touches a handful of places and the test suite shows regressions.
*Growth (4,000 → 40,000)*: stateless web nodes behind a load balancer; DB is the only shared state; queries are indexed and lists paginated. Next steps in order: cache anonymous catalogue pages; read replica for admin reports; object storage for uploads; queue for notifications/email.

## 7. Honest limitations (say these in the defence)

- No email delivery: notifications are in-app and passwords are reset by administrators.
- Chat/updates use polling, not websockets; this is a deliberate trade-off for hosting simplicity and data use.
- Search uses `LIKE`; replace with FULLTEXT or a search service at larger scale.
- The suite tests server behaviour thoroughly, but browser JavaScript (draft restore, service worker) is verified manually, not by automated browser tests; no formal load test has been run, so scaling claims are architectural, not measured.
- Video lessons embed YouTube; there is no video hosting.
- Demo accounts are public; they must be removed before real use.
