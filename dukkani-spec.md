# Dukkani (دكاني) — Product & Build Spec

**Working title:** Dukkani ("my shop")  
**Status:** Concept + validation plan  
**Based on:** `app-opportunities.md` — top recommendation  
**Date:** 2026-10-26

> One-line pitch: **Turn a home business's WhatsApp and Instagram messages into organised orders, deposits, and repeat customers — without a complicated e-commerce store.**

---

## 1. One-page summary

| Item | Decision |
|---|---|
| **Who** | Home bakers and dessert sellers in Amman who take custom orders via Instagram/WhatsApp |
| **Problem** | Orders get buried in DMs, deposits are forgotten, delivery dates clash, and customer history is lost |
| **Solution** | An Arabic/RTL storefront link + order board + WhatsApp message templates + customer book |
| **Wedge** | Start with home bakers; expand to caterers, crafts, cosmetics, and other home businesses |
| **Business model** | 25 JOD/month starter, 45 JOD/month pro, free plan up to 15 orders/month; first 20 sellers get starter at 15 JOD/month |
| **MVP build time** | 4–6 weeks after validation |
| **First milestone** | 3 paying pilot sellers — not a finished app |
| **Stack** | Laravel 12 + MySQL + Blade/Tailwind/Alpine.js + WhatsApp deep links first, Cloud API later |
| **Moat** | Workflow lock-in, Arabic/WhatsApp-native templates, community trust, local delivery/supplier integrations |

---

## 2. The problem

### 2.1 Persona: "Amal the home baker"

- **Age:** 28–40
- **Location:** Amman (later Irbid, Zarqa, Aqaba)
- **Business:** Home-based cakes, desserts, and occasion orders
- **Volume:** 20–100 orders/month, with spikes around weekends, Ramadan, Eid, weddings, and graduations
- **Average order:** 15–80 JOD
- **Tools:** Instagram for marketing, WhatsApp for orders, a notebook or Excel for tracking, phone calendar for reminders
- **Tech comfort:** High on Instagram/WhatsApp, low on dashboards and "SaaS"
- **Goal:** More orders without dropping the ball
- **Fear:** Losing a customer because she forgot an order, a deposit, or a delivery time

### 2.2 Current workflow

1. Customer sees an Instagram post or story.
2. Customer DMs on Instagram or WhatsApp: "How much for a 2-tier chocolate cake for Saturday?"
3. Amal replies with price, options, and delivery terms.
4. Customer sends a voice note, screenshots, and inspiration photos.
5. Amal writes the order in a notebook or Excel.
6. Customer pays a deposit via CliQ or cash; Amal tries to remember who paid.
7. Amal scrolls through chats to find the address and delivery time.
8. On delivery day, she hopes she did not double-book.
9. After delivery, she has no easy way to follow up or re-market to the customer.

### 2.3 Pain points, ranked

| Pain | Frequency | Cost | Current workaround |
|---|---|---|---|
| Orders buried in DMs | Daily | Lost orders, slow replies | Scroll/search chats |
| Deposit not collected or tracked | Weekly | Cashflow loss, no-shows | Notebook, memory |
| Delivery date/time clashes | Weekly | Reputation damage, refunds | Phone calendar, memory |
| No customer history | Every order | Can't upsell, repeats questions | Chat search |
| Unprofessional ordering experience | Every order | Lost trust, lower prices | "DM me" |
| No idea which products sell | Monthly | Wasted effort, poor pricing | Guessing |

### 2.4 Why now

- WhatsApp is the default business channel in Jordan; customers already expect to order there.
- Instagram/TikTok home businesses are growing, especially among women.
- Global e-commerce tools (Shopify, Salla, Zid) are too heavy and too expensive for a solo home baker.
- WhatsApp Business catalogs exist, but they are not order-management tools.
- Arabic/RTL-first tools are still rare, and most global SaaS feels foreign to this audience.

### 2.5 Evidence to validate (not assume)

- How many orders/week does each baker handle?
- How many orders are lost or delayed per month?
- How much time per day is spent on order admin?
- What do they currently pay for tools, ads, or delivery?
- Have they tried Salla/Zid/Shopify/Google Forms? Why did they stop?
- Would they pay 15–25 JOD/month after a 2-week pilot? (25 JOD is the standard starter price; 15 JOD is the founding price.)

---

## 3. Value proposition

**For the seller:**

> "Your storefront, order book, and WhatsApp follow-ups in one simple link. Stop losing orders in your DMs."

**Concrete outcomes:**

- Capture every order in one place.
- Never forget a deposit or delivery date.
- Look professional with a shareable storefront link.
- See repeat customers and best-selling products.
- Spend less time on admin and more time baking.

**For the buyer:**

- See products, prices, and availability without asking.
- Order in a clear form instead of a messy chat.
- Get WhatsApp confirmations and reminders.
- Know the total, deposit, and delivery terms upfront.

---

## 4. Scope

### 4.1 MVP (v1) — must ship

1. **Seller auth** — register/login with email or phone; one store per seller.
2. **Store settings** — name, slug, logo, bio, phone, currency, delivery fee, deposit percentage, working hours, blocked dates.
3. **Product catalog** — name, description, price, photo, options (size/flavour), availability, lead time.
4. **Public storefront** — `dukkani.jo/{slug}` with products, prices, photos, and an order form (Arabic/RTL, mobile-first).
5. **Order capture** — customer name, phone, product(s), quantity, options, pickup/delivery, date/time, address, notes, inspiration photo, deposit amount.
6. **Order board** — statuses: new → confirmed → preparing → ready → delivered / cancelled; filters by date/status; search.
7. **Customer book** — phone, address, order history, total spent, notes, tags.
8. **WhatsApp message templates** — order received, confirmed, deposit reminder, ready, delivery reminder, thank-you. **Phase 1 uses `wa.me` links with prefilled text; no API approval needed.**
9. **Dashboard** — orders this week/month, revenue, outstanding deposits, top products, repeat customers.
10. **Notifications** — in-app bell + optional email to the seller for new orders.
11. **Onboarding** — a 5-step wizard: store name → logo → first product → delivery/pickup → share link.
12. **Billing** — 14-day trial, then free plan or paid plan; manual activation for founding sellers.

### 4.2 Later (v2+)

- WhatsApp Cloud API automation (two-way, automated reminders).
- Online payments / deposit collection (CliQ, card, wallet).
- Delivery integration (Aramex, local delivery companies) and labels.
- Custom domain.
- Staff accounts and roles.
- Inventory and ingredient costing.
- Loyalty/referral tools.
- Marketplace discovery (buyers browse local home businesses).
- Native mobile app.
- AI: menu descriptions, price suggestions, demand forecasting.

### 4.3 Not building in v1

- A full e-commerce platform with checkout, shipping, and tax.
- A two-sided marketplace.
- Accounting/bookkeeping.
- A native app.
- Multi-branch/multi-currency.
- Complex AI features.

---

## 5. User stories

### Seller

- As a seller, I can create a store and share a link in my Instagram bio in under 10 minutes.
- As a seller, I can add products with photos, prices, and options.
- As a seller, I can see new orders in one board and move them through statuses.
- As a seller, I can record a deposit and see who still owes money.
- As a seller, I can open a customer's history before replying.
- As a seller, I can send a pre-written WhatsApp confirmation with one tap.
- As a seller, I can block dates when I am fully booked.
- As a seller, I can see my revenue and best-selling products.

### Buyer

- As a buyer, I can open a link and see products, prices, and delivery terms.
- As a buyer, I can submit an order with my details and a reference photo.
- As a buyer, I can receive a WhatsApp confirmation and reminder.
- As a buyer, I can track my order status from a link.

### Admin (you)

- As an admin, I can see stores, orders, and subscription status.
- As an admin, I can activate/deactivate stores and apply credits.
- As an admin, I can impersonate a store for support (with audit log).

---

## 6. UX flows

### 6.1 Seller onboarding

1. Landing page → "Start free" (no credit card).
2. Sign up with name, phone, email, password.
3. Store name + slug (auto-suggested) + city.
4. Upload logo (optional) + cover image.
5. Add first product (name, price, photo).
6. Choose pickup/delivery + delivery fee + deposit percentage.
7. Share store link (copy, WhatsApp, Instagram bio).
8. Guided checklist: add 3 products, share link, receive first order.

**Activation event:** store created + at least 1 product + first order received.

### 6.2 Buyer order flow

1. Buyer opens `dukkani.jo/amal-bakes` from Instagram.
2. Browses products; taps "Order."
3. Order form: name, phone, product, quantity, options, pickup/delivery, date/time, address, notes, reference photo.
4. Sees total, deposit due, and delivery terms.
5. Submits; gets a confirmation screen + WhatsApp message from the seller (phase 1: seller taps to send; phase 2: automatic).
6. Buyer can open a signed tracking link to see status.

### 6.3 Seller order management

1. New order appears on the board + in-app notification.
2. Seller reviews details, confirms availability, and moves to "Confirmed."
3. Seller taps "Send confirmation" → WhatsApp opens with a prefilled message.
4. Seller records deposit (paid/pending) and balance.
5. On the day, moves to "Preparing" → "Ready" → "Delivered."
6. Seller sends a thank-you/review request.
7. Customer history updates automatically.

### 6.4 Public storefront

- Mobile-first, Arabic/RTL by default, English toggle.
- Hero: logo, store name, short bio, delivery/pickup info, WhatsApp button.
- Product grid: photo, name, price, "Order" button.
- Product detail: description, options, lead time, photos.
- Footer: contact, working hours, policies (deposit, cancellation).
- SEO: title, description, Open Graph image, structured data (LocalBusiness/Product later).

---

## 7. Screens

### Seller dashboard

1. **Home** — today's orders, new orders, outstanding deposits, quick actions.
2. **Orders** — board/list, filters, search, status actions.
3. **Order detail** — customer, items, options, deposit, timeline, messages, notes.
4. **Products** — list/grid, add/edit, availability toggle.
5. **Customers** — list, search, history, tags.
6. **Storefront** — preview, share link, settings.
7. **Analytics** — revenue, orders, top products, repeat rate.
8. **Settings** — profile, store, notifications, billing, team.
9. **Onboarding checklist** — progress and next step.

### Public

1. **Storefront** — products and order CTA.
2. **Product detail** — options and order form.
3. **Order form** — details, date/time, address, notes, photo.
4. **Order confirmation** — summary, deposit instructions, tracking link.
5. **Order tracking** — status timeline.

### Admin

1. **Stores** — list, status, plan, last active.
2. **Orders** — global search.
3. **Subscriptions** — trial/paid/overdue.
4. **Support** — impersonate, notes.

---

## 8. Data model

### 8.1 Tables

#### users

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique nullable | |
| phone | string unique | normalized E.164 |
| password | string | hashed |
| locale | string | `ar` / `en` |
| timezone | string | default `Asia/Amman` |
| status | enum | `active`, `suspended` |
| email_verified_at | timestamp nullable | |
| created_at / updated_at | timestamps | |

#### stores

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| owner_id | FK users | |
| name | string | |
| slug | string unique | public URL |
| description | text nullable | |
| logo_url | string nullable | Cloudinary |
| cover_url | string nullable | |
| phone | string | public contact |
| city | string | |
| currency | string | default `JOD` |
| delivery_fee | decimal(10,3) | default 0 |
| deposit_percent | tinyint | 0–100 |
| pickup_enabled | boolean | |
| delivery_enabled | boolean | |
| status | enum | `active`, `paused`, `suspended` |
| trial_ends_at | timestamp nullable | |
| created_at / updated_at | timestamps | |

#### store_members

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| user_id | FK users | |
| role | enum | `owner`, `staff` |
| created_at | timestamp | |

Unique: `(store_id, user_id)`.

#### products

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| name | string | |
| description | text nullable | |
| price | decimal(10,3) | |
| image_url | string nullable | |
| lead_time_hours | int | default 24 |
| is_active | boolean | default true |
| sort_order | int | default 0 |
| created_at / updated_at | timestamps | |

#### product_options

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| product_id | FK products | |
| name | string | e.g. Size, Flavour |
| type | enum | `single`, `multiple` |
| is_required | boolean | |
| sort_order | int | |

#### product_option_values

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| option_id | FK product_options | |
| value | string | e.g. Medium, Chocolate |
| price_delta | decimal(10,3) | default 0 |
| sort_order | int | |

#### customers

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| name | string | |
| phone | string | normalized |
| email | string nullable | |
| address | text nullable | |
| notes | text nullable | |
| tags | json nullable | e.g. `["vip","ramadan"]` |
| created_at / updated_at | timestamps | |

Unique: `(store_id, phone)`.

#### orders

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| customer_id | FK customers | |
| public_token | string unique | signed tracking |
| status | enum | `new`, `confirmed`, `preparing`, `ready`, `delivered`, `cancelled` |
| fulfillment_type | enum | `pickup`, `delivery` |
| scheduled_at | datetime | |
| delivery_address | text nullable | |
| subtotal | decimal(10,3) | |
| delivery_fee | decimal(10,3) | default 0 |
| discount | decimal(10,3) | default 0 |
| total | decimal(10,3) | |
| deposit_amount | decimal(10,3) | default 0 |
| deposit_status | enum | `none`, `pending`, `paid`, `refunded` |
| payment_method | enum nullable | `cash`, `cliq`, `bank_transfer`, `other` |
| balance_due | decimal(10,3) | computed/cached |
| customer_notes | text nullable | |
| internal_notes | text nullable | |
| source | enum | `storefront`, `whatsapp`, `manual` |
| created_at / updated_at | timestamps | |

Indexes: `(store_id, status, scheduled_at)`, `(store_id, created_at)`.

#### order_items

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| order_id | FK orders | |
| product_id | FK products nullable | keep null if product deleted |
| product_name | string | snapshot |
| quantity | int | |
| unit_price | decimal(10,3) | |
| options | json nullable | `[{"name":"Size","value":"Medium","price_delta":0}]` |
| notes | text nullable | |
| line_total | decimal(10,3) | |

#### order_attachments

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| order_id | FK orders | |
| url | string | Cloudinary |
| type | string | `image`, `document` |
| created_at | timestamp | |

#### order_events

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| order_id | FK orders | |
| user_id | FK users nullable | |
| type | string | `created`, `status_changed`, `deposit_updated`, `message_sent`, `note_added` |
| payload | json nullable | |
| created_at | timestamp | |

#### message_logs

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| order_id | FK orders nullable | |
| channel | enum | `whatsapp`, `sms`, `email` |
| template | string | |
| to | string | |
| status | enum | `queued`, `sent`, `delivered`, `failed` |
| provider_id | string nullable | |
| error | text nullable | |
| sent_at | timestamp nullable | |
| created_at | timestamp | |

#### notifications

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| user_id | FK users | |
| type | string | `new_order`, `order_status`, `deposit`, `system` |
| title | string | |
| body | text | |
| data | json nullable | deep link |
| read_at | timestamp nullable | |
| created_at | timestamp | |

#### plans

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| code | string unique | `free`, `starter`, `pro` |
| name | string | |
| price_jod | decimal(10,3) | monthly |
| order_limit | int nullable | null = unlimited |
| features | json | |
| is_active | boolean | |

#### subscriptions

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| plan_id | FK plans | |
| status | enum | `trialing`, `active`, `past_due`, `cancelled` |
| trial_ends_at | timestamp nullable | |
| current_period_start | timestamp | |
| current_period_end | timestamp | |
| cancelled_at | timestamp nullable | |
| created_at / updated_at | timestamps | |

#### blocked_dates

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| date | date | |
| reason | string nullable | |

Unique: `(store_id, date)`.

#### store_hours

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK stores | |
| day_of_week | tinyint | 0–6 |
| open_time | time nullable | |
| close_time | time nullable | |
| is_closed | boolean | |

### 8.2 Relationships

```
users 1─1 stores (owner)
users 1─* store_members *─1 stores
stores 1─* products 1─* product_options 1─* product_option_values
stores 1─* customers 1─* orders 1─* order_items
orders 1─* order_attachments
orders 1─* order_events
orders 1─* message_logs
stores 1─* notifications
stores 1─1 subscriptions *─1 plans
stores 1─* blocked_dates
stores 1─* store_hours
```

### 8.3 Multi-tenancy rules

- Every tenant table has `store_id`.
- Use a `BelongsToStore` trait with a global scope based on the authenticated user's store.
- Never trust a `store_id` from the request; derive it from the authenticated user's membership.
- Policies for every model (`view`, `update`, `delete`).
- Public storefront queries only expose active products and public fields.
- Write tests that attempt cross-tenant access for every endpoint.
- Admin routes use a separate guard/policy.

---

## 9. API design

### 9.1 Conventions

- REST JSON, `/api` prefix.
- Auth: Sanctum bearer tokens.
- Responses: `{ "data": ... }`; collections include `meta`.
- Errors: `{ "message": "...", "errors": { "field": ["..."] } }`.
- Validation: 422; auth: 401; forbidden: 403; rate limit: 429.
- Pagination: `page`, `per_page` (max 100).
- Dates: ISO-8601.
- Currency: decimal strings or integer fils; be consistent.

### 9.2 Auth

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/auth/register` | Seller signup |
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/me` | Current user + store |
| POST | `/api/auth/forgot-password` | Reset link |
| POST | `/api/auth/reset-password` | Reset |
| POST | `/api/auth/otp/send` | Phone OTP (optional) |
| POST | `/api/auth/otp/verify` | Verify phone |

### 9.3 Store

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/store` | Current store |
| PUT | `/api/store` | Update store |
| POST | `/api/store/logo` | Upload logo |
| POST | `/api/store/cover` | Upload cover |
| GET | `/api/store/onboarding` | Checklist status |
| GET | `/api/store/hours` | Working hours |
| PUT | `/api/store/hours` | Update hours |
| GET/POST | `/api/store/blocked-dates` | Blocked dates |
| DELETE | `/api/store/blocked-dates/{id}` | Unblock |

### 9.4 Public storefront

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/public/stores/{slug}` | Store + active products |
| GET | `/api/public/stores/{slug}/products/{id}` | Product detail |
| POST | `/api/public/stores/{slug}/orders` | Submit order (rate-limited) |
| GET | `/api/public/orders/{token}` | Order status (signed token) |

### 9.5 Products

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/products` | List |
| POST | `/api/products` | Create |
| GET | `/api/products/{id}` | Show |
| PUT | `/api/products/{id}` | Update |
| DELETE | `/api/products/{id}` | Soft delete |
| POST | `/api/products/{id}/image` | Upload image |
| PATCH | `/api/products/{id}/availability` | Toggle active |

### 9.6 Orders

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/orders` | List/filter (`status`, `date`, `q`) |
| POST | `/api/orders` | Manual order (seller) |
| GET | `/api/orders/{id}` | Detail + timeline |
| PATCH | `/api/orders/{id}/status` | Change status |
| PATCH | `/api/orders/{id}/deposit` | Update deposit |
| PATCH | `/api/orders/{id}/notes` | Internal notes |
| POST | `/api/orders/{id}/messages` | Send WhatsApp template |
| GET | `/api/orders/{id}/messages` | Message history |
| POST | `/api/orders/{id}/attachments` | Upload reference |

### 9.7 Customers

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/customers` | List/search |
| GET | `/api/customers/{id}` | Detail + orders |
| PUT | `/api/customers/{id}` | Update notes/tags |
| DELETE | `/api/customers/{id}` | Delete (privacy) |

### 9.8 Dashboard

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/dashboard/summary` | Today/week/month revenue, orders |
| GET | `/api/dashboard/outstanding-deposits` | Who owes what |
| GET | `/api/dashboard/top-products` | Best sellers |
| GET | `/api/dashboard/repeat-customers` | Retention |

### 9.9 Billing

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/plans` | Public plans |
| GET | `/api/subscription` | Current subscription |
| POST | `/api/subscription/checkout` | Start paid plan |
| POST | `/api/subscription/cancel` | Cancel |
| POST | `/api/webhooks/payments` | Provider webhook |

### 9.10 Webhooks

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/webhooks/whatsapp` | Meta verification |
| POST | `/api/webhooks/whatsapp` | Inbound messages/status |
| POST | `/api/webhooks/payments` | Payment provider |

---

## 10. Technical architecture

### 10.1 Stack

| Layer | Choice | Why |
|---|---|---|
| Backend | Laravel 12 (PHP 8.2+) | Your strength; fast CRUD, auth, queues, validation |
| Database | MySQL 8 | Familiar; JSON columns for options/tags |
| Cache/queue | Redis (or database queue initially) | Reminders, WhatsApp sends, exports |
| Frontend (dashboard) | Blade + Tailwind + Alpine.js | Fastest solo path; SSR; Arabic/RTL |
| Frontend (public store) | Blade + Tailwind | SEO, speed, no SPA needed |
| API | REST + Sanctum | Future mobile app/integrations |
| Images | Cloudinary | You already use it |
| Hosting | Hetzner/DigitalOcean + Forge/Ploy | Cheap, reliable |
| Monitoring | Sentry + UptimeRobot | Errors and uptime |
| Backups | Daily DB + weekly restore test | Trust |

**Trade-off:** React/Vue would give a richer dashboard, but Blade + Alpine ships faster and is easier to maintain solo. Keep the API clean so you can add a React dashboard later.

### 10.2 WhatsApp strategy — manual first, API later

**Phase 1 — validation (no approval, no cost):**

- Generate `wa.me` links with prefilled, URL-encoded messages.
- Seller taps "Send confirmation" → WhatsApp opens with the message → seller taps send.
- Works on mobile and desktop; no Meta Business verification.
- Store the message in `message_logs` for history.

**Phase 2 — automation (after 20+ paying stores):**

- Meta WhatsApp Cloud API:
  - Meta Business account + verified phone number.
  - Permanent system user token.
  - Approved message templates (Arabic + English).
  - Webhook for delivery status and inbound replies.
- Use a queue with retries and idempotency.
- Handle opt-in/opt-out and the 24-hour customer service window.
- Costs: per-template pricing varies by country/category; check Meta's current rates for Jordan. Do not hardcode prices.
- Fallback: SMS via a local aggregator or email.

**Why this order matters:** WhatsApp API setup can take days/weeks and adds cost. The core value — organised orders and prefilled messages — can be validated without it.

### 10.3 Suggested project structure

```
app/
  Domain/
    Store/
    Catalog/
    Ordering/
    Messaging/
  Http/
    Controllers/Api/
    Controllers/Web/
    Requests/
    Resources/
  Models/
  Policies/
  Jobs/
  Listeners/
  Services/
  Support/
database/
  migrations/
  seeders/
  factories/
resources/
  views/
    seller/
    storefront/
    admin/
  js/
  css/
routes/
  api.php
  web.php
  admin.php
tests/
  Feature/
  Unit/
```

### 10.4 Security & privacy

- Tenant isolation tests for every endpoint.
- Signed URLs for order tracking and attachments.
- Rate-limit public order submissions; honeypot + optional CAPTCHA.
- Validate uploads (MIME, size, dimensions); strip EXIF.
- Encrypt sensitive fields at rest if feasible (phone/address).
- Consent checkbox for WhatsApp updates.
- Data retention and deletion policy.
- Audit log for status/deposit changes.
- Admin impersonation logged and time-limited.
- Check Jordan's personal data protection rules before launch.

---

## 11. Pricing

| Plan | Price | Limits | Features |
|---|---|---|---|
| **Free** | 0 JOD | 15 orders/month | Storefront, 1 seller, basic order board |
| **Starter** | 25 JOD/month | Unlimited orders | Customer book, analytics, deposit tracking, WhatsApp templates (`wa.me`) |
| **Pro** | 45 JOD/month | Unlimited + 3 staff | Custom domain, staff accounts, delivery labels, priority support |
| **Founding seller** | 15 JOD/month | Unlimited | First 20 sellers only, in exchange for feedback + a case study |

- 14-day trial on Starter; no credit card.
- Annual: 2 months free.
- Payment: CliQ / bank transfer initially; card via a local provider later.
- Do not discount forever; the founding price is limited to the first 20 sellers.

**Unit economics check:** at 25 JOD/month, you need ~100 paying stores for 2,500 JOD/month (~$3,500). That is realistic only if support is low. Keep the product simple and the onboarding self-serve.

---

## 12. Go-to-market

### 12.1 Niche launch

- **City:** Amman first.
- **Segment:** Home bakers and dessert sellers.
- **Goal:** 20 founding sellers in 60 days; 5 paying in the first 30.

### 12.2 Channels

1. **Facebook groups** for home businesses, bakers, and women entrepreneurs. Share useful content, not spam.
2. **Instagram/TikTok** — short videos: "How I stopped losing cake orders in my DMs," "Price your cake correctly," "Free order form for home bakers."
3. **WhatsApp communities** — ask for permission, offer a free storefront setup.
4. **Workshops** — partner with baking schools, women's entrepreneurship programmes, and microfinance institutions.
5. **Referrals** — one free month for each paying referral.
6. **Partnerships** — baking-supply shops, delivery companies, event planners, photographers.

### 12.3 Offer

> "We'll set up your storefront and move your first 10 orders for free. If you like it, it's 15 JOD/month for the first 20 sellers (normally 25 JOD/month)."

### 12.4 Sales script (DM/phone)

1. "How do you currently take orders — Instagram, WhatsApp, both?"
2. "How many orders did you get last week?"
3. "How many messages got buried or answered late?"
4. "What happens when someone forgets the deposit?"
5. "If I set up a simple link that captures every order and reminds you about deposits, would you try it for 2 weeks?"
6. "Can I set it up for you today?"

### 12.5 Content ideas

- "5 signs you're losing orders in your DMs."
- "The deposit mistake that costs home bakers money."
- "How to price a custom cake in Jordan."
- "A 10-minute storefront setup for your home business."
- "Before/after: from WhatsApp chaos to an organised order board."

---

## 13. Validation plan

### Week 0 — before code

**Goal:** 15 interviews, 5 concierge pilots, 3 paid commitments.

1. **Recruit** 15 home bakers from Facebook/Instagram/WhatsApp communities.
2. **Interview** using the script in section 12.4; record pain, frequency, spend, workarounds.
3. **Concierge MVP:** 5 sellers send you their orders; you manage them in a shared Google Sheet and send WhatsApp confirmations using templates.
4. **Measure:** orders/week, time saved, missed orders, deposit collection.
5. **Ask for payment:** 15 JOD/month (founding price) to continue after 2 weeks.
6. **Decide:** build only if 3+ pay or commit.

### Landing page

- Headline: **"حوّلي رسائل الواتساب إلى طلبات منظّمة"** / "Turn WhatsApp messages into organised orders."
- Subhead: "متجر إلكتروني بسيط ولوحة طلبات لمشاريع المنزل. لا مزيد من الطلبات الضائعة أو العربون المنسي."
- CTA: "جرّبي مجانًا" / "Start free."
- Sections: problem, how it works (3 steps), features, pricing, FAQ, WhatsApp CTA.
- Measure: signup rate, WhatsApp clicks, replies.

### Concierge SOP

1. Seller shares a Google Form link in their Instagram bio.
2. Customer submits order; you receive an email/Sheet row.
3. You reply on WhatsApp with a confirmation template.
4. You add the order to a shared Sheet with status and deposit.
5. Each evening, you send the seller a summary: new orders, deposits due, tomorrow's deliveries.
6. After 2 weeks, review time saved and errors; ask for payment.

### Green lights

- 3+ sellers pay or commit.
- 10+ orders/week across pilots.
- Sellers say the tool saves at least 30 minutes/day or prevents at least 1 lost order/week.
- You can acquire the next 20 sellers through the same channels.

### Kill criteria

- Sellers won't pay even after the concierge MVP.
- Most orders are one-off, not repeat.
- Sellers demand complex custom features for free.
- Acquisition cost/time per seller is too high.
- WhatsApp/Instagram already solves it "well enough."

---

## 14. Build plan (6 weeks after validation)

### Week 1 — foundation

- Laravel project, auth (Sanctum), users/stores/members.
- Multi-tenant trait + policies + tests.
- Store settings + onboarding wizard shell.
- Product CRUD + image upload.

### Week 2 — storefront + orders

- Public storefront (Blade, RTL, mobile-first).
- Product detail + order form.
- Order creation, validation, rate limiting.
- Order board + statuses.
- Customer creation/lookup by phone.

### Week 3 — messaging + customers

- `wa.me` templates + message logs.
- Customer book + history.
- Order detail timeline.
- In-app notifications.
- Deposit tracking + outstanding deposits.

### Week 4 — dashboard + billing + polish

- Dashboard summary, top products, repeat customers.
- Plans/trial/subscription (manual activation).
- Arabic/English localization, RTL QA.
- Empty states, onboarding checklist, error handling.
- Feature tests for tenant isolation and order flow.

### Week 5 — pilot

- Onboard 5 sellers; migrate their current orders.
- Daily check-ins; fix blockers.
- Instrument analytics events.
- Measure activation, orders/store/week, support tickets.

### Week 6 — launch

- Public launch to 20 sellers.
- Landing page + content + referral programme.
- Case study from pilot.
- Weekly iteration cadence.

### Post-launch

- WhatsApp Cloud API automation.
- Payments/deposits.
- Delivery integration.
- Custom domains.
- Expand to caterers/crafts.

---

## 15. Metrics

### North star

**Orders processed per active store per week.**

### Activation

- Store created → first product → storefront shared → first order within 48 hours.
- Target: 60% of signups activate.

### Retention

- % of stores with at least one order in week 4.
- Target: 40%+ for a tool with real workflow value.

### Revenue

- MRR, ARPU, trial-to-paid conversion, churn.
- Target: 20% trial-to-paid; <5% monthly churn after 3 months.

### Support

- Tickets per store per week; time to resolve.
- Target: <0.5 tickets/store/week by month 3.

### Quality

- Order error rate (wrong date/address/deposit).
- WhatsApp delivery rate.
- Page load time <2s on 3G.

### Instrumentation events

`store_created`, `product_created`, `storefront_viewed`, `order_created`, `order_confirmed`, `deposit_paid`, `order_delivered`, `whatsapp_sent`, `subscription_started`, `subscription_cancelled`.

---

## 16. Risks & mitigations

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Sellers won't pay | High | High | Concierge MVP + paid pilot before building; show recovered revenue |
| Low ARPU | High | Medium | Keep support low; annual plans; expand to higher-ticket niches |
| WhatsApp API costs/policy | Medium | High | Start with `wa.me`; abstract messaging; SMS/email fallback |
| Support burden | High | Medium | Narrow niche; templates; videos; WhatsApp onboarding; office hours |
| Competition (Salla/Zid/Shopify) | Medium | Medium | Be the simplest Arabic/WhatsApp-first tool for home businesses |
| Seasonality (Ramadan/Eid) | High | Medium | Annual plans; diversify into weddings/corporate orders |
| Trust / data privacy | Medium | High | Clear policies; consent; encryption; deletion; local hosting option |
| Payment collection | Medium | Medium | CliQ/bank transfer first; local provider later |
| Founder focus | Medium | High | One niche, one ICP, 3 paid customers before expanding |

---

## 17. Open questions

1. **Name:** Dukkani, Matjari, Sahla, Siniya, or something else? Check domain + Instagram handle + trademark.
2. **City:** Amman only, or Amman + Irbid/Zarqa?
3. **Niche:** Bakers only, or all home food businesses?
4. **Payments:** CliQ, bank transfer, cash, or card? Which provider is available to a Jordanian sole proprietor?
5. **Legal:** Do you need a company/registration to invoice and collect payments?
6. **Data protection:** What are Jordan's requirements for storing customer phone numbers/addresses?
7. **WhatsApp:** Direct Cloud API vs a provider (360dialog, Twilio)? What are the current Jordan message rates?
8. **Hosting:** Local hosting requirement for some clients, or cloud?
9. **Pricing:** Is 25 JOD/month realistic for this audience? Test 15/25/35.
10. **Your time:** How many hours/week can you commit for 6 weeks?

---

## 18. Next steps

**Already done:**

- **Validation kit** — `dukkani-validation-kit.md` (interview script, scorecard, concierge SOP, outreach messages, decision memo).
- **Landing page** — `dukkani-landing/index.html` (Arabic/RTL, responsive, waitlist form).
- **Live page + waitlist** — https://dukkani-app.netlify.app (Formspree connected and tested).

**Still to do, in order:**

1. **Validate** — talk to 15 bakers, run 5 concierge pilots for 2 weeks, ask for 15 JOD/month (founding price). Do not build until 3 sellers pay or commit.
2. **Scaffold the app** — Laravel project, auth, stores, products, public storefront, order form, order board.
3. **Or switch** — if the clinic SaaS or emigration navigator fits you better, I can produce the same spec for that.

My recommendation remains: **validate while the landing page collects leads**, then build. The only validation that matters is payment.
