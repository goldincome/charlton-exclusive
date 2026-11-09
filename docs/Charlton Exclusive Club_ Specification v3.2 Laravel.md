# Charlton Exclusive Club: Specification v3.2 (Laravel Edition)

Stack: **Laravel 13 (PHP 8.3+), Blade, MySQL 8.4 LTS**, Stripe (Laravel Cashier), **ZeptoMail** (transactional email), **local file storage**. Single Laravel application (replaces the v3.1 monorepo `/client`, `/server`, `/shared`). **\[Assumption\]** marks a default I chose where you gave no answer. Confirm these in section 10.

## 0. Changes from v2

- Management Council role and support step **removed**.
- Hall morning/afternoon periods and the 11:00 rule **removed**.
- Hall, table and event rates and member discounts are **fully admin-set** (percentage or flat).
- Club sells food; caterer handled **offline** (no caterer integration).
- Membership paid **yearly or monthly**; membership fees **not VATable**.
- Gallery visibility is **per photo** (public or members-only).
- SendGrid, S3 and Cloudinary dropped; **ZeptoMail** for transactional email; files stored **locally**.
- v3.1: confirmed VAT-registered, sectional fees recurring, monthly fee separate, grace/replacement periods, local storage, club contact details.

## 0.1 Changes from v3.1 (stack migration only)

Business rules, roles, lifecycles, refund tiers, VAT, entities and policies are **unchanged**. Only stack-dependent items changed:

| v3.1 | v3.2 |
| --- | --- |
| React + TypeScript client, Node/Express API | Laravel 13 with server-rendered **Blade** views (Alpine.js for small interactions, Livewire optional for dashboards) |
| PostgreSQL | **MySQL 8.4 LTS** (InnoDB, utf8mb4) |
| Postgres `tstzrange` exclusion constraint | MySQL-safe double-booking protection: transactional row lock plus a unique `booking_slot` table (section 2) |
| httpOnly JWT cookie + rotating refresh token | Laravel session authentication (Laravel Fortify) with encrypted httpOnly session cookie |
| Zod validation | Laravel Form Request validation |
| Helmet | Security-headers middleware |
| Prisma | Eloquent models and migrations |
| BullMQ | Laravel Queues (database driver at launch, Redis optional) and Task Scheduler |
| Nodemailer / `MailService` | Laravel Mail with a ZeptoMail SMTP mailer, behind a `MailService` interface |
| `StorageService` | Laravel Filesystem (`local` disk, private), behind a `StorageService` interface |
| pnpm workspaces, Vitest, Playwright | Composer, npm with Vite for assets, Pest (PHPUnit), Laravel Dusk or Playwright |
| Stripe SDK for Node | Laravel Cashier (Stripe) plus the Stripe PHP SDK |
| OpenAPI | Only for any JSON endpoints exposed (Scramble or Scribe) \[Assumption\] |

---

## 1. Roles

| Role | Type | Access |
| --- | --- | --- |
| Admin | Login role (MFA) | Everything |
| Staff | Login role (MFA) | Table/hall bookings, food orders, check-in. No finance or membership. |
| Member | Login role | Member rates, dashboard, sponsorship |
| Customer (guest/applicant) | Login role | Bookings at standard rates, applications, status tracking |
| Screening Committee | Flag on a user | Review applications, run interviews, **recommend** |
| Sponsor | Relationship | A member nominated by an applicant; sees status only |

A committee member cannot review an application they sponsor.

Implementation: roles and permissions via `spatie/laravel-permission` \[Assumption\]; enforced with Laravel Policies and Gates. The committee flag is a boolean on `user`.

| Action | Customer | Member | Staff | Committee | Admin |
| --- | --- | --- | --- | --- | --- |
| Book hall, table, event; order food | Standard rate | Member rate | View/manage | – | Y |
| Approve/reject bookings and orders | – | – | **No** | – | Y |
| Check in guests/members | – | – | Y | – | Y |
| Apply for membership | Y | – | – | – | Y |
| Sponsor an applicant | – | If eligible | – | – | – |
| View applications | Own | Sponsored (status only) | No | Full | Full |
| Record interview and recommendation | – | – | – | Y | Y |
| **Approve/reject application (final)** | – | – | – | – | **Y** |
| Payments, refunds, reports, config | Own | Own | No | – | Y |

---

## 2. Shared booking lifecycle (tables, halls, events, food)

```
Requested -> Approved (awaiting payment, price locked) -> Confirmed -> Checked In -> Completed
Requested -> Rejected (reason required)
Requested -> Expired (no admin action in 72h; reminder 24h, escalation 48h)
Approved  -> Expired (unpaid after 72h, slot released)
Confirmed -> Cancelled (policy in 6)
Confirmed -> No-show (staff marks 30 min after start)
```

Admin alone approves/rejects. Staff may check in and mark Completed or No-show. A Requested or Approved booking **holds** its slot. MySQL has no range exclusion constraint, so no double-booking under concurrency is enforced in two layers:

1. **Pessimistic lock:** the booking is created inside `DB::transaction()`; the parent resource row (hall, table or event) is locked with `lockForUpdate()` and the overlap check (including buffer) runs before insert.
2. **Database guarantee:** a `booking_slot` table holds one row per resource per 30-minute slot (including buffer slots) with a **unique index** on `(resource_type, resource_id, slot_start)`. Slot rows are inserted in the same transaction as the booking; a duplicate-key error means the slot is taken. Slots are deleted when a booking is Rejected, Expired or Cancelled.

Price and discount are shown at request and **locked at approval**. Times stored UTC (`app.timezone` = UTC, `DATETIME`/Carbon), displayed Europe/London.

---

## 3. Modules

### 3.1 Tables

`table_category` (standard price, member discount type percent|flat, value) and `dining_table` (number, capacity, section, category, active). Non-members book at standard price.

### 3.2 Events

Fields as original, plus tickets per booking and price per ticket. Member discount (percent or flat per ticket) set by admin per event. Capacity checked at request, approval and payment (inside a locked transaction). Event cancelled by the club means automatic full refunds.

### 3.3 Gallery

Admin uploads photos (bulk, event-tagged). **Each photo** has visibility `public` or `members_only`. Public pages and logged-out users see only public photos. Lightbox, filter by event/date. Types JPEG/PNG/WebP, 10 MB max, EXIF stripped, thumbnails generated.

### 3.4 Halls / meeting rooms

- Room: name, description, capacity, amenities, multiple images, opening hours per weekday, buffer minutes (default 30).
- **Rates are admin-set per room and day type** in `room_rate`: day type = Mon..Sun or Bank Holiday; standard price per hour; member discount type (percent | flat per hour) and value.
- Seed defaults: Mon-Thu £120/h, Fri £90/h, member discount 20%. **A day type with no rate is not bookable.** Weekends and bank holidays become bookable when admin sets a rate.
- Bank holidays: admin-maintained `bank_holiday` table (seeded with England and Wales dates).
- Rules: minimum 3 hours, 30-minute increments, booking must fit within opening hours, no overlap incl. buffer. Validated in the browser (Alpine.js), authoritatively in the Form Request and booking service, and shown to the admin at approval.
- Inclusions displayed at booking and confirmation: discount on Century Dining Room food (beverages excluded), prices include VAT (see 5).

### 3.5 Food

- Menu: categories, items, descriptions, prices, images, **14 UK allergen flags**, `discount_exempt` flag (beverages).
- Personal and party orders. Fields: items, quantities, date/time, dietary notes, delivery/table location, contact.
- The **club is the seller**. Admin sets the **hall-booking food discount** in `app_config` (percent or flat; default 20%). It applies automatically to non-exempt items when the order is linked to a Confirmed hall booking.
- Admin approves, then Stripe payment. Staff view orders and may mark Fulfilled. Caterer coordination is offline; no integration is built.

### 3.6 Membership

**Catalogue.** Admin creates:

- `membership_category` (e.g. Single, Double, Corporate): description, benefits, entrance fee, annual fee, optional monthly fee, min sections (default 2).
- `section` (e.g. Gym, Tennis, Snooker): description, fee, billing recurring annually.
- Category-to-section availability.

**Double** = primary member plus partner: one subscription, two profiles with separate logins, fees set by admin on the category. **Corporate** = one organisation with nominated individuals (limit set on the category).

**Sponsors**

- Applicant nominates **2** members. Eligible = Active, financial, joined at least 3 years ago, not suspended, and under their **sponsor cap**.
- **Sponsor cap** = maximum applicants a member may sponsor concurrently (pending applications). **0 = unlimited.** Global default in config, per-member override by admin.
- A nominated sponsor can **Accept** or **Decline** by email link (signed URL) or dashboard. After accepting, a sponsor can **Withdraw support** any time before the admin decision, with a reason (visible to committee and admin only **\[Assumption\]**).
- On decline or withdrawal the applicant is notified and has **14 days** to nominate a replacement, or the application expires **\[Assumption\]**.
- At least one sponsor must attend the interview.

**Application status flow**

```
Draft
 -> Fee Pending (only if application fee active; non-refundable)
 -> Submitted
 -> Awaiting Sponsors (2 accepted)
 -> Under Review (committee)
 -> Interview Scheduled
 -> Interview Completed (committee recommends Eligible | Ineligible)
 -> Admin Decision: Approved | Rejected
 Approved -> Fees Notified (14-day payment window) -> Payment Received -> Active
 Rejected -> formal PDF letter -> Re-application allowed (no fee, evidence upload)
Any stage -> Withdrawn | Expired
```

Admin may differ from the committee recommendation but must record a reason (audited). Re-application is free; the applicant uploads evidence the basis of refusal has been addressed, and the committee reviews it.

**Subscriptions.** One-year term, paid **yearly or monthly** (member chooses, using Stripe Subscriptions through Laravel Cashier).

- Anniversary renewal. Reminders 60/30/7 days before annual renewals.
- **Financial** = subscription up to date, with no overdue invoices beyond grace.
- Failed payment: Stripe retries; **14-day grace** then status Suspended (loses member rates and booking rights, cannot sponsor); admin may terminate after 90 days suspended **\[Assumption\]**.

---

## 4. Payments (Stripe)

- GBP, integer pence (MySQL `BIGINT UNSIGNED`). Stripe Checkout for one-off payments; Subscriptions for membership (Laravel Cashier).
- First membership payment is one invoice with line items: entrance fee, category fee, section fees, partner fee.
- Webhooks: signature verification (Cashier webhook controller, extended), idempotency via unique `stripe_event_id`, nightly reconciliation (scheduled command). Handle payment succeeded/failed, refunded, dispute created, invoice events.
- Admin refund screen (full or partial, reason required, audited). Receipts available to the payer.
- Reports: revenue by module and period, refunds, VAT summary, CSV export.

## 5. VAT and invoices

- `vat_rate` is configured **per product type**. Membership entrance, category and section fees: **0% / outside scope**, no VAT shown.
- The club is **VAT-registered**: halls, tables, events and food are displayed **VAT-inclusive** at the standard UK rate (20%, stored per product type). Store the VAT number in config; it is printed on every VAT invoice.
- Invoice/receipt contains: sequential number, issue date, club legal name, address, VAT number (if any), customer, line items, net, VAT (per rate), gross, payment method and reference, refund notes. Sequential numbers are generated inside a transaction with a locked counter row so numbers never repeat or skip. Invoice PDFs are rendered from Blade templates (DomPDF or Browsershot) \[Assumption\].

## 6. Cancellation, refund and no-show policy (generated, editable by admin)

| Item | Cancel timing | Refund |
| --- | --- | --- |
| Hall booking | 7+ days before | 100% |
|  | 48 hours to 7 days | 50% |
|  | Under 48 hours | 0% |
| Table booking | 24+ hours before | 100% |
|  | Under 24 hours | 0% |
| Event tickets | 7+ days before | 100% |
|  | Under 7 days | 0% unless the club can resell |
| Food order (personal) | 24+ hours before | 100% |
| Food order (party) | 72+ hours before | 100% |
|  | 24-72 hours | 50% |
|  | Under 24 hours | 0% |
| Application fee | Any time | Not refundable |
| Entrance and sectional fees | After activation | Not refundable |
| Annual subscription | Mid-term | No pro-rata refund; membership ends at term end |
| Monthly subscription | Any time | Cancel before next cycle; no refund of the current month |

Other rules: club-initiated cancellation = full refund. No-show = no refund. Three no-shows in 12 months lets admin restrict online booking. Payment must be completed within **72 hours** of approval. Admin may override any refund with a recorded reason. The thresholds are stored in config (`app_config` table), not hardcoded.

## 7. Email (ZeptoMail)

Send through ZeptoMail via Laravel Mail (SMTP mailer, or its API through a custom transport), behind a `MailService` interface. Templates (Blade Mailables/Markdown mail): application received, sponsor nomination/reminder, sponsor decline/withdrawal, status updates, interview invite, booking approved/rejected, payment link and receipt, renewal reminders, formal rejection letter (PDF attached). Mail is queued. Use an outbox table (`notification_outbox`) with delivery status, and configure SPF/DKIM/DMARC.

**Newsletters and marketing are out of scope for ZeptoMail.** Zoho's documentation states ZeptoMail supports transactional email only and that accounts used consistently for promotional or bulk mail are blocked automatically, which would also stop receipts and approvals. Throttling sends does not change that rule. If the club wants newsletters, use a separate provider (e.g. Zoho Campaigns) with its own sending domain, and keep marketing consent separate in the data model.

## 8. Security, privacy, infrastructure

- Laravel session authentication (encrypted, httpOnly, SameSite cookie, session regeneration on login), CSRF protection (built in), email verification, password reset, rate limiting (`RateLimiter`), lockout (Fortify login throttling), TOTP MFA for Admin and Staff (Fortify two-factor authentication), Form Request validation, security-headers middleware (CSP, HSTS, X-Frame-Options, Referrer-Policy).
- UK GDPR: lawful-basis register, subject access/erasure workflow (financial records kept 6 years), retention schedule, special-category handling for dietary/health data, processor agreements (Stripe, ZeptoMail, hosting), breach procedure, ICO registration.
- Versioned, admin-editable legal pages; accepted version stored per user. Cookie banner with categories and consent log.
- **File storage (local):** images are saved to a dedicated private disk outside the web root (`storage/app/private`, on a persistent volume mounted in Docker). Public photos and room/event/menu images are served through a controller with caching headers; **members-only photos are served only through an authenticated route**, never as static files and never through the `public/storage` symlink. Random file names, type/size checks, EXIF stripping, thumbnails (Intervention Image). Keep a `StorageService` interface so the filesystem disk can be swapped later. **Back up the upload directory with the database** (daily, with periodic restore tests, for example `spatie/laravel-backup`), since local disk is a single point of failure.
- Audit log for approvals, refunds, decisions, role changes, config changes.
- Background jobs (Laravel Queues and Scheduler): approval reminders, expiry, renewals, interview reminders, reconciliation.
- WCAG 2.1 AA, Composer and npm (Vite), Eloquent migrations, Pest, Laravel Dusk or Playwright, Docker, CI/CD, backups, monitoring.
- Tests required: rate and discount calculation, hall rules and overlap/concurrency (including the `booking_slot` unique-index path), sponsor eligibility and cap, webhook idempotency, RBAC, refund tiers.

## 9. Entities

`user`, `user_role`, `member_profile`, `membership_category`, `section`, `category_section`, `membership`, `membership_person`, `application`, `application_section`, `sponsorship` (status: pending/accepted/declined/withdrawn, reason), `interview`, `interview_attendee`, `decision_letter`, `table_category`, `dining_table`, `hall`, `hall_image`, `hall_feature`, `hall_hours`, `room_rate`, `bank_holiday`, `event`, `gallery_album`, `gallery_photo` (visibility), `booking`, `booking_slot` (new in v3.2, MySQL overlap guard), `menu_category`, `menu_item`, `menu_item_allergen`, `food_order`, `food_order_item`, `invoice`, `invoice_line_item`, `payment`, `refund`, `stripe_event`, `check_in`, `legal_document`, `legal_acceptance`, `consent_log`, `contact_message`, `notification_outbox`, `audit_log`, `app_config`.

Laravel framework tables are also present: `sessions`, `jobs`, `failed_jobs`, `cache`, `password_reset_tokens`, `permissions`/`roles` (if spatie is used), and Cashier's subscription tables.

## 10. Remaining items

Resolved: storage (local), VAT-registered, sectional fees recurring, separate monthly fee, grace (14 days), termination (90 days suspended), sponsor replacement window (14 days).

Still open:

1. **VAT number** (required on invoices), plus \[COMPANY NUMBER\] and \[ICO REGISTRATION NUMBER\] for the legal pages.
2. Whether withdrawal reasons stay hidden from the applicant (assumed hidden).
3. Default room opening hours, and rates for weekends and bank holidays (admin can set these after launch; days without a rate stay unbookable).
4. Newsletter provider, if wanted (not ZeptoMail).
5. Solicitor review of the legal pages.
6. Stack confirmations (assumptions in v3.2): Laravel 13 on PHP 8.3+; MySQL 8.4 LTS; `spatie/laravel-permission` for roles; Alpine.js (Livewire optional) for interactivity; database queue driver at launch; DomPDF or Browsershot for PDFs; hosting with PHP-FPM, a queue worker and a cron entry for `schedule:run`.
