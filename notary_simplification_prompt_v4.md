# UPDATE PROMPT (v4): Simplify the system (client request)

Apply this on top of all previous prompts (main prompt, UI prompt, v3 update). **Where this update conflicts with them, this update wins.**

## 0. Why this change
We met the client. He wants a system that is **easy and simple** for office staff. He asked us to remove most of the archive form inputs. The Add Archive form must now contain only:

1. **Reference No**
2. **Documents**
3. **Review**

Everything else on that form is removed. Do not hide fields with CSS only: remove them properly from the form, validation, database usage, views, search and reports as described below.

---

## 1. Features to REMOVE

Remove completely from the **Add Archive form, Edit Archive, View Archive, Review screen, archive list/search, receipt/print views, validation, and JS**:

| Remove | Notes |
|---|---|
| Ministry of Justice & Constitutional Affairs document (+ MoJ document number) | Added in v3, now removed |
| Seller (name, phone, photo) | |
| Buyer (name, phone, photo) | |
| Witnesses 1-4 (name, phone, photo) and the "minimum 2 witnesses" rule | |
| Identity card upload | |

Also remove the related: wizard steps, upload tiles, camera-capture inputs, counters ("minimum 2 witnesses"), review-screen sections, view-page sections, edit-page sections, and any client-side image-resize code that only served those fields.

---

## 2. What the new Add Archive flow looks like (3 steps)

Wizard progress bar with only: **1 Reference No > 2 Documents > 3 Review**.

1. **Reference No**: required, unique (keep the PHP + UNIQUE index check and the clear duplicate error, keep entered data on error).
2. **Documents**: keep exactly the previous rule: **7 upload slots always visible, at least 2 required, the other 5 optional**, PDF/JPG/JPEG/PNG, max 5 MB each, same secure upload handling, preview thumbnails, live "2 of 2 required uploaded" counter.
3. **Review**: shows the reference number and the uploaded documents (thumbnails/file names), then the Submit button.

Keep the glass/liquid design, mobile-first layout and sticky Next/Back buttons. Because there are only 3 short steps, make it feel fast: big tap targets, minimal text, no unnecessary hints.

**Payment popup stays exactly as before** (sender name, sender phone, price, opens automatically after the archive is saved, bottom sheet on mobile, receipt button, "Payment pending" badge if skipped). The payment-to-archive link (`payments.archive_id`) stays.

---

## 3. Archive list / Search page changes

- Remove the **"Seller / Buyer"** column (desktop table) and the seller to buyer line (mobile cards).
- Show a single **"Buyer"** column / line instead.
- **IMPORTANT (conflict resolved by assumption):** because the Buyer input no longer exists in the form, the **Buyer column displays the payment sender's name** (the person who paid the notary, from `payments.sender_name`). If the archive has no payment yet, show "-" together with the "Payment pending" badge.
  - Make the column label a single constant/variable so it can be renamed in one place.
  - If the client later confirms he wants a real Buyer name field in the form, it will be added as one optional text field; do not add it now.
- The list columns/cards are now: **Reference No, Buyer (payer), Documents count, Amount, Payment status (Paid/Pending), Date, Entered by (admin view), Actions**.
- **Search** now matches only: **reference number, payment sender name, payment sender phone**. Remove seller, buyer, and witness search. The search must still return archive + payment together (LEFT JOIN on `payments`). Keep filter chips (Paid / Pending, date range) and the staff-role limits (staff: view/search only; admin: full CRUD).
- Update the placeholder text of the search bar to: "Search by reference no, payer name or phone".

---

## 4. Other pages that must be updated

- **View Archive**: only sections: Header (reference no, status badges, who entered it and when), **Documents** (preview, lightbox, download), **Payment** (sender, phone, amount, date, received by). Remove seller, buyer, witnesses, ID card and MoJ sections.
- **Edit Archive (admin)**: only reference no (unique, ignoring current record) and the documents (replace/remove individual files while keeping the minimum of 2). Old files deleted from disk when replaced.
- **Delete Archive (admin)**: still deletes the document files from disk. For legacy data (see section 5), also delete any leftover old files (seller/buyer/witness/ID/MoJ) from disk.
- **Receipt/print**: reference no, payer name, phone, amount, date, office name + logo. No seller/buyer.
- **Staff Performance, Activity Log, auto logout, PWA, logo, role permissions, payments admin pages, staff user CRUD**: **unchanged**. Only make sure nothing in them references removed fields (e.g. activity log text "archive created" must work without seller/buyer data).
- Validation messages, README, and any help text must be updated so they do not mention removed fields.

---

## 5. Database changes (safe, no data loss)

The system may already contain real archives, so **do not blindly drop data**.

- Provide `update_v4.sql` that makes the legacy columns **nullable** and stops the application from using them: `seller_name, seller_phone, seller_image, buyer_name, buyer_phone, buyer_image, id_card_file, moj_document_file, moj_document_number` on `archives`, and keeps the `witnesses` table untouched but unused.
- The application must no longer require, read or write these fields. Old archives must still open and show their reference number, documents and payment without errors.
- Update `database.sql` (fresh install) so a **new** database is created **without** those columns and without the `witnesses` table.
- Optional, separate and clearly labelled script `cleanup_legacy.sql` (do not run automatically) that drops the legacy columns/table after the client confirms and a backup is taken. Mention in the README: "take a backup (phpMyAdmin export) before running cleanup".
- Add an index on `payments.sender_name` and `payments.sender_phone` for fast search if not present.
- Remove the now-unused upload folders handling (`people`, `ids`, `moj`) from the code; keep `documents`. Do not delete existing files in them automatically.

---

## 6. Code cleanup requirements
- Delete dead code, unused CSS classes, unused JS modules and unused form partials related to removed fields. No commented-out leftovers.
- Keep the code organized and consistent; the file structure stays the same except removed files/sections.
- Server-side validation must match the new form exactly: reference no unique and required, at least 2 documents, valid file types/sizes. Reject unexpected extra POST/file fields (ignore them safely).

---

## 7. Test before finishing
1. Add Archive wizard shows only 3 steps (Reference No, Documents, Review) at 320, 390, 768 and 1440 px, light and dark mode.
2. Duplicate reference no is rejected; fewer than 2 documents is rejected; a valid submit saves and opens the payment popup; payment saves and links to the archive.
3. No seller, buyer, witness, ID card or Ministry of Justice input or text appears anywhere (form, review, view, edit, list, search, receipt, README).
4. The list/search shows a single **Buyer** column (payer name) and no "Seller / Buyer"; searching by reference no, payer name and payer phone returns the archive with its payment; searching by a seller/witness name returns nothing.
5. Old archives created before this change still display correctly (reference, documents, payment) and can be deleted with their files removed.
6. Staff can still only add, search and view; admin can still edit/delete archives and payments and manage staff; auto logout, PWA, logo and staff performance pages still work.
7. No PHP warnings/notices (enable `error_reporting(E_ALL)` during testing) and no console errors in the browser.

Report back with a short summary of what was removed, what was changed, and the SQL files to run.
