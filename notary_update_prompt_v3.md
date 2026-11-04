# UPDATE PROMPT (v3): New features + fixes

Apply this on top of the main prompt (`notary_system_agent_prompt.md`) and the UI prompt (`notary_ui_design_prompt.md`). Where this update conflicts with them, **this update wins**. Implement everything below, update `database.sql`, and update the README.

---

## 1. New required document: "Ministry of Justice & Constitutional Affairs"

Every asset transaction receives an official document from the **Ministry of Justice & Constitutional Affairs**. It must be stored in the archive as its **own dedicated field**, separate from the 7 asset-document slots.

- Label everywhere: **"Ministry of Justice & Constitutional Affairs Document"**.
- **Required** for every new archive (one file per transaction). Allowed types: PDF, JPG, JPEG, PNG, max 5 MB. Same secure upload rules as the other files (real MIME check, random file name, stored in `/uploads/moj/`).
- Optional extra text field next to it: "MoJ document number" (not required).
- Add it to the Add Archive wizard as its own step (or a clearly separated card) right after "Documents": **1 Reference > 2 Documents > 3 Ministry of Justice Doc > 4 Seller > 5 Buyer > 6 Witnesses > 7 ID Card > Review**.
- Show it on the Review screen, in View Archive (its own section with preview/download and lightbox), in the Admin edit page (replace/remove-and-reupload, never delete without a replacement), and delete its file from disk when the archive is deleted.
- Database: add columns `moj_document_file` (required) and `moj_document_number` (nullable) to `archives`.
- The 7 asset-document rules stay: all 7 slots visible, minimum 2 uploaded.

---

## 2. Staff performance tracking (admin only)

The admin must be able to see **which staff member did what**.

**Data to record**
- `archives.created_by` and a new `archives.updated_by` (user id). `payments.received_by` already exists.
- New table `activity_logs`: `id, user_id, user_name_snapshot, action, entity_type, entity_id, entity_ref (e.g. archive ref number), details (short text/JSON), ip_address, user_agent, created_at`, with indexes on `user_id`, `action`, `created_at`.
- Log these actions: login, failed login, logout, auto logout (inactivity), archive created / viewed / updated / deleted, payment created / updated / deleted, archive search (store the search term), staff user created / updated / deactivated / deleted.
- Logs are **append-only**: no edit or delete in the UI.
- A staff account must **never be hard-deleted if it has archives or payments** (use deactivate instead, or block deletion with a clear message), so history is always preserved. Keep `user_name_snapshot` so names remain visible even if a user is later changed.

**Admin pages**
1. **Staff Performance** (`staff_performance.php`, admin only):
   - Filter by date range (Today, This week, This month, Custom) and by staff member.
   - A table (cards on mobile) per staff: archives added, payments recorded, total money collected, pending-payment archives they created, last login, last activity. Sortable, with a **ranking** (top performer highlighted).
   - A simple bar chart comparing staff (plain CSS/SVG/canvas, no heavy library), colors and style consistent with the glass design.
   - Click a staff member to open **Staff Detail**: their summary numbers, a list of every archive they added (with reference number, date, payment status, link to the archive), and their recent activity timeline.
2. **Activity Log** (`activity_log.php`, admin only): searchable, filterable (staff, action type, date range), paginated.
3. In **Archive list** and **View Archive**, show "Entered by: [staff name] on [date/time]" and, if edited, "Last updated by [name] on [date/time]". Also show who recorded the payment.
4. Dashboard (admin): add a small "Today's activity by staff" widget.

Staff cannot see any of these pages (server-side `require_admin()`).

---

## 3. Logo and branding

- The logo file is **`logo.jpeg`** in the project folder. Copy it to `assets/img/logo.jpeg` and use it:
  - On the **login page** (large, centered, above the title; display it in a rounded glass container so the JPEG background blends cleanly).
  - In the **web dashboard**: top of the sidebar (collapsed state shows a small version) and in the mobile top bar.
  - On the printable **payment receipt** and the archive print view header.
  - As the **favicon** and the **PWA / home-screen app icon** (see section 4).
- Extract the logo's dominant colors and use them as the system's **brand accent color** (replace the generic blue in buttons, active states, focus rings, progress bars and charts), keeping contrast WCAG AA.
- If `logo.jpeg` cannot be found in the project folder, stop and ask me to provide it; do not invent a logo.

---

## 4. Progressive Web App (PWA)

Make the system installable on phones and desktops.

- `manifest.webmanifest`: name "Notary Office System" (short_name "Notary"), `start_url` = login/dashboard, `scope` = `/notary/`, `display: "standalone"`, `orientation: "portrait-primary"`, `theme_color` and `background_color` taken from the brand colors, and icons.
- **Icons generated from `logo.jpeg`**: 192x192 and 512x512 PNG (`purpose: any`), 512x512 **maskable** (logo scaled with safe padding on a solid brand-colored background), `apple-touch-icon` 180x180, favicon 32x32 and 16x16. Generate them with a PHP GD script (`tools/generate_icons.php`) or equivalent, and save them in `assets/icons/`. Include the script and run it.
- Add in every page `<head>`: manifest link, `theme-color`, `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style`, `apple-mobile-web-app-title`, apple-touch-icon.
- **Service worker** (`sw.js`, served from the app root so its scope covers the whole app):
  - Pre-cache static assets only (CSS, JS, icons, logo, fonts if self-hosted, and an `offline.html` page).
  - **Never cache** PHP pages with private data, uploads (`/uploads/`), POST requests, or API/JSON responses. Use network-first for page navigations and fall back to `offline.html` when offline.
  - Versioned cache name and cleanup of old caches on activate.
- An **"Install app"** button/banner (using `beforeinstallprompt`) that appears on supported browsers, plus a short hint for iPhone ("Share > Add to Home Screen").
- Important note to put in the README: service workers and installation only work on **HTTPS or `http://localhost`**. For testing on a phone over the local network, explain how to use HTTPS (e.g. mkcert with XAMPP, or a tunnel) because plain `http://192.168.x.x` will not allow install.
- Offline behavior: the app does not support offline data entry; show a friendly "You are offline" page instead of the browser error.

---

## 5. Auto logout after 10 minutes of inactivity

**Server-side (authoritative)**
- Store `$_SESSION['last_activity']` and check it at the top of every protected request (inside `require_login()`). If more than **600 seconds** passed without activity, destroy the session, clear the cookie, log the action "auto logout", and redirect to `login.php?timeout=1` showing the message "You were signed out after 10 minutes of inactivity."
- Make the timeout a single constant in `config` (`SESSION_TIMEOUT = 600`).
- Session cookie hardening: `HttpOnly`, `SameSite=Lax` (or Strict), `Secure` when on HTTPS, `use_strict_mode`, `session_regenerate_id()` on login.
- AJAX/JSON endpoints must return `401` with a JSON message when the session expired, and the JS must then redirect to login.

**Client-side (user friendly)**
- JS idle timer that resets on `mousemove`, `keydown`, `click`, `scroll`, `touchstart`.
- At **9 minutes** show a glass dialog: "You will be signed out in 60 seconds" with a live countdown and a **"Stay signed in"** button (sends a lightweight keep-alive request to `keepalive.php`) and a "Sign out now" button.
- At **10:00** redirect to `logout.php?reason=timeout`.
- Synchronize across tabs using `localStorage` (activity in one tab keeps all tabs alive; logout in one tab logs out all).
- Long file uploads and the multi-step form must not be interrupted: treat form typing, step changes and upload progress as activity, and the keep-alive must also fire while the Add Archive wizard is open and being used.
- After timeout, the login page must not allow the browser back button to show private pages (send `Cache-Control: no-store` on authenticated pages).

---

## 6. Mobile bottom navigation: remove the blue and make it professional

In my screenshot of the mobile screen, the bottom navigation has a **solid blue highlighted element around the "Add" item** (blue block / oversized center button). **Remove it completely.** I will attach the screenshot for you to look at; if you cannot see it, apply the design below.

New design for the mobile bottom navigation:
- A clean floating **glass tab bar** (blur, thin light border, soft shadow, large radius), with **equal-width items**, same icon size (24px) and label size (11-12px), nothing larger than the rest, no solid colored backgrounds, no raised center button.
- Items: Admin = Home, Archives, Add, Payments, More. Staff = Home, Search, Add, More. "More" opens a glass bottom sheet with Profile, Staff Performance / Staff Users (admin only), Theme toggle, Install app, Logout.
- **Active state**: icon + label in the brand accent color (from the logo) with a small dot or thin pill indicator under the icon and a very subtle translucent tint behind the icon only. Inactive items use a muted neutral color. No blue fill.
- "Add" is a normal item with a plus icon (use the plus-in-circle outline icon so it is easily recognized).
- Minimum touch target 48x48px, `padding-bottom: env(safe-area-inset-bottom)`, content gets bottom padding so the bar never covers buttons, and the bar hides slightly when scrolling down and returns on scroll up (optional, smooth).
- Looks correct at 320, 360, 390, 414 and 768px, in light and dark mode, and in the installed PWA (standalone) mode.

---

## 7. Database changes summary
- `archives`: add `moj_document_file`, `moj_document_number`, `updated_by`.
- New table `activity_logs` (section 2).
- Keep all previous tables and relationships. Provide an upgrade-safe `database.sql` (create everything from scratch) and also an `update_v3.sql` with only the `ALTER TABLE` / `CREATE TABLE` statements for an existing database.

## 8. New/changed files
```
manifest.webmanifest, sw.js, offline.html
assets/img/logo.jpeg, assets/icons/*
tools/generate_icons.php
keepalive.php
staff_performance.php, staff_detail.php, activity_log.php
config/auth.php        (timeout check, logging helper log_activity())
assets/js/idle.js      (idle timer + warning dialog)
update_v3.sql
```

## 9. Test before finishing
1. Archive cannot be saved without the Ministry of Justice document; it shows on view and is replaceable by the admin.
2. Staff A and Staff B add archives and payments; the admin sees correct per-staff counts, totals, ranking, detail pages and activity log entries; staff cannot open those URLs.
3. The logo appears on the login page, sidebar, mobile top bar, receipt, favicon, and as the installed app icon.
4. The app installs as a PWA (on localhost/HTTPS), opens standalone, shows `offline.html` when offline, and private pages/uploads are never cached.
5. After 9 minutes idle the warning appears, "Stay signed in" resets the timer, and at 10 minutes the user is logged out both client-side and server-side (also test by waiting 10 minutes and sending a request without JS).
6. The mobile bottom bar has no blue block, looks balanced and professional, and works at all widths and in dark mode.
7. All earlier requirements (roles, validation, unique reference number, payment popup, search with payment info, admin CRUD) still work.
