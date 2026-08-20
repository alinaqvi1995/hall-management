# Hall Management

Booking, billing and back-office system for Pakistani marquees, banquet halls,
lawns and farmhouses. Multi-venue: one installation runs several halls, and each
hall's staff see only their own venue.

Built on Laravel 12 with a Bootstrap 5 admin theme (no asset build step).

---

## Requirements

- PHP 8.2+
- MySQL / MariaDB 10.4+
- Composer 2

Node is optional — it is only used by the `composer dev` helper.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database and point `.env` at it:

```sql
CREATE DATABASE hall_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

> **XAMPP note:** its MariaDB frequently listens on **3307**, not 3306.
> Check `DB_PORT` against `xampp/mysql/bin/my.ini` if the connection is refused.

Then migrate, seed and serve:

```bash
php artisan migrate --seed
php artisan storage:link      # required for invoice PDFs and hall logos
php artisan serve
```

Open http://127.0.0.1:8000.

### Seeded accounts

All seeded accounts use the password `12345678`.

| Email                  | Role        | Can do                                                   |
| ---------------------- | ----------- | -------------------------------------------------------- |
| `superadmin@mail.com`  | Super Admin | Everything, across every venue                           |
| `halladmin1@mail.com`  | Hall Admin  | Runs one venue end to end, including its books and staff  |
| `manager1@mail.com`    | Manager     | Takes bookings and collects payments; cannot delete       |
| `staff1@mail.com`      | Staff       | Read-only on bookings, packages and customers            |

`halladmin2` / `manager2` / `staff2` (and `3`) exist for the other two seeded
venues. The seeder also creates three venues with lawns, packages, add-ons,
staff, vendors, ~17 bookings each with payments, and two months of expenses, so
every screen has realistic data on a fresh install.

**Change these passwords before deploying anywhere real.**

---

## What the system does

### Bookings

A booking reserves one **bookable space** (a lawn, hall or marquee inside a
venue) for a date-time window. Availability is checked per space, so two events
can run at once in different spaces of the same venue.

- Slot collisions are rejected inside a transaction with the space row locked,
  so two clerks saving the same slot at the same moment cannot both succeed.
- Windows are **half-open**: an event ending at 18:00 does not block one
  starting at 18:00, so back-to-back events are legal.
- Cancelling releases the slot but keeps the record, the reason, and who
  cancelled it. Cancelled bookings never block a date.

### Pricing

Quoted the way Pakistani marquees actually quote:

```
catering   = guests × per-head rate      (from the chosen package, or a custom rate)
add-ons    = Σ (price × qty × [guests if the add-on is charged per head])
subtotal   = catering + add-ons + hall rent − discount
total      = subtotal + tax
```

**Catering is optional.** Plenty of customers rent the venue only and bring
their own caterer, so a booking can carry hall rent alone — tick *"Customer is
arranging their own catering"* on the booking form and the package and per-head
rate are cleared and disabled. The invoice, booking page and daily ops sheet all
drop the catering line and show *"own caterer"* instead of a Rs. 0 row.

A booking must still charge for *something* — hall rent, catering, or at least
one paid extra service — otherwise it would invoice zero and is rejected.

Every amount is recomputed server-side on save, so a tampered form field cannot
change what a customer is charged. Add-on prices are **copied onto the booking**
at save time, so later price-list edits never rewrite an agreed bill.

### Payments

A ledger of receipts and refunds, not a single "advance paid" figure — halls
collect in instalments (booking advance, second instalment, settlement on the
day). `payment_status` is derived from the ledger, never set by hand.

The **advance can be taken on the booking form itself** — enter the amount,
method and date and the first receipt is created with the booking. The two commit
or roll back together, so a rejected advance never leaves a booking behind with
the money unrecorded. Further instalments are added from the booking page.

Guard rails: a receipt cannot exceed the outstanding balance, an advance cannot
exceed the bill it is paid against, and a refund cannot exceed what was actually
collected. Every entry gets a sequential receipt number and a printable receipt.

### Expenses and profit

- **Linked to a booking** — catering, decor, event staff. Subtracted from what
  that event collected to give its margin.
- **Unlinked** — electricity, gas, salaries, taxes, maintenance. Counted against
  the venue as a whole.

### Reports

| Report              | Answers                                                 |
| ------------------- | ------------------------------------------------------- |
| Business Summary    | Billed, collected, outstanding, expenses, profit, occupancy |
| Outstanding Dues    | Who still owes money, soonest event first                |
| Daily Event Sheet   | Everything happening on one day — printable ops sheet    |
| Profit per Event    | Collected less linked costs, per booking                 |

### Also included

Menus/packages with per-head rates and minimum guest counts · priced extra
services (fixed or per head) · customer history with lifetime value and a
blacklist · staff records and payroll totals · vendor records with spend ·
invoices as HTML and PDF, shareable by WhatsApp or email · NTN/GST fields on the
invoice · activity log · trusted IPs · OTP verification.

---

## Roles and permissions

Permissions are `<action>-<module>` slugs (`view-bookings`, `create-payments`).
The catalogue lives in `database/seeders/PermissionSeeder.php`; which role gets
what is a single matrix in `database/seeders/RolePermissionSeeder.php`.

After changing either, re-run:

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

Super admin bypasses every check via `Gate::before`. Everyone else is confined
to the hall on their user record — enforced by the `BelongsToHall` trait's
`visibleTo()` scope, which every operational query goes through, plus the
`ResolvesCurrentHall` trait on the controller side.

Your own profile is never permission-gated: any signed-in user can maintain
their own account and password.

---

## Architecture

```
app/
  Http/Controllers/     thin — authorise, delegate, redirect
  Http/Requests/        validation, including cross-field business rules
  Services/
    BookingService      availability, slot locking, pricing, cancellation
    PaymentService      ledger writes, status derivation, receipt numbering
    ReportService       aggregates for the dashboard and reports
    InvoiceService      PDF rendering and WhatsApp/email share links
    HallService         venue + lawn syncing, logo storage
    CustomerService     find-or-create matched on CNIC
  Traits/
    BelongsToHall       hall relation + visibleTo() tenancy scope
    ResolvesCurrentHall controller-side hall resolution and write guards
  Policies/             Hall, Booking and User authorisation
resources/views/
  components/           page-header, stat-card, empty-state, money, data-table
  dashboard/includes/   layout, sidebar, topbar, flash messages
public/admin/
  css/app.css           application styling (loads after the vendor theme)
  css/auth.css          sign-in screens
  js/app.js             theme toggle, DataTables, Select2, submit guards
```

Money is always `decimal` in the database and formatted through the `<x-money>`
component, so amounts are consistent and never drift through float arithmetic.

### Colour system

The brand is a single orange (`#fc5523`) and it is wired into Bootstrap rather
than sitting beside it. `public/admin/css/app.css` repoints `--bs-primary` and
the whole `--bs-primary-*` family at the brand, so `btn-primary`, `bg-primary`,
focus rings, active tabs and pagination all follow it. Before this the theme
shipped stock blue (`#0d6efd`), which clashed with the orange sidebar.

Two things to know when adding UI:

- **Bootstrap scopes colours to per-component custom properties** on the
  component's own class — `.dropdown-menu`, `.list-group`, `.pagination`,
  `.nav-pills`, `.progress`, `.accordion`, `.btn-close`. Overriding
  `--bs-primary` on `:root` does *not* reach them; each family is repointed
  explicitly in app.css. If a hover suddenly looks blue, that is why.
- **The vendor theme has its own accent, `#008cff`, separate from Bootstrap's
  blue.** It paints the sidebar hover / focus / `.mm-active` states in
  `sass/main.css` at four classes of specificity, so an override has to mirror
  that selector shape (`.sidebar-wrapper .sidebar-nav .metismenu …`) to win.
  Pace's page-load bar and a handful of demo components use it too.
- **When hunting a stray colour, match by hue, not by hex.** Grepping for
  `#0d6efd` misses `#008cff`, `#5897fb` and `#377dff`, which all read as blue.
- **`--hm-brand` for fills, `--hm-brand-text` for text.** The brand only
  reaches ~3.2:1 against white, so brand-coloured *text* and links use the
  darker `--hm-brand-text`; fills keep the true brand colour.

Status colours live in `Booking::STATUS_COLOURS` and are used by both the badges
and the calendar, so the legend cannot drift from the events. Reserve the
semantic badges (`success`/`danger`/`warning`) for status; use the `.chip` class
for neutral metadata such as role names or employment type.

### Performance notes

Things that were slow and why they are not any more — worth knowing before
adding a new list page:

- **Role and permission checks are cached per user instance.** Every `@can` in
  Blade runs through `Gate::before` → `hasRole()`. Without the cache on the
  `User` model, a long list page issued one `roles` query per check — hundreds
  per page.
- **Any list showing a balance must call `Booking::withPaymentTotals()`.** The
  `amount_paid` and `balance_due` accessors fall back to two `SUM` queries per
  row; the scope pre-aggregates them. `/bookings` went from 501 queries to 8.
- **Eager-load `creator`/`updater`** wherever a table shows who created a row.
- **One shared modal per page, not one per row.** The cities screen rendered a
  modal for every city (495 KB of HTML); it now reuses a single modal populated
  from `data-` attributes.
- **ApexCharts (~500 KB) is pushed only by the two screens that draw charts**,
  via the `vendor_scripts` stack, not by the layout.
- **DataTables uses `datatables.core.min.js` (85 KB).** The theme also ships
  `jquery.dataTables.min.js`, which is a 2.1 MB download build bundling jszip
  and pdfmake for export features this app does not use — do not link it.
- **Vendor assets are served from `public/admin`,** not a CDN, so a page does
  not wait on cross-origin round trips. Only the Material Icons font is remote.

### Front-end conventions

- Theme (light/dark) is stored in `localStorage` and applied before first paint.
- Wide tables scroll inside their card; the page body never scrolls sideways.
- Submit buttons disable themselves on submit, so a double-click cannot create
  the same booking or payment twice.
- Destructive buttons use `data-confirm="…"`.
- Invoices and receipts are standalone pages with their own print styles.

---

## Tests

```bash
php artisan test
```

Tests run against in-memory SQLite. `BookingFlowTest` covers the service layer
(pricing, slot collisions, back-to-back bookings, ledger maths, refund and
cancellation caps, hall scoping); `BookingHttpTest` covers the same flows over
HTTP including validation, tamper-resistance and tenancy isolation.

---

## Notes for deployment

- Set `APP_DEBUG=false` and `APP_ENV=production`.
- Change every seeded password.
- Run `php artisan storage:link`, or invoice PDFs and hall logos will 404.
- Configure a real `MAIL_MAILER`; the default only writes to the log.
- `QUEUE_CONNECTION=database` needs a worker: `php artisan queue:work`.
