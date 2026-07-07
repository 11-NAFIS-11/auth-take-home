# QuantiTop Auth — Take-Home Assignment

A production-like authentication module built with **Laravel + Vue.js + Inertia.js**: login, registration, logout, email-based two-factor authentication, and forgot/reset password — fully localized in English and Hebrew (RTL).

**Live demo:** https://auth-take-home.onrender.com (note: Render's free tier cold-starts after ~15 minutes idle — the first request can take 10-30 seconds)

See [DECISIONS.md](DECISIONS.md) for the implementation notes and rationale behind the non-obvious choices.

## Stack

- Laravel 12, Vue 3 + Inertia.js (Breeze scaffold as the starting point)
- Tailwind CSS
- SQLite locally, MySQL/Postgres in production (swapped purely via `.env`)
- Gmail SMTP for transactional email (2FA codes + password resets) — see [DECISIONS.md](DECISIONS.md) for why

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

Visit `http://localhost:8000`. With the default `MAIL_MAILER=log`, 2FA codes and password-reset links are written to `storage/logs/laravel.log` instead of being emailed — open that file to find the 4-digit code or reset link while testing locally.

To send real email locally, set in `.env` (see DECISIONS.md for why Gmail SMTP was chosen over Resend/SendGrid):

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_FROM_ADDRESS=your-gmail-address@gmail.com
```

`MAIL_PASSWORD` must be a Google [App Password](https://myaccount.google.com/apppasswords) (requires 2-Step Verification on the account), not the account's normal login password.

## Features

- **Login** — email/password, specific validation and server error states, loading/disabled state during submission, rate-limited.
- **Registration** — real user creation, strong password policy, routes through the same 2FA challenge as login.
- **Two-factor authentication (email)** — 4-digit one-time code required after valid credentials, 10-minute expiry, 5-attempt cap, 60-second resend cooldown, all with specific error copy.
- **Forgot / reset password** — real email via Gmail SMTP, signed reset link, localized email template.
- **Logout** — invalidates the session and rotates the CSRF token.
- **Hello World landing page** — the only page behind `auth`, shown once 2FA succeeds.
- **English / Hebrew (RTL)** — a language switcher persists the choice in session + a long-lived cookie (so it survives logout), and the whole layout mirrors for Hebrew via Tailwind's logical-property utilities (`ms-`/`me-`/`ps-`/`pe-`/`start-`/`end-`).

## Deployment (Render)

Railway was the original target but requires a paid plan (trial credits expired on this account); Render's free tier doesn't need a card, so that's what this is actually deployed on. See DECISIONS.md for the full reasoning.

Render has no native PHP runtime (only Node, Go, Python, Ruby, Rust, Elixir, or Docker), so this deploys via the repo's **Dockerfile** — a single-stage `php:8.2-cli` image with the Postgres/MySQL/zip/mbstring/curl/xml extensions installed, running `composer install` then `php artisan migrate --force && php artisan serve`. The built frontend assets (`public/build/`) are **committed to this repo** rather than built during deploy, specifically to avoid needing a Node.js stage in that Dockerfile — one less thing to get right in a build I couldn't fully test locally (no Docker available in the dev environment) before it had to work for the actual submission. This means: **after any change to `resources/js` or `resources/css`, run `npm run build` and commit the updated `public/build/` before deploying.**

Steps:

1. On [render.com](https://render.com), create a new **PostgreSQL** instance (free tier) first, and note its **Internal Database URL**.
2. Create a new **Web Service** from this GitHub repo, with **Language** set to **Docker** (it auto-detects the `Dockerfile`) and **Free** instance type.
3. Set environment variables on the web service:
   - `APP_KEY` — generate locally with `php artisan key:generate --show` and paste the value.
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<your-render-domain>`
   - `DB_CONNECTION=pgsql`, `DB_URL=<the Postgres instance's Internal Database URL>` (Laravel's `config/database.php` reads a single `DB_URL` and derives host/port/database/credentials from it).
   - `SESSION_SECURE_COOKIE=true` (the app is served over HTTPS in production).
   - `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`, `MAIL_USERNAME`, `MAIL_PASSWORD` (Gmail App Password), `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME=QuantiTop`.
4. Deploy, then walk through register → 2FA → dashboard → logout → login → forgot password on the live URL before sharing it.

`php artisan serve` is a single-process dev server — acceptable per the assignment's "demo server, not production-grade" allowance, but worth naming as a known limitation rather than presenting it as a production setup. A `Procfile` is also kept in the repo in case deployment moves to Railway or another Procfile-based host later.

## Tests / verification

`php artisan test` runs 18 PHPUnit feature tests covering login (including the "credentials succeed but session isn't established until 2FA passes" behavior), registration, the 2FA challenge (correct code, wrong code, expiry, the 5-attempt cap, resend cooldown), logout, and the full forgot/reset-password flow.

On top of that, the full user-facing flow was verified end-to-end with a scripted browser (Playwright) walkthrough: registration → 2FA → dashboard → logout → login → 2FA → Hebrew toggle (layout mirror + persistence across logout) → forgot password → emailed reset link → reset → login with new password, plus the invalid-code, wrong-password, and double-submission-guard visual states.
