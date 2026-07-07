# Implementation Decisions

This documents the non-obvious choices made while building this module, and why — written so I can explain and defend each one.

## Scaffold: Laravel Breeze (Vue + Inertia stack)

Rather than wiring Inertia/session auth from scratch, I started from `laravel/breeze`'s Vue+Inertia stack. It's the standard, reviewer-recognizable pattern for this exact stack, and the well-solved plumbing (session auth, CSRF, password broker, form requests) isn't worth reinventing. Everything beyond that baseline — 2FA, the i18n layer, the visual design, the security hardening — is custom and I can walk through any of it.

I then **removed** the parts of the default scaffold the spec doesn't ask for: email verification (`MustVerifyEmail` was never enabled on the `User` model, so those routes were dead code), profile management (update name/password/delete account), and the marketing "Welcome" page. Keeping unused surface area around just makes the app harder to reason about and explain — the spec explicitly rewards a "stable, clear, well-finished solution" over extra scope.

## Two-factor authentication design

- **Storage**: four columns added directly to `users` (`two_factor_code` hashed, `two_factor_expires_at`, `two_factor_attempts`, `two_factor_last_sent_at`) rather than a separate table. A user only ever has one *active* challenge at a time, so a separate table would just add joins for no benefit.
- **Code**: 6-digit numeric (`random_int(100000, 999999)`), hashed with `Hash::make()` before storage — the plaintext only ever exists in the email itself, never logged or persisted.
- **Expiry**: 10 minutes.
- **Attempts**: capped at 5 wrong guesses; exceeding it invalidates the code and forces the user back to a fresh login (rather than silently letting them keep guessing).
- **Resend**: 60-second cooldown enforced server-side (via `two_factor_last_sent_at`), independent of the route-level `throttle` middleware — the cooldown is the actual UX-facing rate limit; the route throttle is a coarser backstop.
- **Login flow**: credentials are verified (same rate-limited logic Breeze normally runs inside `Auth::attempt`), but `Auth::login()` is *not* called yet. Instead the user id sits in `session('two_factor.user_id')` until the code is verified, at which point the session is regenerated and the user is actually logged in. This means a valid password alone never establishes a session — the second factor is what actually authenticates.
- **Registration also routes through the same challenge**, rather than auto-logging a new account in. The spec frames 2FA as gating "login," but a registration silently bypassing it would mean the second factor doesn't actually protect the very first session a user gets — so I chose to apply it uniformly. This is the one place I extended the spec's literal wording, and I'm flagging it here since it wasn't explicit.

## Password reset

Reused Laravel's built-in password-broker flow (token generation, expiry, signed verification) rather than building it from scratch — it's already secure and well-tested. The only customization is `User::sendPasswordResetNotification()`, which swaps in a localized notification instead of the default English-only mail. Because it goes through Laravel's standard `Mail`/`Notification` facades, it's provider-agnostic — the actual transport (see below) was swapped twice without touching this code at all.

## Email provider: three attempts, here's why

The provider went through three candidates before landing on one that actually satisfies the spec's requirement that "the reviewer must be able to test... on the deployed version" — i.e., a real inbox that isn't mine has to receive real mail.

1. **Resend** (first choice) — Laravel ships a native `resend` transport, so integration is trivial (`composer require resend/resend-php` + two env vars). But Resend's sandbox mode only allows sending to the account owner's own verified email until a *domain* is verified via DNS. The domain available for this account (a university subdomain, `diu.edu`) isn't one we control the DNS for, so verification could never complete — a dead end that had nothing to do with the code and everything to do with account/domain ownership.
2. **SendGrid** (second choice) — only requires single-sender email verification, not domain/DNS, which looked like the fix. Twilio's automated account vetting rejected the signup outright with no specifics and no appeal path.
3. **Gmail SMTP** (final choice) — no vetting process, works immediately, and — critically — was verified to deliver to a mailbox that is *not* the sending account (see verification log), which is the actual property the spec requires. The trade-off: Gmail's SMTP relay caps at ~500 messages/day and looks less "enterprise" than a dedicated transactional provider, but for a review-scale demo that ceiling is a non-issue.

This is documented in this much detail deliberately: the interesting engineering judgment here wasn't which provider's API to call (that part is one line in `.env` either way), it was recognizing that "email works" and "the *reviewer* can receive email" are different claims, and testing the second one rather than assuming it.

Mail is sent **synchronously** (not queued), regardless of provider — queuing would need a worker process running continuously in the deployed environment, which is unnecessary infrastructure for a demo server and would risk a 2FA email silently sitting in an unprocessed queue if the worker isn't running.

## Localization (English / Hebrew, RTL)

- Locale is read from `session('locale')`, falling back to a **long-lived cookie** if the session doesn't have it. This matters because `logout()` calls `session()->invalidate()`, which wipes the session entirely — without the cookie fallback, a Hebrew-speaking user would silently be bounced back to English every time they logged out. This was actually caught during manual verification: the RTL layout worked immediately after switching, but reverted to English after a logout/login cycle until the cookie fallback was added.
- Backend copy (validation messages, mail, flash messages) lives in `lang/en/*.php` and `lang/he/*.php`. The Hebrew files only override the specific keys this app actually triggers (not a full translation of Laravel's ~80 validation rules) — Laravel automatically falls back to English for any key missing from the active locale's file, so this is a deliberate scope cut, not an oversight.
- Frontend static strings use Laravel's **JSON translation** convention: `lang/he.json` maps the literal English copy to Hebrew (e.g. `"Login": "כניסה"`). English needs no file at all, since the English string *is* the key — a missing translation always degrades to readable English rather than a raw key like `auth.login_button`. A small `useTrans()` composable reads this dictionary from an Inertia-shared prop.
- **RTL layout**: rather than writing separate RTL/LTR stylesheets, all custom components use Tailwind's logical-property utilities (`ms-`/`me-`, `ps-`/`pe-`, `start-`/`end-`), which flip automatically based on the `dir` attribute. `dir`/`lang` on `<html>` are set **server-side** in `app.blade.php` from `App::getLocale()`.
- **Switching locale forces a full page reload** rather than an Inertia SPA transition. I initially tried updating `dir`/`lang` client-side on Inertia's `navigate` event, but hit a real bug: the SPA prop update completed (translated text appeared) while the `<html dir>` attribute silently didn't flip, because blade — the only place `dir` is actually computed — was never re-rendered. A full reload after the locale POST guarantees the server-rendered attributes are always authoritative, at the cost of one extra round-trip on a rarely-used action. Confirmed fixed via a scripted browser walkthrough (see README).

## Security

- Passwords hashed via bcrypt (`'password' => 'hashed'` cast); 2FA codes hashed the same way.
- Session is regenerated on both login success paths (2FA verification, password reset) and invalidated + CSRF-rotated on logout.
- Rate limiting is layered: Laravel's `RateLimiter` (5 attempts per email+IP, mirroring Breeze's original login throttle) plus route-level `throttle` middleware on login, register, 2FA verify/resend, and password-reset requests.
- Password policy strengthened beyond Laravel's bare default: minimum 8 characters, mixed case, at least one number (`AppServiceProvider::boot()`). I deliberately left out `->uncompromised()` (the HaveIBeenPwned breach check) — it makes an external HTTP call on every registration/reset, which trades demo-server reliability for a marginal benefit in a low-stakes take-home context.
- `TrustProxies` is configured to trust all proxies (`at: '*'`), which is required for correct HTTPS/secure-cookie detection behind Railway's reverse proxy — without it, Laravel would think every request is plain HTTP even when served over TLS.
- Login intentionally does **not** reveal whether an email exists ("These credentials do not match our records." for both a wrong password and an unknown email) — but 2FA and reset-password errors *are* specific ("4 attempts remaining," "code expired," "please wait 45 seconds"), since at that point the user has already proven ownership of the credentials or the inbox, so there's no enumeration risk left to protect against.

## Deployment

**Railway** was chosen over Render primarily because its free/starter tier doesn't cold-start a stopped container between requests — which matters here specifically because a reviewer testing 2FA needs the email round-trip to feel snappy, not delayed by a container waking up. Railway also makes attaching a managed MySQL/Postgres instance and setting env vars straightforward for a Laravel app.

## Known limitations

- The PHPUnit suite (18 tests) covers the auth flows' behavior at the HTTP/session layer (login defers `Auth::login()` until 2FA passes, the attempt cap, expiry, resend cooldown, password reset). It does not cover the Vue components directly — the UI/UX layer (loading states, RTL mirroring, translated copy) was instead verified with a scripted Playwright browser walkthrough (see README), which is what caught the locale-persistence-after-logout bug described above.
- The cityscape image on the login/auth screens is a CSS/SVG reconstruction of the reference screenshot's mood (dark skyline, grid overlay, soft glow), not the original Figma asset — I couldn't authenticate into the linked Figma file to export it directly, so I rebuilt the visual impression from the screenshot embedded in the assignment PDF instead of substituting an unrelated stock photo.
