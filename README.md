# YouthConnect Platform (YCP)

A Coursera-style learning and collaboration platform for young people (18–35) in Uganda and East Africa.
Built with **PHP 8 + MySQL/MariaDB, no framework**, as the integrated Web Application Engineering project.

* **Level One – Virtual Learning Exchange:** register → create profile → discover courses → see the weekly plan *before* enrolling → enrol → learn week by week → pass a quiz every week → get feedback on assignments → track progress → earn a verifiable certificate.
* **Level Two – Collaborative Virtual Exchange:** participant → team → project → tasks → milestones → evidence → mentoring → assessment → stakeholder validation → certificates and badges.
* Built for **mobile and slow connections**: small pages, Lite mode, conditional requests, offline-safe drafts.

> Live link: _add your deployed URL here_ · Demo accounts are listed below.

## Quick start (local)

Requirements: PHP 8.0+ with `pdo_mysql`, MySQL 5.7+/MariaDB 10.3+.

```bash
git clone <your-repo-url> youthconnect && cd youthconnect
cp config/local.example.php config/local.php     # then edit DB credentials
php database/install.php                          # creates tables + demo data
php -S 127.0.0.1:8080 -t public public/index.php  # open http://127.0.0.1:8080
```

With XAMPP/Apache, point the virtual host at `public/` (or just drop the folder in `htdocs`; the root `.htaccess` forwards to `public/`).
With Docker: `docker compose up --build`, then `docker compose exec web php database/install.php` → http://localhost:8080.

### Demo accounts (change or delete before real use)

| Role | Email | Password |
|---|---|---|
| Admin | admin@youthconnect.test | Admin@12345 |
| Facilitator | grace@youthconnect.test, david@youthconnect.test | Facilitate@123 |
| Mentor | amina@youthconnect.test | Mentor@12345 |
| Stakeholder | samuel@youthconnect.test | Stake@12345 |
| Participants | brian@, wanjiru@, joseph@, aline@ (Level Two unlocked), peter@ (in progress), esther@ (not eligible), moses@, fatuma@ `youthconnect.test` | Learn@12345 |

Brian, Wanjiru, Joseph and Aline are on the cross-border team **AgriTech Bridge** (Uganda, Kenya, Tanzania, Rwanda), whose project has mentor feedback waiting: the exact CAT 2 scenario.

## Architecture

```
public/index.php          front controller (only web-reachable PHP file)
app/Core/                 Router, DB (PDO), Session (DB-backed), Auth, Csrf, Validator, Policy, Upload, Throttle…
app/Controllers/          thin controllers, one per area
app/Services/             Progress, Level2 eligibility, Recognition, Courses queries
app/Views/                PHP templates; everything escaped with e()
database/                 schema.sql, installer, seed data
tests/run.php             automated functional + security tests
```

Request flow: **Router → CSRF check → middleware (auth/role) → Controller → Policy → DB → View**.

## Key engineering decisions (and why)

| Concern | Decision |
|---|---|
| Access control | A central `Policy` layer. Controllers never trust an ID from the URL; resources are loaded *through* the policy for the current user and answer "not found" otherwise. |
| Insecure direct object reference | Teams, projects and evidence use random 16-hex public ids **and** a membership check on every route, including downloads, tasks, messages. Child ids (task, milestone, evidence) are always queried together with the project id. |
| Stale feedback | Feedback is stored once per project (not copied per user). Every change bumps `projects.version`; members are notified, pages revalidate with ETag, and a pulse endpoint tells open pages to refresh. |
| Data use on slow links | gzip, one CSS + one JS file (long-cache, versioned), system fonts, **ETag/304** on pages, a ~80-byte JSON `pulse` with exponential backoff that pauses when the tab is hidden or Lite mode is on, nothing downloads (video) until tapped. |
| Offline / flaky connections | Drafts (quiz answers, assignment text, project fields) autosave to the device; chat messages queue and retry with an idempotency key; service worker caches the shell. |
| Authentication | `password_hash`, login throttling per email and IP, session regeneration on login, HttpOnly + SameSite cookies, DB-backed sessions (any web node can serve any user). |
| Validation | Server-side `Validator` for every input; client attributes are only hints. Role is never accepted from a form on registration. |
| Injection / XSS | PDO prepared statements only; output escaped by default; strict Content-Security-Policy (no inline scripts). |
| File uploads | Extension + real MIME whitelist, size cap, random names, stored outside the web root, downloaded through an authorised route. |
| Reliability | Transactions for multi-step writes; unique keys make enrol/certificate/message retries idempotent; status changes are atomic (`UPDATE … WHERE status = expected`); audit log. |
| Scalability | Stateless app servers (sessions in DB), indexed queries, paginated lists, config via environment variables; the DB is the only shared state. Next steps: read replicas, cache course cards, object storage for uploads. |
| Maintainability | Small single-purpose classes, no hidden globals beyond helpers, schema and seed as code, automated tests. |

## Tests

```bash
php -S 127.0.0.1:8099 -t public public/index.php &
php tests/run.php http://127.0.0.1:8099
```

Covers: registration and role-escalation attempt, login throttling, enrolment idempotency, quiz grading (forged option ids), **the three CAT 2 incidents** (shared feedback visible to all members, project-id tampering, conditional requests / pulse bytes), CSRF rejection, upload restrictions.

## Deploying (live link)

See [docs/DEPLOY.md](docs/DEPLOY.md). For "Continue with Google" see [docs/GOOGLE_SIGNIN.md](docs/GOOGLE_SIGNIN.md).

## Roles

participant · facilitator (builds courses, grades) · mentor · stakeholder · admin. Level Two unlocks after completing the configured number of courses (`config/config.php → level2.min_completed_courses`) or by admin override.
