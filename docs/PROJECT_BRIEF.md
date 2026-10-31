# Dukkani — Demo Build Brief

## Goal

Build one polished, real Laravel demo store for Dukkani that can be shown to
prospective clients and added to Mohannad's portfolio.

The demo is a **vertical slice of the future SaaS**, not a mockup. It uses a
multi-tenant-ready schema (`store_id` on tenant tables) but ships with one
seeded store and a demo login.

## Decisions

| Item | Decision |
|---|---|
| Product scope | Demo storefront + seller dashboard |
| Not in this build | Self-serve registration, billing, admin panel, WhatsApp API, online payments |
| Backend | Laravel 12 + PHP 8.5 |
| Frontend | Blade + Tailwind CSS 4 + Alpine.js + Vite |
| Database | SQLite locally and for the demo |
| Language | Arabic only, RTL |
| Hosting | Render (free tier initially, paid always-on optional) |
| Installs | Through proxy `http://192.168.49.1:8282` |
| Demo store | أمل بيكنز — Amal Bakes, Amman |
| WhatsApp | Placeholder demo number, no real messages |
| Images | Seeded royalty-free bakery photos, credited |
| Portfolio | Screenshots + case study + project entry |

## Milestones

| # | Milestone | Estimate |
|---|---|---|
| 1 | Foundation, auth, data model, demo seed | 1.5 days |
| 2 | Storefront, cart, checkout, confirmation/tracking | 2 days |
| 3 | Dashboard home, orders board, order workflow | 2 days |
| 4 | Products, customers, settings | 2 days |
| 5 | Analytics, Arabic polish, tests | 1.5 days |
| 6 | Render deploy, screenshots, portfolio card | 1 day |

## Progress

- [x] **Milestone 1 — Foundation, auth, data, seed** (completed Oct 30, 2026)
  - Laravel 12 + SQLite + Arabic locale + Asia/Amman timezone
  - 13 multi-tenant migrations, enums, models, services
  - Amal Bakes demo seed: 10 products, 14 customers, 32 orders, deposits,
    events, notifications, hours, blocked dates
  - Arabic login + one-click demo login
  - Dashboard shell with KPIs, 14-day revenue chart, today's schedule,
    recent orders, notification dropdown
  - 7 passing feature tests
- [x] **Milestone 2 — Storefront, cart, checkout, tracking** (completed Oct 30, 2026)
  - Storefront home, full menu, product pages with options and price deltas
  - Server-side menu filtering by category and search
  - Session cart: add, update quantity, remove, clear, suggestions, live totals
  - Checkout: customer details, delivery/pickup, schedule picker, payment method
  - `ScheduleService` validates lead time, store hours, blocked dates, and slots
    on the server (30-minute slots, Asia/Amman)
  - 30% deposit rules for CliQ/bank transfer, free pickup, 2 JOD delivery
  - Public confirmation and tracking page at `/orders/{public_token}`
  - Root `/` redirects to the active storefront
  - 24 passing feature tests
- [x] **Milestone 3 — Orders board and workflow** (completed Oct 30, 2026)
  - Orders board with list and kanban board views, status tabs with counts,
    search, date/fulfillment/payment filters, sorting, pagination, and stat strip
  - Order detail: workflow stepper, quick status actions, items and totals,
    customer card, deposit card, internal notes
  - WhatsApp template launcher that logs every opened message, plus message history
  - Full activity timeline (status changes, deposits, notes, messages) with actors
  - Dashboard home links into the board; notification dropdown opens orders and
    marks them read; sidebar shows a new-order badge
  - Store-scoped queries and server-side transition validation
  - 39 passing feature tests
- [x] **Milestone 4 — Products, customers, settings** (completed Oct 31, 2026)
  - Product catalog: grid/list with stat cards, status tabs, search, category
    filter, sorting, and pagination
  - Product create/edit: image upload or URL, category, lead time, featured and
    availability flags, and a dynamic option builder with price deltas
  - Availability toggle, soft delete, and a category manager
  - Customer book: search, tag filters, sorting, order counts, lifetime spend,
    last order, and full profile with linked order history
  - Editable customer details, notes, and tags with store-scoped phone uniqueness
  - Store settings: profile, logo/cover, contact and location, storefront link
  - Delivery/pickup toggles, delivery fee, deposit percentage, lead time, deposit
    instructions, seven-day working hours, and blocked dates
  - 67 passing feature tests
- [x] **Milestone 5 — Analytics, Arabic polish, tests** (completed Oct 31, 2026)
  - Analytics dashboard with 7/30/90-day windows and previous-period comparisons
  - Revenue trend, order-status mix, weekday load, fulfillment and payment splits
  - Top products (by revenue or quantity) and top customers with links to profiles
  - Period-aware analytics service scoped to the authenticated store
  - Arabic copy and RTL pass, custom favicon, and dashboard links into analytics
  - 74 passing feature tests
- [x] **Milestone 6 — Deploy and portfolio** (completed Oct 31, 2026)
  - Production container: multi-stage Dockerfile (Vite assets → Composer → Apache
    + PHP 8.4), Apache vhost, PHP production ini, and a boot entrypoint
  - `render.yaml` Blueprint: Docker web service on Render's free plan, health
    check, and the full production environment
  - Entrypoint handles storage setup, uploads symlink, stable `APP_KEY`, migrations,
    one-time demo seed, and config/route/view caching
  - Deployed and verified live at `https://dukkani-demo.onrender.com`
  - `APP_URL` follows `RENDER_EXTERNAL_URL`; trusted proxy headers and HTTPS URLs
  - Self-hosted Amiri + Tajawal fonts and local bakery photos; no external assets
  - Screenshot suite (storefront, dashboard, mobile) captured by a Playwright
    script at `dukkani-app/scripts/capture-screenshots.cjs`
  - Portfolio case study in English and Arabic, a paste-ready portfolio card, a
    deployment guide, and credits
  - 75 feature tests still passing on the final configuration

## Demo login

- Email: `demo@dukkani.jo`
- Password: `demo1234`
- Or use the one-click demo button on `/login`.

## Local commands

```bash
export PHP_INI_SCAN_DIR=/home/mohannad/Projects/dukkani/.php-ext/conf.d
cd dukkani-app
php artisan serve
npm run dev
```

Demo data can be refreshed with `php artisan migrate:fresh --seed`.

## Local environment

- Composer phar: `~/.config/composer/latest.phar`
- PHP SQLite extension extracted locally to `.php-ext/lib/`
- Use `PHP_INI_SCAN_DIR=/home/mohannad/Projects/dukkani/.php-ext/conf.d`
- Composer/npm/pacman must use the proxy
- No MySQL server; SQLite is the local database

## Demo content

- 4 categories, 10 products, 14 customers, 32 orders, deposits, notifications,
  2 blocked dates
- Arabic copy, JOD prices, Amman neighborhoods
- Clear "demo store / sample data" label

## Out of scope

- Registration, onboarding wizard, email verification
- Plans, trials, subscriptions, admin
- Real email, WhatsApp Cloud API, payment gateway
- Staff accounts, custom domains, marketplace, mobile app
