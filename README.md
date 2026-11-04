# Notary Office System

## Fresh XAMPP setup
1. Copy this folder to `C:\xampp\htdocs\notary` (or keep the current folder name and use its matching localhost URL).
2. Start **Apache** and **MySQL** in XAMPP.
3. In phpMyAdmin, import `database.sql` into MySQL.
4. Confirm the credentials in `config/db.php` (`root` with an empty password is the XAMPP default).
5. Ensure PHP can write to `uploads/documents`.
6. Open `http://localhost/notary/`.

Default login: **admin** / **Admin@123**. Change it immediately in Staff Users.

## Upload settings
In `php.ini`, then restart Apache:
```
upload_max_filesize = 5M
post_max_size = 64M
max_file_uploads = 20
```

## Upgrade an existing installation
1. Export a full phpMyAdmin backup.
2. Find records that do not yet have a payment:
   `SELECT a.id,a.ref_number FROM archives a LEFT JOIN payments p ON p.archive_id=a.id WHERE p.id IS NULL;`
3. Add those payments in the admin archive editor.
4. Import `update_final.sql`. It adds the final activity log and one-payment constraint.
5. Review `cleanup_legacy.sql` separately and run it only after another backup and confirmation that old data is no longer needed.

## Operating notes
A new archive is saved atomically with its documents and payment. The wizard requires a unique reference number, at least two documents, sender name, sender phone, and an amount greater than zero. Documents are PDF/JPG/JPEG/PNG and are limited to 5 MB each.

Administrators can manage archives, payments, staff, performance, and the append-only activity log. Staff can add, search, and view archives. Sessions expire after ten minutes of inactivity; the client warning appears at nine minutes, while the server remains authoritative.

## PWA and logo
The app uses the supplied `assets/img/logo.jpeg`; generated icons are in `assets/icons`. To regenerate them after changing the logo, run:
```
C:\xampp\php\php.exe -d extension=gd tools\generate_icons.php
```

PWA installation works on `http://localhost` or HTTPS. For phone testing, use HTTPS with `mkcert` configured for XAMPP or use a secure tunnel. The service worker caches only static assets and the offline page; private PHP pages and uploaded files are not cached.
