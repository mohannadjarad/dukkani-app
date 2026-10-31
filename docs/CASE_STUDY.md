# Dukkani — an Arabic-first storefront and order desk for home businesses

**A working Laravel demo for أمل بيكنز (Amal Bakes), a home bakery in Amman.**

[العربية](CASE_STUDY.ar.md) · [Live demo](https://dukkani-demo.onrender.com) · Demo login: `demo@dukkani.jo` / `demo1234`

## Snapshot

| | |
|---|---|
| **Role** | Product design, UX writing, front-end and back-end development |
| **Type** | Portfolio demo of a small-business SaaS vertical slice |
| **Year** | 2026 |
| **Stack** | Laravel 12, PHP 8.4, Blade, Tailwind CSS 4, Alpine.js, Chart.js, Vite 7, SQLite |
| **Language** | Arabic only, RTL throughout |
| **Scale** | 1 store, 4 categories, 10 products, 14 customers, 32 orders, 75 tests |
| **Hosting** | Render (Docker, free tier) — [live demo](https://dukkani-demo.onrender.com) |

## The problem

Home businesses in Jordan run their orders in WhatsApp. A baker receives photos,
voice notes, delivery addresses and deposit screenshots in one long chat, then
tries to remember who confirmed, who paid a deposit, what time the order is due,
and which cake had the nut allergy note.

The result is missed messages, double-booked days, and a seller who cannot
answer simple questions like *"how much did I sell this month?"* or *"who are my
best customers?"*

## The goal

Build the smallest real product that removes the chaos — **a storefront and an
order desk** — without the heavy tooling (accounting, inventory, marketing,
subscriptions) a home baker will never use.

**In scope**

- A public, mobile-first storefront: menu, product options, cart, checkout,
  delivery or pickup, and a tracking link customers can keep.
- A seller dashboard: KPIs, an order board with a real workflow, products,
  customers, store settings, and analytics.
- A deposit model that matches how home businesses actually get paid: cash on
  delivery or a CliQ / bank transfer deposit.

**Out of scope**

- Self-serve registration, plans and billing, an admin panel.
- Real WhatsApp Cloud API messages, email, or an online payment gateway.
- Staff accounts, custom domains, a marketplace, and a mobile app.

Everything ships with one seeded demo store and a one-click demo login so the
work can be judged in seconds.

## Who it is for

- **أمل, the seller.** Wants to answer one question at a time: *what do I need to
  bake next, and has it been paid for?*
- **The customer.** Wants to see the menu, choose options, and know the order
  was received — from a phone, in Arabic.
- **The business.** Wants sales visibility without exporting a spreadsheet.

## The experience

### 1. A storefront that feels like a neighbourhood shop

The home page leads with the store's story and a single "order card" that answers
the practical questions immediately: lead time, delivery fee, deposit
percentage, opening hours, and whether the shop is open right now.

![Amal Bakes storefront home](screenshots/01-storefront-home.jpg)

The full menu supports server-side category and search filtering, and each
product page carries options with visible price deltas so the total is never a
surprise.

![Product page with options](screenshots/04-storefront-product.jpg)

The cart leads to a checkout that validates the schedule on the server: opening
hours, blocked dates, and the store's lead time are all enforced, not just drawn
in the browser. The customer gets a public tracking link when the order is
placed.

| Cart | Checkout | Tracking |
|---|---|---|
| ![Cart](screenshots/05-storefront-cart.jpg) | ![Checkout](screenshots/06-storefront-checkout.jpg) | ![Order tracking](screenshots/07-order-tracking.jpg) |

### 2. An order desk built around the workflow

The dashboard opens with the day's picture: revenue, orders, deposits waiting,
today's schedule, and the products selling best.

![Dashboard home](screenshots/09-dashboard-home.jpg)

Orders can be worked as a **list** with tabs, filters and sorting, or as a
**kanban board** that mirrors how a kitchen actually moves work.

![Orders board](screenshots/10-orders-board.jpg)

An order detail page holds everything in one place: the workflow stepper, quick
status actions, items and totals, the customer card, the deposit card, WhatsApp
templates, internal notes, and a full activity timeline.

![Order detail](screenshots/12-order-detail.jpg)

Status moves are validated against `OrderStatus::nextStatuses()`, so an illegal
jump is rejected server-side, and every action appends an immutable event.

### 3. Products and customers without a spreadsheet

The catalog has grid/list views, status tabs, search, category and sort
controls. The product form includes an image upload or URL, availability and
featured flags, and a dynamic option builder with price deltas.

| Product catalog | Product editor |
|---|---|
| ![Products](screenshots/13-products.jpg) | ![Product editor](screenshots/14-product-edit.jpg) |

Every customer has a profile with order count, lifetime spend, last order, tags,
notes, and a linked order history.

![Customer profile](screenshots/16-customer-profile.jpg)

### 4. Analytics the seller will actually read

The analytics page compares the selected 7/30/90-day window with the preceding
one, then breaks the period down by status, weekday, fulfillment method and
payment method. Top products can be ranked by revenue or quantity, and top
customers link straight into their profile.

![Analytics](screenshots/17-analytics.jpg)

### 5. Settings that match real business rules

Delivery and pickup toggles, delivery fee, deposit percentage, lead time,
deposit instructions, seven-day working hours, and blocked dates — all editable,
with at least one fulfillment method always required.

| Store profile | Delivery rules | Working hours |
|---|---|---|
| ![Settings profile](screenshots/18-settings-profile.jpg) | ![Delivery rules](screenshots/18b-settings-delivery.jpg) | ![Working hours](screenshots/18c-settings-hours.jpg) |

### 6. Mobile is the default

The storefront and dashboard are responsive and tested at phone width; the
storefront is where most customers will ever see the product.

| Storefront | Dashboard | Analytics |
|---|---|---|
| ![Storefront mobile](screenshots/19-storefront-home-mobile.jpg) | ![Dashboard mobile](screenshots/20-dashboard-home-mobile.jpg) | ![Analytics mobile](screenshots/20c-analytics-mobile.jpg) |

## Design system

A warm, food-friendly palette and a small set of reusable pieces keep 28 views
consistent:

- **Tokens:** canvas `#f8f3ea`, ink `#2a1c15`, terracotta brand `#b25c38`, gold
  `#c9a15a`, sage `#6c7f65`, plus soft variants and two elevation shadows.
- **Type:** Amiri for display headings, Tajawal for UI — both self-hosted and
  subset to Arabic + Latin.
- **Components:** brand mark, product card, status badge, deposit badge, order
  card, avatar, pagination, empty states, and a shared button/input/badge layer.
- **RTL discipline:** logical properties (`start`/`end`, `ms-`/`me-`) rather than
  left/right, and charts configured to read right-to-left.
- **Arabic copy pass:** every screen is written in the seller's own vocabulary,
  with a clear "demo store / sample data" label so nothing is mistaken for real.

## Architecture

```text
Storefront (public)                     Dashboard (auth)
  ShopController                          DashboardController
  CartController        ┌──────────┐      OrderController
  CheckoutController ──▶│ Services │◀──   ProductController
  OrderTrackingController│          │      CustomerController
                        └──────────┘      SettingsController
                       CartService        AnalyticsController
                       OrderService
                       ScheduleService      ┌───────────────┐
                       WhatsAppService      │ SQLite, 13    │
                       AnalyticsService     │ migrations,   │
                                            │ store_id on   │
                                            │ tenant tables │
                                            └───────────────┘
```

- **Multi-tenant ready.** Every tenant table carries `store_id`; dashboard
  queries scope through the authenticated user's store, and cross-store access
  returns 404.
- **Money as integers.** All amounts are stored in fils (`1 JOD = 1000 fils`) and
  formatted by `App\Support\Money`.
- **Enums for truth.** `OrderStatus`, `FulfillmentType`, `DepositStatus` and
  `PaymentMethod` are PHP enums, so status labels, transitions and badges cannot
  drift apart.
- **Services, not fat controllers.** `CartService` (session cart), `OrderService`
  (placement, transitions, deposits, notes, message logging), `ScheduleService`
  (lead time, hours, blocked dates, 30-minute slots), `WhatsAppService`
  (templates), and `AnalyticsService` (period metrics).

## Quality

- **75 feature tests, 296 assertions**, all green, covering the storefront,
  cart, checkout and scheduling rules, public tracking, auth and dashboard,
  the orders board and workflow, catalog, customers, settings, analytics, and
  cross-store isolation.
- **Server-side validation everywhere** the browser cannot be trusted: schedule
  slots, option requirements, store ownership, and status transitions.
- **Testable demo data.** The seeder produces a full, realistic store on any
  machine with `php artisan migrate:fresh --seed`.

## Deployment

The demo ships as a Docker image with a multi-stage build (Vite assets,
Composer vendor, Apache + PHP 8.4). A `render.yaml` Blueprint creates the web
service on Render's free plan; the entrypoint prepares storage, runs migrations,
seeds the demo once, generates and stores `APP_KEY`, caches config, routes and
views, and starts Apache on Render's port. The Blueprint validates against the
Render API and the reference deployment is live at
**https://dukkani-demo.onrender.com**. See [`DEPLOYMENT.md`](DEPLOYMENT.md).

## Trade-offs and what I would build next

- **SQLite in the container on Render's free plan** keeps the demo a single free
  service; it is ephemeral, so the demo reseeds on restart. Persistence is a
  one-line Blueprint change plus a paid instance. A multi-tenant production build
  would move to Postgres and object storage.
- **WhatsApp is a template launcher and logger**, not the Cloud API. The
  messages are real and copy-paste ready, but nothing is sent automatically.
- **One owner account per store.** Staff accounts, roles and invitations are the
  natural next slice.
- **Analytics is period-based, not cohort-based.** Useful today; retention and
  repeat-purchase curves would be the next analytical step.

## Links

- **Live demo:** https://dukkani-demo.onrender.com
- **Source:** repository root (`dukkani-app/`)
- **Deployment guide:** [`DEPLOYMENT.md`](DEPLOYMENT.md)
- **Project brief and milestones:** [`PROJECT_BRIEF.md`](PROJECT_BRIEF.md)
- **Photo and font credits:** [`CREDITS.md`](CREDITS.md)
