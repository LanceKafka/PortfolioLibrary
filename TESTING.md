# Test Plan & Troubleshooting

## Quick checks

Run from the project root:

```shell
php -v
php tools/smoke-test.php
```

These tests validate the JSON schema, book mapping, output escaping, and basic path protections. They do not send mail or replace browser testing.

## Public site

- [ ] Browse all five public pages; check links and navigation, including the mobile menu.
- [ ] Confirm the homepage portrait is built from individual books (inspect `#portrait-books > button.pixel-book`), not a static image.
- [ ] Confirm the name is built from individual books (`#name-books > button.name-book`).
- [ ] Hover or focus a portrait book; it should move. Click or press Enter; a book detail dialog should open.
- [ ] Press Escape to close a dialog; focus should return to the originally selected book.
- [ ] Arrow keys should move between books in the pixel mosaics.
- [ ] Browse projects, search by title/technology, change category filters and open/close detail pages.
- [ ] Browse skills, filter categories, and open a skill book; do not display a fabricated skill level.
- [ ] Confirm an empty achievements shelf shows its empty state without invented awards.
- [ ] At 375 px, 768 px, 1366 px widths, verify books are still clickable and content is legible.
- [ ] Enable reduced motion and verify no elaborate animation is required for functionality.

## Admin and JSON persistence

1. Run `php tools/create-admin.php`, choose a password of 12+ characters.
2. Sign in through `/login.php` and verify the dashboard loads.
3. Create a new project, with color and dimensions and optional screenshot.
4. Return to `/projects.php` and `/index.php` and verify the added book appears or participates in the mosaic.
5. Edit its title, refresh both pages and check that the updated title appears in the modal and on the collection shelf.
6. Delete it and verify it disappears.
7. Create, edit, and remove a skill and a golden achievement.
8. Update the name and upload a new portrait, then refresh home; observe the updated spine portrait and book-made name.
9. Attempt an invalid image type, oversized upload, tampered form CSRF and malformed category; expect an error.
10. Log out and verify admin pages redirect to login.

## Actual SMTP verification

1. Run `composer install`; check `vendor/autoload.php` exists.
2. Configure `.env` with working SMTP host, port, TLS mode, username, password and verified sender/recipient.
3. Open the correspondence page and submit a valid inquiry.
4. Check the **recipient mailbox** and spam/junk folder; inspect the subject, sender details, time and Reply-To value.
5. Temporarily set an incorrect SMTP password and submit again. The UI **must show failure**, retain the message, and must **not** claim the inquiry was sent.
6. Check the PHP/Apache error log for a non-public failure diagnostic. Restore the correct credentials and retry.
7. Submit rapid requests and verify the server responds with HTTP 429 after four attempts within 15 minutes. For testing, remove your own test `storage/inquiry-rate-*.json` file to reset the limit; do not expose this mechanism on the web.
8. Test an invalid email, blank required field, forged CSRF and a non-empty hidden `website` honeypot field.

## Access-denial checks — critical

The following requests must respond **403**, not JSON/PHP source or credentials:

- `/developer-library/.env`
- `/developer-library/data/settings.json`
- `/developer-library/storage/admin.json`
- `/developer-library/config/mailer.php`
- `/developer-library/tools/create-admin.php`
- `/developer-library/vendor/autoload.php`

If they load, stop your Apache server and review `AllowOverride All` and your directory configuration before configuring SMTP.

## Troubleshooting

| Problem | What to inspect |
| --- | --- |
| Blank page / HTTP 500 | Apache PHP error log, PHP 8.2+, file permissions, `php -l` of modified files |
| No portrait books | Browser console, accessibility/JavaScript enabled, `assets/js/bookshelf.js` loading, image source URL, JavaScript canvas availability |
| Actual portrait is cropped | Use a centered headshot or adjust image to a 42:32 crop before upload |
| Admin changes won't persist | PHP write access to `data/` and `storage/`, filesystem permissions, JSON file integrity |
| Composer not found | Install Composer, ensure the XAMPP PHP executable and Composer are on the PATH |
| Mail says unavailable | Check `vendor/autoload.php`, `.env` SMTP parameters, PHP `openssl` and file permissions |
| Mail accepted but not in inbox | Recipient spam folder, provider sender identity verification, SPF/DKIM/DMARC, provider logs |
| CSRF/session expired | Enable cookies, reload inquiry/login page, use a consistent hostname and HTTP/HTTPS scheme |
| Upload fails | Check `upload_max_filesize`, `post_max_size` in `php.ini`, 5 MB limit and permitted file types |
| Nested installation path broken | Extract directly to the intended folder or configure `APP_BASE_PATH` in `.env` |
| Access rules ignored | Apache `AllowOverride All` is required; PHP built-in server must use `router.php` |
