# YouthConnect Platform (YCP) – Design Spec

Date: 2026-11-09 · Stack: PHP 8 + MySQL/MariaDB (no framework) · Deliverables: GitHub repo + live link

## Goal
A Coursera-style learning platform (own branding) for ~4,000 East African youth (18–35), delivering:
- **Level One** – Virtual Learning Exchange: register → profile → discover → enrol → learn weekly → quiz each week → feedback → progress → certificate.
- **Level Two** – Collaborative Virtual Exchange: participant → team → project → tasks → milestones → evidence → mentoring → assessment → stakeholder validation → recognition.
- Secure, usable, reliable, maintainable, scalable; works on mobile and slow connections.

## UX model (patterns from Coursera, not its brand or assets)
- Home: hero, goal picker, category chips, card carousels, FAQ.
- Course page visible before enrolling: summary, what you'll learn, skills, level, duration (weeks × hours/week), certificate, language, week-by-week syllabus with item counts, instructors, reviews, FAQ, sticky Enrol card.
- Learning player: week sidebar with check marks, lesson view, progress bar, **a quiz at the end of every week**, graded assignment, discussion forum.
- My Learning dashboard with progress per course; certificates verifiable by code.

## Architecture (lightweight MVC, front controller)
`public/index.php` → Router → Middleware (auth, role, CSRF) → Controller → Policy check → Model/DB → View.
- PDO prepared statements only. Passwords: `password_hash` (bcrypt/argon). DB-backed sessions (stateless web nodes → horizontal scale).
- **Central Policy layer**: every resource access (course management, team, project, evidence, messages) goes through `Policy::*`; controllers never trust IDs from the URL.
- Public opaque IDs (`public_id`) for teams/projects/evidence in URLs (defence in depth, not the primary control).
- Validation: server-side `Validator`, client hints only. Output escaping by default in views.
- CSRF tokens on all state-changing requests; session fixation protection; login throttling; secure headers (CSP, X-Frame-Options, etc.).
- Uploads: stored outside web root, random names, MIME whitelist + size cap, served through an authorised download route.

## Low-bandwidth / mobile / reliability
- Mobile-first CSS, system fonts, no frameworks/CDNs, one CSS + one JS file, gzip, long-cache versioned assets.
- Conditional GET (ETag / 304) on HTML pages; tiny JSON "pulse" endpoint (`/api/projects/{id}/pulse`) so pages poll a few bytes instead of reloading. Polling pauses when tab hidden or **Save-Data / Lite mode** is on.
- Lite mode (profile + `Save-Data` header): no decorative images, no polling.
- Offline tolerance: service worker caches shell; drafts (messages, quiz answers, assignment text) saved in localStorage and re-sent on reconnect.
- Idempotent writes (unique keys, e.g. one enrolment per user/course); transactions for multi-row changes; audit log.

## Incident fixes (CAT 2 scenario) – each has an automated test
1. *Member can't see updated feedback* → feedback stored once, keyed to the project (not copied per member); all active members read the same rows; project `version` bumps on feedback; ETag + pulse endpoint + in-app notification for every member.
2. *Changing a project ID exposes another team* → opaque IDs + `Policy::viewProject` (member of team, assigned mentor/facilitator/stakeholder, or admin) on every project route, including evidence downloads, messages, tasks. Non-members receive 404.
3. *Refreshing consumes excessive data* → 304 conditional responses, pulse endpoint (~100 bytes), gzip, polling backoff, Lite mode.

## Roles
participant, facilitator (course owner / project facilitator), mentor, stakeholder, admin. Level Two requires eligibility (completed ≥1 course, or admin override).

## Data model (summary)
users, profiles, categories, courses, modules(weeks), lessons, quizzes, questions, options, enrollments, lesson_progress, quiz_attempts, assignments, submissions, reviews, discussion_posts, certificates, teams, team_members, projects, project_staff, milestones, tasks, evidence, feedback, assessments, validations, project_messages, badges, user_badges, notifications, audit_log, throttle, sessions. See `database/schema.sql`.

## Out of scope (YAGNI)
Payments, live video, SSO, email delivery (notifications are in-app; mail hook stubbed), native apps.

## Testing
`tests/run.php` smoke + security suite against the built-in PHP server and a test database: auth, role checks, enrolment, quiz grading, IDOR attempt, conditional GET, pulse endpoint.
