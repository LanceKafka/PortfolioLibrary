# The Developer's Library

An interactive PHP/MySQL portfolio for **Lance Geslani**. The homepage is one enormous bookshelf whose
books form a **pixel portrait** and the **owner's name**; every gold-banded book is a real project or
achievement, read from MySQL. Contact letters are stored in the database **and** emailed through
PHPMailer over authenticated SMTP (STARTTLS/TLS).

> *"An entire IT journey, preserved in a library. Every book is a project. Every shelf is a chapter."*

---

## 1. What's in the box

```
developer-library/
├── index.php  about.php  projects.php  skills.php  inquiry.php     public pages
├── login.php  logout.php  dashboard.php                            admin entry
├── admin/        projects · skills · achievements · inquiries · about · settings
├── actions/      submit-inquiry.php          (validate → save → email → honest reply)
├── config/       env.php · database.php · mailer.php
├── includes/     bootstrap · functions · auth (+CSRF) · library (domain) · header/nav/footer · admin shell
├── assets/       css/ js/ fonts/ images/      (library-core.js = pixel-portrait generator)
├── database/     developer_library.sql        (schema + your real content)
├── scripts/      create-admin.php · test-mail.php
├── tests/        core.test.js                 (node tests/core.test.js)
├── vendor/       PHPMailer 6.10 (bundled) + autoload.php
├── uploads/      images uploaded from the dashboard (PHP execution disabled)
├── .env.example  composer.json  .htaccess  .gitignore
```

**Pages:** The Library (home) · About the Author · The Collection (filterable) · Skills & Achievements ·
Correspondence Desk · private Admin dashboard.

**Your content is already loaded** from your existing freelance site: 23 projects in 7 categories,
17 certificates (with their images), 24 skills, your About text, and both portrait photos.

---

## 2. Install on XAMPP (Windows / macOS / Linux)

Requirements: **PHP 8.2+** (XAMPP 8.2+), **MySQL 8+ / MariaDB 10.5+**, Apache. Composer is optional
(PHPMailer is already bundled).

1. **Copy the folder** to `C:\xampp\htdocs\developer-library` (macOS: `/Applications/XAMPP/htdocs/`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. **Import the database**: open <http://localhost/phpmyadmin> → *Import* → choose
   `database/developer_library.sql` → *Go*. (It creates the `developer_library` database, tables and data.)
   ⚠ Re-importing drops all tables, including inquiries and admin accounts.
4. **Create your settings file**: copy `.env.example` to `.env` and fill it in (sections 3 and 4 below).
5. **Create the administrator** (no default password exists):
   ```
   C:\xampp\php\php.exe scripts\create-admin.php
   ```
   Choose a username and a password of 12+ characters.
6. Open **<http://localhost/developer-library/>**. Admin lives at `/login.php`.

### Composer (optional)
A working PHPMailer copy is in `vendor/`. To let Composer manage it instead:
```
cd C:\xampp\htdocs\developer-library
composer install
```
Composer replaces `vendor/autoload.php` with its own; nothing else changes.

### Enable the XAMPP extensions
`extension=pdo_mysql`, `mbstring`, `fileinfo`, `openssl` must be on in `php.ini` (they are by default;
`openssl` is required for TLS email).

---

## 3. Database configuration

`.env`:
```
DB_HOST=127.0.0.1
DB_NAME=developer_library
DB_USER=root        # XAMPP default; empty password
DB_PASS=
```
For anything beyond local development, create a limited user instead of `root`:
```sql
CREATE USER 'dl_app'@'localhost' IDENTIFIED BY 'a-long-random-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON developer_library.* TO 'dl_app'@'localhost';
```
Also set `APP_KEY` (used to hash visitor IPs and sign form tokens):
`php -r "echo bin2hex(random_bytes(32));"`

---

## 4. SMTP configuration — making the email real

All credentials live in `.env`, never in JavaScript or in Git.

**Gmail (most common):**
1. Turn on 2-Step Verification for the Google account.
2. Google Account → Security → **App passwords** → create one (16 characters).
3. `.env`:
   ```
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls                # STARTTLS. Use ssl + port 465 for implicit TLS
   MAIL_USERNAME=you@gmail.com
   MAIL_PASSWORD=abcdefghijklmnop     # the app password, not your login password
   MAIL_FROM_ADDRESS=you@gmail.com    # providers require this to match MAIL_USERNAME
   MAIL_FROM_NAME="The Developer's Library"
   ```
4. The **recipient** of notifications is *Admin → Settings → Inquiry recipient email*
   (seeded with `geslanilanceericson@gmail.com`, taken from your old site). Change it there.

Other providers work the same way (Brevo, Mailgun, Outlook, your host's SMTP): set host, port, and TLS mode.

### Prove it works — do this before going live
```
php scripts/test-mail.php                 # sends to the saved recipient
php scripts/test-mail.php other@x.com     # or to any address
```
It prints `SENT` or PHPMailer's exact error. Then submit a real letter on the Correspondence Desk and
confirm it lands in your inbox. **Reply-To is the visitor's address**, so *Reply* answers them directly.

### How failure is reported (by design, never faked)
| Situation | Visitor sees | Database |
|---|---|---|
| Saved + emailed | "Your letter has been delivered." | `email_status = sent` |
| Saved, SMTP failed | "Saved in the ledger, **but the email notification could not be sent**" | `email_status = failed` + reason; *Admin → Inquiries → Retry* |
| Could not save | Error; their text stays in the form | nothing stored |

Server details and credentials are never shown to visitors; errors go to `storage/logs/app.log`.

---

## 5. The pixel portrait

`assets/js/library-core.js` + `shelf.js` do this in the browser, every page load:

1. Load the portrait (`site_settings.profile_image`) and crop it (`portrait_config.crop`).
2. Downscale in steps to a grid (76 columns on desktop, 44 on phones; rows are derived so faces aren't stretched).
3. Auto-levels, contrast, gamma, saturation and a local-contrast pass (so eyes and edges survive).
4. Map each cell to the nearest of 19 book colours in CIE-Lab space.
5. Build the name from a 5×7 pixel font (one book per pixel), on a dark plate, with smart wrapping for long names.
6. Place every real volume on a wall cell or a dark background cell (never on the face or the name).

**To change the photo:** *Admin → Settings → Replace portrait*, then adjust **crop, columns, contrast, gamma,
saturation, detail boost, dither, seed** in the same page. A head-and-shoulders photo with a plain dark
or light background works best; the current crop is tuned for your photo.

Thick / thin / dark / bright / golden books are set per project (*Book type*) with optional custom
colour, width and height in the project form.

---

## 6. Managing content (Admin → `/login.php`)

- **Projects** — add/edit/delete, category, book type/colour/size, GitHub & live links, cover + extra screenshots, hide/show.
  A new project becomes a book on the home shelf and in its category **immediately**.
- **Skills** — add, rate (1–5 or leave *Not rated*), delete.
- **Achievements** — add certificates with images; unknown dates may stay blank.
- **Inquiries** — read, set *pending / read / responded*, reply by email, retry a failed notification, delete.
- **About chapters** — edit each chapter; an empty chapter shows "This chapter is still being written."
- **Settings** — owner name (also redrawn on the shelf), intro lines, recipient email, social links, portrait recipe.

### Things I deliberately did **not** invent — fill these in
| Item | Status |
|---|---|
| Skill proficiency levels | `NULL` (your old site had none). Rate them in *Admin → Skills*. |
| Achievement dates | blank. Add them in *Admin → Achievements*. |
| Career aspirations chapter | empty (shows "still being written"). Write it in *Admin → About chapters*. |
| LinkedIn URL | empty. Add it in *Admin → Settings*. |
| Book *types* (thick/thin/dark/bright) | my judgment from each project's nature. Change per project. |
| "Design & Multimedia" category | added because your Figma/Photoshop/Canva work didn't fit the 8 suggested categories. |
| Project "key features" | only where your own descriptions listed them. |

The 2 large demo videos from the old site were left out (≈200 MB). To show one on the About page, copy
an `.mp4` into the project and set the `demo_reel` row in `site_settings` to its relative path.

---

## 7. Security summary

PDO prepared statements everywhere · `password_hash`/`password_verify` (+ timing-equalised login, 5 tries per 15 min) ·
session-based admin with HttpOnly + SameSite cookies, session-id regeneration, 2 h idle timeout ·
CSRF tokens on every POST (single-use rotation for the inquiry form) · output escaping on all dynamic HTML and
`<script>` JSON · honeypot + signed timing trap + MySQL rate limit (3 letters / 15 min, 10 / day per hashed IP) +
duplicate-letter guard · header-injection stripping · uploads: finfo MIME check, `getimagesize`, 4 MB cap, random
filenames, PHP execution disabled in `uploads/` · Content-Security-Policy (`script-src 'self'`), `X-Frame-Options`,
`nosniff` · `.htaccess` blocks `.env`, `config/`, `includes/`, `database/`, `vendor/`, `scripts/`, `storage/` ·
IPs stored only as HMAC hashes.

**Before publishing:** serve over HTTPS; move `.env` one level above the web root as
`../developer-library-private/.env` (auto-detected); set `APP_DEBUG=false`; use a limited DB user;
keep `MAIL_VERIFY_PEER=true`; never commit `.env` (it is in `.gitignore`).

---

## 8. Git & GitHub

```
cd developer-library
git init
git add .
git status          # confirm .env is NOT listed
git commit -m "The Developer's Library"
git branch -M main
git remote add origin https://github.com/<you>/developer-library.git
git push -u origin main
```

---

## 9. Testing checklist (what I ran, and what only you can run)

Verified in a sandbox with PHP 8.3, MariaDB 10.11, headless Chromium, and a local SMTP server that
**requires STARTTLS and AUTH**:

- All 5 public pages + 7 admin pages return 200 with zero console errors; admin redirects when logged out.
- Full visitor journey: enter library → open a book → About → submit inquiry → row saved (`email_status=sent`) → message received with the right subject, From, **Reply-To**, escaped body.
- Validation (client + server), CSRF (missing/forged), honeypot, timing trap, forged form token, header injection, XSS payload (inert), duplicate guard, rate limit (4th letter → 429).
- SMTP **down** → saved-not-emailed, never "sent"; SMTP **wrong password** → fails cleanly; **silent server** → gives up after 20 s (PHPMailer's default would hang 300 s — capped).
- Admin: login (wrong/right), malicious `.jpg`-that-is-PHP rejected, new project appears as a book on both shelf and index, status change, forged admin POST refused, settings save regenerates the shelf.
- Keyboard (Enter opens, focus trapped, Esc restores focus, arrow keys turn pages), 44 px tap targets, filters without reload, phone layout (portrait over name, zoom).

**Only you can verify:** delivery to a *real* inbox with your real SMTP credentials
(`php scripts/test-mail.php`), and appearance on your own devices/browsers.

Run the generator unit tests: `node tests/core.test.js`.

---

## 10. Troubleshooting

| Symptom | Fix |
|---|---|
| "The library is briefly closed" (503) | MySQL isn't running or `.env` DB values are wrong. Details: `storage/logs/app.log`; set `APP_DEBUG=true` temporarily. |
| Blank bookshelf / "portrait could not be loaded" | `profile_image` path wrong or file missing. Check *Admin → Settings*. |
| Links go to wrong URLs | Set `APP_BASE=/developer-library` in `.env`. |
| Letters saved but `email_status = failed` | Run `php scripts/test-mail.php`. Gmail needs an **App Password**; `MAIL_FROM_ADDRESS` must equal `MAIL_USERNAME`; try port 465 + `MAIL_ENCRYPTION=ssl`; make sure `extension=openssl` is enabled. |
| "certificate verify failed" | Your PHP lacks a CA bundle: set `openssl.cafile` in `php.ini` (XAMPP: `C:\xampp\apache\bin\curl-ca-bundle.crt`). Don't disable verification in production. |
| Upload fails | Image > 4 MB, wrong type, or `uploads/` not writable. Also check `upload_max_filesize` / `post_max_size` in `php.ini`. |
| Locked out of admin | `php scripts/create-admin.php <username>` resets that user's password. |
| "Too many attempts" at login | Wait 15 minutes, or `DELETE FROM rate_limits;` in phpMyAdmin. |
| Shelf looks slow on an old phone | Lower *Columns (mobile)* / *Columns (desktop)* in *Admin → Settings*. |
| Sound doesn't play | It's off by default; toggle **Sound** in the header (browsers also need a click first). |

Fonts: Cormorant, Karla and Silkscreen (SIL Open Font License), bundled in `assets/fonts/`.
