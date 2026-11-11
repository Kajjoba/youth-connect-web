# Deploying YouthConnect (live link)

The app is plain PHP 8 + MySQL/MariaDB with no build step, so it runs on almost any host. Pick one route.

You need two things on every route: **(1)** the code, **(2)** an empty MySQL database. Then load the database and set the credentials.

## Recommended free route: GitHub + Render + TiDB Cloud (auto-updates on every push)

1. **Database** – create a free account at https://tidbcloud.com → create a **Serverless** cluster → *Connect* → generate a password. Note host, port (4000), user, password. Create a database called `youthconnect` (SQL editor: `CREATE DATABASE youthconnect;`).
2. **Load the data** (from your computer, one time): in PowerShell inside the project folder
   ```
   $env:DB_HOST='<host>'; $env:DB_PORT='4000'; $env:DB_NAME='youthconnect'; $env:DB_USER='<user>'; $env:DB_PASS='<password>'; $env:DB_SSL='1'
   php database/install.php
   ```
   (creates all tables and the demo data).
3. **Website** – https://render.com → sign in with GitHub → *New +* → *Blueprint* → pick `youth-connect-web`. Render reads `render.yaml`; enter `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` when asked (leave the Google ones blank for now).
4. After the first build you get `https://youthconnect-xxxx.onrender.com`: share that.
5. **Making changes**: edit locally → `git commit` → `git push`. Render rebuilds and updates the live site in a couple of minutes.

Free-plan limits: the site sleeps after ~15 minutes without visitors (first load then takes ~30–60 s); uploaded evidence files are wiped on each redeploy (fine for demos; use object storage for real use).
## Route A – Free PHP shared hosting (e.g. InfinityFree)

Simplest if you have no card and no command line.

1. Create a hosting account and a **MySQL database** in the control panel. Note the host, database name, user and password.
2. Choose **PHP 8.x** for the site.
3. Upload the whole project (File Manager or FTP) into `htdocs/` **including the hidden `.htaccess` files**. The root `.htaccess` forwards every request to `public/`, so source code and config are never served.
4. Open **phpMyAdmin** → select your database → **Import** → `database/youthconnect_demo.sql` (ready-made schema + demo courses and users).
5. In the file manager create `config/local.php` (copy `config/local.example.php`) and fill in:
   ```php
   <?php return [
     'APP_ENV' => 'production', 'DB_HOST' => '<host>', 'DB_NAME' => '<db>', 'DB_USER' => '<user>', 'DB_PASS' => '<password>',
     'APP_FORCE_HTTPS' => true,
   ];
   ```
6. Make sure `storage/uploads` and `storage/logs` are writable (permissions 755/775).
7. Open your site URL. Log in as `admin@youthconnect.test` / `Admin@12345`, then **immediately** reset or delete the demo accounts (Admin → Users).

Notes: free hosts may show a short anti-bot page to automated tools, run slower, and limit file size. Good enough for coursework; use Route B/C for real use.

## Route B – Docker host (Render, Railway, Fly.io…) from GitHub

1. Push the repo to GitHub. Create a **Web Service** from it; the host detects the `Dockerfile`.
2. Add a managed **MySQL** (Railway MySQL, Aiven free tier, TiDB Cloud…).
3. Set environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_ENV=production`, `APP_FORCE_HTTPS=1`.
4. Load the data once, either by importing `database/youthconnect_demo.sql` with any MySQL client, or from the service shell: `php database/install.php` (**drops and recreates all tables**; use `--no-seed` for an empty platform).
5. Uploaded evidence files live in `storage/uploads`: attach a persistent disk/volume there (or move to object storage later), otherwise they vanish on redeploy.

## Route C – Your own VPS

Apache or Nginx + PHP-FPM, document root = `public/`. Nginx: `try_files $uri /index.php?$query_string;`. Put credentials in environment variables, enable HTTPS (Let's Encrypt), and set `APP_FORCE_HTTPS=1`.

## Production checklist

- [ ] Demo accounts removed or passwords changed (they are public in the README).
- [ ] `APP_ENV=production`, `APP_DEBUG` off, HTTPS on.
- [ ] Database user has only the privileges it needs; database backups scheduled.
- [ ] `storage/` is outside the document root (it is, when the root is `public/`) and backed up.
- [ ] A real mail service if you later add password-reset emails (not included: administrators issue temporary passwords).
- [ ] Review `config/config.php` → `level2.min_completed_courses` for your programme rules.

## Scaling notes (4,000+ users)

- App servers are stateless (sessions are in the database): run several behind a load balancer.
- The hot reads are the course catalogue and course pages. Add a small cache (APCu/Redis) or CDN for anonymous pages, and a read replica for reporting.
- Move uploads to object storage (S3-compatible) when running more than one app server.
- All list screens are paginated and queries use indexes (see `database/schema.sql`).
