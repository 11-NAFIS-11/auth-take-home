# Kreisberg Auth — Take-Home Assignment

A production-like authentication module built with **Laravel + Vue.js + Inertia.js**: login, registration, logout, email-based two-factor authentication, and forgot/reset password — fully localized in English and Hebrew (RTL).

See [DECISIONS.md](DECISIONS.md) for the implementation notes and rationale behind the non-obvious choices.

## Stack

- Laravel 12, Vue 3 + Inertia.js (Breeze scaffold as the starting point)
- Tailwind CSS
- SQLite locally, MySQL/Postgres in production (swapped purely via `.env`)
- [Resend](https://resend.com) for transactional email (2FA codes + password resets)

## Local setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate

npm run build   # or `npm run dev` in a separate terminal for hot-reload
php artisan serve
```

Visit `http://localhost:8000`. With the default `MAIL_MAILER=log`, 2FA codes and password-reset links are written to `storage/logs/laravel.log` instead of being emailed — open that file to find the 6-digit code or reset link while testing locally.

To send real email locally, set in `.env`:

```
MAIL_MAILER=resend
RESEND_API_KEY=your-resend-api-key
```

## Features

- **Login** — email/password, specific validation and server error states, loading/disabled state during submission, rate-limited.
- **Registration** — real user creation, strong password policy, routes through the same 2FA challenge as login.
- **Two-factor authentication (email)** — 6-digit one-time code required after valid credentials, 10-minute expiry, 5-attempt cap, 60-second resend cooldown, all with specific error copy.
- **Forgot / reset password** — real email via Resend, signed reset link, localized email template.
- **Logout** — invalidates the session and rotates the CSRF token.
- **Hello World landing page** — the only page behind `auth`, shown once 2FA succeeds.
- **English / Hebrew (RTL)** — a language switcher persists the choice in session + a long-lived cookie (so it survives logout), and the whole layout mirrors for Hebrew via Tailwind's logical-property utilities (`ms-`/`me-`/`ps-`/`pe-`/`start-`/`end-`).

## Deployment (Railway)

The app deploys as a standard Laravel project — Railway's Nixpacks builder auto-detects `composer.json` and `package.json` and runs `composer install` + `npm run build` during the build step. A `Procfile` pins the start command:

```
web: php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT
```

Steps:

1. On [railway.app](https://railway.app), create a new project from this GitHub repo.
2. Add a MySQL (or Postgres) database plugin to the project.
3. Set environment variables on the web service:
   - `APP_KEY` — generate locally with `php artisan key:generate --show` and paste the value.
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<your-railway-domain>`
   - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — copied from the database plugin's connection details.
   - `SESSION_SECURE_COOKIE=true` (the app is served over HTTPS in production).
   - `MAIL_MAILER=resend`, `RESEND_API_KEY=...`, `MAIL_FROM_ADDRESS=...`, `MAIL_FROM_NAME=Kreisberg`.
4. Generate a public domain for the service and confirm it matches `APP_URL`.
5. Deploy, then walk through register → 2FA → dashboard → logout → login → forgot password on the live URL before sharing it.

`php artisan serve` is a single-process dev server — acceptable per the assignment's "demo server, not production-grade" allowance, but worth naming as a known limitation rather than presenting it as a production setup.

## Tests / verification

`php artisan test` runs 18 PHPUnit feature tests covering login (including the "credentials succeed but session isn't established until 2FA passes" behavior), registration, the 2FA challenge (correct code, wrong code, expiry, the 5-attempt cap, resend cooldown), logout, and the full forgot/reset-password flow.

On top of that, the full user-facing flow was verified end-to-end with a scripted browser (Playwright) walkthrough: registration → 2FA → dashboard → logout → login → 2FA → Hebrew toggle (layout mirror + persistence across logout) → forgot password → emailed reset link → reset → login with new password, plus the invalid-code, wrong-password, and double-submission-guard visual states.
