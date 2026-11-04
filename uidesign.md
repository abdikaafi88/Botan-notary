# ADDITIONAL PROMPT: UI/UX Design Requirements (replaces Section 10 of the main prompt)

Apply everything below to **every page** of the Notary Archive & Payment System. This section overrides the earlier "UI/UX" section. The goal: a **premium, professional, modern interface** that mixes **iOS "Liquid Glass"** and **Glassmorphism**, is extremely **simple to use**, and is **perfectly responsive on mobile (mobile-first)**.

Stack stays the same: plain HTML + CSS (+ small vanilla JS). No Bootstrap/Tailwind/frameworks. Icons: inline SVG or a lightweight CDN icon set (e.g. Lucide/Bootstrap Icons). Font: `Inter` (Google Fonts) with a system-font fallback (`-apple-system, "SF Pro Text", "Segoe UI", Roboto, sans-serif`).

---

## 1. Visual style: Liquid Glass + Glassmorphism

**Background**
- A soft, colorful, animated-gradient "mesh" background (deep navy/indigo to teal/blue, with a few blurred floating color blobs using `radial-gradient` and slow CSS animation). Keep it subtle so text stays readable.
- Light theme by default plus a **dark mode toggle** (store choice in `localStorage`, also respect `prefers-color-scheme`).

**Glass surfaces** (cards, sidebar, top bar, modals, inputs, dropdowns)
```css
background: rgba(255,255,255,0.14);          /* dark mode: rgba(20,24,40,0.45) */
backdrop-filter: blur(22px) saturate(160%);
-webkit-backdrop-filter: blur(22px) saturate(160%);
border: 1px solid rgba(255,255,255,0.28);
border-radius: 22px;
box-shadow: 0 8px 32px rgba(10,20,60,0.18), inset 0 1px 0 rgba(255,255,255,0.45);
```
- Add the Liquid Glass feel: a soft **specular highlight** (thin light gradient on the top edge using a `::before` pseudo-element), smooth large radii (16-28px), gentle inner glow, and layered depth (cards slightly lift on hover with `translateY(-2px)` and stronger shadow).
- Provide a **fallback** for browsers without `backdrop-filter` (use a more opaque solid background) so nothing becomes unreadable.
- **Contrast rule:** text on glass must meet WCAG AA (4.5:1). Use dark text on light glass and light text on dark glass; add a slightly stronger glass tint behind long text and tables.

**Design tokens**: define all colors, radii, shadows, blur values, spacing and font sizes as CSS variables in `:root` (and a `[data-theme="dark"]` override) so the whole theme can be changed in one place.

**Buttons**
- Primary: pill/rounded glass button with a blue-to-indigo gradient, white text, soft glow, smooth press animation (`scale(.97)`).
- Secondary: translucent glass with border. Danger: soft red gradient (only for delete).
- Minimum touch size **48px height** on mobile. Clear hover, focus-visible and disabled states. Loading state (spinner) on submit buttons to prevent double submit.

**Inputs**
- Large, rounded, glass inputs (min 48px tall, 16px font so iOS does not zoom), floating or clearly placed labels, icon on the left, clear focus ring (soft blue glow), inline validation messages (green check / red message) next to the field.
- Use correct `type` and `inputmode` (`tel` + `inputmode="tel"` for phones, `inputmode="decimal"` for amount) so the right mobile keyboard appears.

**Motion**
- Subtle and fast (150-300ms, `ease-out`): fade/slide-in for pages, scale-in for modals, smooth tab/menu transitions, skeleton/shimmer loaders for lists.
- Respect `prefers-reduced-motion` (disable animations).

---

## 2. Layout and navigation

**Desktop (>= 1024px)**: floating glass **sidebar** (collapsible) + glass top bar (page title, global search, theme toggle, user menu with role badge).

**Tablet (640-1023px)**: collapsed icon-only sidebar or top bar with a hamburger drawer.

**Mobile (< 640px) - this is the priority**
- Replace the sidebar with a **floating glass bottom navigation bar** (like iOS tab bar) with 4-5 big icons + labels: Admin = Home, Archives, Add (center highlighted button), Payments, More. Staff = Home, Search, Add, More/Logout.
- Sticky compact glass top bar with the page title and a search icon that expands to a full-width search field.
- Respect device safe areas: `padding-bottom: env(safe-area-inset-bottom)` and `viewport-fit=cover`.
- No horizontal scrolling anywhere. Test widths: **320px, 360px, 390px, 414px, 768px, 1024px, 1440px**.

Always include `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">`. Use mobile-first CSS (base styles for phones, `min-width` media queries for larger screens), CSS Grid/Flexbox, `clamp()` for fluid font sizes and spacing, and `rem` units.

---

## 3. Page-specific UX requirements

**Login**: centered glass card on the animated background, big logo/title ("Notary Office"), username + password with show/hide eye icon, clear error message, full-width button. Nothing else on the page.

**Dashboard**: glass stat cards (Total archives, Total payments, Money collected, Today) in a responsive grid (4 columns on desktop, 2 on tablet, 1-2 on mobile) with icons and number count-up animation. Staff dashboard: two huge, obvious action cards: **"Add New Archive"** and **"Search Archive"**.

**Add Archive form (the most important screen - make it effortless)**
- Break the long form into a **step-by-step wizard** with a progress bar: 1 Reference > 2 Documents > 3 Seller > 4 Buyer > 5 Witnesses > 6 ID Card > Review. Big "Next / Back" buttons (sticky at the bottom on mobile). Validate each step before moving on, and keep entered data when going back.
- Each upload field is a **big glass drop-zone / tap-to-upload tile** with: icon, label, "Required" or "Optional" badge, file type + size hint, instant **image preview thumbnail** (PDF shows a file icon + name), a remove (x) button, and on mobile the option to **take a photo with the camera** (`accept="image/*"` + `capture` option for person photos and ID card).
- Documents step shows 7 tiles with a live counter "2 of 2 required uploaded" that turns green when satisfied. Witness step shows 4 witness cards with a counter "minimum 2 witnesses" (turns green when done).
- A final **Review screen** summarizes everything (with thumbnails) before the user presses Submit.
- Show upload progress (progress bar) when submitting, and a clear success state.

**Payment popup (modal)**
- Opens automatically after a successful archive save, with a smooth scale-in. On desktop: centered glass modal. On mobile: a **bottom sheet** that slides up from the bottom with a drag handle, full width, rounded top corners.
- Only 3 fields (Sender name, Sender phone, Price), the archive reference shown at the top as a badge, a large amount input, and one big "Save Payment" button. After saving: animated success checkmark and buttons "Print receipt" / "Add another archive".

**Archive list / Search**
- Prominent glass search bar at the top with instant (debounced) search and filter chips (Paid / Pending, date range).
- Desktop: clean glass table with sticky header, row hover, status badges (green "Paid", amber "Pending").
- **Mobile: do NOT use a wide table.** Convert each row into a **stacked glass card** showing reference number, seller to buyer, date, amount + status badge, with a clear "View" button (and Edit/Delete icons for admin only).
- Pagination or "Load more" button, with skeleton loaders and a friendly empty state with an illustration/icon.

**Archive view**: header card with reference number + status badges + action buttons; sections as collapsible glass cards (Documents, Seller, Buyer, Witnesses, ID Card, Payment). Documents/images open in a **lightbox** preview (swipe/close on mobile) with a download button.

**Admin pages (users, payments)**: same card-on-mobile / table-on-desktop pattern. Forms for add/edit user open as modals or clean separate glass pages. Delete always asks confirmation in a styled glass dialog (never the browser `confirm()`), red confirm button.

**Feedback**: replace plain alerts with **glass toast notifications** (top-right on desktop, top-center on mobile) for success/error/info that auto-dismiss.

---

## 4. UX principles to follow
- **Simplicity first:** one primary action per screen, short labels, plain language, no clutter. A new staff member must understand the system in under 2 minutes without training.
- Consistent patterns (same buttons, same colors for same meaning: blue = main, green = success, amber = pending, red = delete).
- Clear required marks (*) and helpful hints under fields; error messages say exactly what to fix.
- Preserve form data after validation errors; never make the user retype.
- Prevent double submits; show loading states for anything that takes > 300ms.
- Accessibility: semantic HTML, `label` for every input, `aria-label` on icon buttons, visible `:focus-visible` rings, keyboard navigable modals (Esc to close, focus trap), alt text on images.
- Performance: compress/resize large uploaded images client-side before upload when possible (canvas, max ~1600px) so mobile uploads are fast; lazy-load images (`loading="lazy"`); keep CSS in one organized file (`assets/css/style.css`) and JS in `assets/js/app.js`; no heavy libraries.
- Print stylesheet: the receipt and archive view print cleanly (white background, no glass, no navigation).

---

## 5. Definition of done (check before finishing)
1. Every page looks consistent in glass/liquid style in both light and dark mode.
2. No horizontal scroll and no overlapping/cut-off elements at 320px, 390px, 768px, 1024px, 1440px.
3. On phones: bottom navigation works, tap targets are at least 48px, inputs do not trigger zoom, camera upload works, the payment popup appears as a bottom sheet.
4. Lists become cards on mobile; tables only on desktop.
5. The add-archive wizard validates every step and the review screen shows all data before submit.
6. Text contrast passes AA on glass backgrounds; a fallback exists if `backdrop-filter` is unsupported.
7. All functional requirements from the main prompt still work (roles, validation, uniqueness, archive-payment link, search, CRUD).

Implement this design system first (CSS variables, glass components, layout, bottom nav, toasts, modal/bottom sheet), then build every page on top of it.