# MUSINA GAS — DEVELOPER SPECIFICATION
## Part 2: Database, API, Edge Cases & Infrastructure
**Version:** 1.0 | **Date:** July 2026 | **Status:** Pre-build

---

## 7. DATABASE SCHEMA (Supabase / PostgreSQL)

> Enable PostGIS extension in Supabase: `CREATE EXTENSION IF NOT EXISTS postgis;`

---

### Table: `profiles`
Extends Supabase `auth.users`. Created via trigger on user signup.

```sql
CREATE TABLE profiles (
  id          UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  full_name   TEXT NOT NULL,
  phone       TEXT NOT NULL,              -- +27 format
  role        TEXT NOT NULL DEFAULT 'customer'
                CHECK (role IN ('customer','driver','admin')),
  account_type TEXT NOT NULL DEFAULT 'residential'
                CHECK (account_type IN ('residential','commercial')),
  created_at  TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `price_config`
One active price row at a time. Admins insert new rows; old rows remain as history.

```sql
CREATE TABLE price_config (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  price_per_kg    NUMERIC(10,2) NOT NULL,
  currency        TEXT NOT NULL DEFAULT 'ZAR',
  effective_from  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  adjusted_by     UUID REFERENCES profiles(id),
  notes           TEXT
);
-- Get current price: SELECT * FROM price_config ORDER BY effective_from DESC LIMIT 1;
```

---

### Table: `delivery_zones`
Defines serviceable areas as geographic polygons.

```sql
CREATE TABLE delivery_zones (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  zone_name     TEXT NOT NULL,             -- e.g. "Zone A", "Musina CBD"
  zone_boundary GEOMETRY(POLYGON, 4326),  -- PostGIS polygon
  delivery_fee  NUMERIC(10,2) NOT NULL,
  is_active     BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `delivery_locations`
Customer saved addresses.

```sql
CREATE TABLE delivery_locations (
  id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_id      UUID NOT NULL REFERENCES profiles(id),
  label            TEXT,                  -- e.g. "Home", "Office"
  latitude         NUMERIC(10,7) NOT NULL,
  longitude        NUMERIC(10,7) NOT NULL,
  digital_address  TEXT,                  -- Human readable
  access_notes     TEXT,                  -- "Blue gate, ring twice"
  zone_id          UUID REFERENCES delivery_zones(id),
  is_default       BOOLEAN DEFAULT FALSE,
  created_at       TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `gas_orders`
Core order record.

```sql
CREATE TABLE gas_orders (
  id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_ref         TEXT UNIQUE NOT NULL,  -- e.g. MG-00124 (generated)
  customer_id       UUID NOT NULL REFERENCES profiles(id),
  location_id       UUID NOT NULL REFERENCES delivery_locations(id),
  cylinder_size_kg  NUMERIC(5,1) NOT NULL, -- 9, 19, 48
  quantity          INT NOT NULL DEFAULT 1,
  price_per_kg      NUMERIC(10,2) NOT NULL, -- Locked at order creation time
  delivery_fee      NUMERIC(10,2) NOT NULL, -- Locked at order creation time
  total_amount      NUMERIC(10,2) NOT NULL, -- (price_per_kg × cylinder_size_kg × qty) + delivery_fee
  payment_method    TEXT NOT NULL CHECK (payment_method IN ('EFT','COD','CARD_PENDING')),
  payment_status    TEXT NOT NULL DEFAULT 'pending'
                    CHECK (payment_status IN (
                      'pending',          -- Just placed
                      'awaiting_eft',     -- EFT chosen, waiting for proof upload
                      'eft_submitted',    -- Proof uploaded, waiting admin review
                      'eft_rejected',     -- Admin rejected, customer must re-upload
                      'verified',         -- EFT approved / COD pre-approved
                      'dispatched',       -- Driver assigned and en route
                      'arrived',          -- Driver at location
                      'completed',        -- Delivery done, COD collected if applicable
                      'cancelled'         -- Cancelled by admin or customer
                    )),
  eft_proof_url     TEXT,                 -- Supabase Storage URL
  assigned_driver   UUID REFERENCES profiles(id),
  dispatch_notes    TEXT,
  created_at        TIMESTAMPTZ DEFAULT NOW(),
  updated_at        TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `order_status_log`
Immutable audit trail of every status change.

```sql
CREATE TABLE order_status_log (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id    UUID NOT NULL REFERENCES gas_orders(id),
  old_status  TEXT,
  new_status  TEXT NOT NULL,
  changed_by  UUID REFERENCES profiles(id),
  note        TEXT,
  created_at  TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `inventory`
Cylinder stock tracking.

```sql
CREATE TABLE inventory (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  cylinder_size   NUMERIC(5,1) NOT NULL,  -- 9, 19, 48
  stock_count     INT NOT NULL DEFAULT 0,
  last_inspected  DATE,
  updated_by      UUID REFERENCES profiles(id),
  updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Table: `bank_config`
Admin-configured bank details shown to customers on EFT screen.

```sql
CREATE TABLE bank_config (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  bank_name    TEXT NOT NULL,
  account_name TEXT NOT NULL,
  account_no   TEXT NOT NULL,
  branch_code  TEXT,
  is_active    BOOLEAN DEFAULT TRUE,
  updated_by   UUID REFERENCES profiles(id),
  updated_at   TIMESTAMPTZ DEFAULT NOW()
);
```

---

### Row Level Security (RLS) Rules

```
profiles:
  - Customer: SELECT own row only. UPDATE own row only.
  - Driver: SELECT own row only.
  - Admin: SELECT/UPDATE all rows.

gas_orders:
  - Customer: SELECT/INSERT own orders. UPDATE own orders (cancel only if status=pending).
  - Driver: SELECT assigned orders only (WHERE assigned_driver = auth.uid()).
  - Admin: All operations.

delivery_locations:
  - Customer: SELECT/INSERT/UPDATE own locations only.
  - Driver: SELECT only (for assigned orders).
  - Admin: SELECT all.

price_config, delivery_zones, bank_config, inventory:
  - Customer: SELECT only (active rows).
  - Driver: SELECT only.
  - Admin: All operations.
```

---

## 8. PHP APPLICATION STRUCTURE

```
/app
  /public          ← Web root (Nginx points here)
    index.php      ← Front controller / router
    /assets
      /css
      /js
      /img
  /src
    /Controllers
      AuthController.php
      OrderController.php
      LocationController.php
      AdminController.php
      DriverController.php
      PricingController.php
    /Middleware
      AuthMiddleware.php   ← Check JWT from Supabase
      RoleMiddleware.php   ← Check role before route
    /Services
      SupabaseClient.php   ← cURL wrapper for Supabase REST + Auth
      StorageService.php   ← EFT file upload handler
      OrderRefService.php  ← Generate MG-XXXXX refs
    /Views
      /customer
      /admin
      /driver
      /shared
        layout.php
        navbar.php
    /Config
      config.php           ← Env vars loaded here (never hardcoded)
  /docker
    Dockerfile
    nginx.conf
  docker-compose.yml
  .env.example
```

---

## 9. KEY API ENDPOINTS (PHP Routes)

All routes go through `public/index.php` front controller.
Auth check runs on every non-public route via `AuthMiddleware`.

### Public Routes
| Method | Route | Action |
|---|---|---|
| GET | `/` | Splash page |
| GET | `/login` | Login page |
| POST | `/login` | Submit credentials → Supabase Auth |
| GET | `/register` | Register page |
| POST | `/register` | Create user → Supabase Auth + insert `profiles` |
| POST | `/logout` | Invalidate session |

### Customer Routes (role: customer)
| Method | Route | Action |
|---|---|---|
| GET | `/home` | Dashboard — fetch current price + active order |
| GET | `/order/new` | Step 1: cylinder selection |
| POST | `/order/new` | Save step 1 to PHP session |
| GET | `/order/location` | Step 2: map + address picker |
| POST | `/order/location` | Validate zone, save to session |
| GET | `/order/checkout` | Step 3: summary + payment choice |
| POST | `/order/place` | Create `gas_orders` record in Supabase |
| GET | `/order/confirm/{id}` | Confirmation screen |
| GET | `/order/track/{id}` | Tracking screen (polls `/api/order-status/{id}`) |
| GET | `/order/{id}/eft-upload` | EFT upload screen |
| POST | `/order/{id}/eft-upload` | Upload file → Supabase Storage → update order URL |
| GET | `/orders` | Order history |
| GET | `/addresses` | Saved addresses list |
| POST | `/addresses` | Add new address |
| DELETE | `/addresses/{id}` | Delete address |
| GET | `/profile` | Profile page |
| POST | `/profile` | Update name/phone |

### Admin Routes (role: admin)
| Method | Route | Action |
|---|---|---|
| GET | `/admin` | Dashboard with stats |
| GET | `/admin/pricing` | Pricing control page |
| POST | `/admin/pricing/price` | Insert new price_config row |
| POST | `/admin/pricing/zone/{id}` | Update zone delivery fee |
| POST | `/admin/pricing/cod` | Toggle COD on/off + set cap |
| GET | `/admin/orders` | Dispatch board |
| POST | `/admin/orders/{id}/assign` | Assign driver to order |
| POST | `/admin/orders/{id}/dispatch` | Set status = dispatched |
| POST | `/admin/orders/{id}/cancel` | Cancel order |
| GET | `/admin/eft` | EFT auditor list |
| POST | `/admin/eft/{id}/approve` | Set status = verified |
| POST | `/admin/eft/{id}/reject` | Set status = eft_rejected, notify customer |
| GET | `/admin/zones` | Zone manager |
| POST | `/admin/zones` | Create new zone |
| PUT | `/admin/zones/{id}` | Update zone polygon/fee |
| GET | `/admin/inventory` | Inventory list |
| POST | `/admin/inventory/{size}` | Update stock count |
| GET | `/admin/users` | User list |
| POST | `/admin/users/{id}/role` | Change user role |

### Driver Routes (role: driver)
| Method | Route | Action |
|---|---|---|
| GET | `/driver` | Driver dashboard |
| GET | `/driver/tasks` | Task list |
| GET | `/driver/task/{id}` | Delivery detail |
| POST | `/driver/task/{id}/arrive` | Set status = arrived |
| GET | `/driver/task/{id}/cod` | COD confirmation screen |
| POST | `/driver/task/{id}/cod` | Log cash collected, set status = completed |
| POST | `/driver/task/{id}/complete` | Mark EFT order as completed |

### JSON API (polled by frontend JS)
| Method | Route | Returns |
|---|---|---|
| GET | `/api/order-status/{id}` | `{status, updated_at}` — for tracking screen polling |
| GET | `/api/current-price` | `{price_per_kg, effective_from}` |

---

## 10. EDGE CASES & ERROR HANDLING

### Authentication
| Edge Case | Handling |
|---|---|
| Wrong password | "Email or password is incorrect." (vague, no enumeration) |
| Unverified email | "Please verify your email before signing in." |
| Session expired mid-order | Save order state to PHP session, redirect to login, return to checkout after re-auth |
| User tries to access `/admin` as customer | RoleMiddleware redirects to `/home` with no error message shown |
| Driver tries to access `/admin` | Same as above |

### Order Placement
| Edge Case | Handling |
|---|---|
| Price changes while customer is on checkout | On form submit, re-fetch price. If different from session value, show: "Price updated to R{new}/kg. Please review your total." Block submission until customer re-confirms. |
| Customer pins location outside all zones | Block Step 2 → show: "We don't deliver to this area yet. Contact us." |
| Customer has an active order already | On `/home`, show "You have an active order" banner. Disable "ORDER NOW" button. One active order per customer at a time. |
| COD cap exceeded | If order total > COD cap, remove COD option from checkout silently. Do not explain the cap amount. |
| COD disabled by admin | Same — hide COD option completely |
| Quantity set to 0 | HTML min=1, server also rejects qty < 1 with 400 error |
| Duplicate order submit (double-tap) | Server checks: if identical order placed within 60 seconds by same customer, return existing order ID, do not create duplicate |

### EFT Upload
| Edge Case | Handling |
|---|---|
| File too large (>5MB) | Client-side check before upload. Server also validates. Error: "File too large. Max 5MB." |
| Wrong file type | Accept: jpg, jpeg, png, pdf only. Server validates MIME type, not just extension. Error: "Only JPG, PNG, or PDF files accepted." |
| Customer uploads for wrong order | URL includes order ID. Server validates order belongs to authenticated customer. |
| EFT rejected by admin | Order status = `eft_rejected`. Customer sees banner on tracking screen: "Your payment proof was rejected. Please re-upload." |
| Customer re-uploads after rejection | Allowed. New file overwrites old URL. Status resets to `eft_submitted`. |
| Admin approves wrong amount | No automatic amount validation in Phase 1 — admin is responsible for manual cross-check. Phase 2: add OCR via Groq. |

### Driver
| Edge Case | Handling |
|---|---|
| Driver tries to access an order not assigned to them | RLS blocks the Supabase query. PHP returns 403. |
| COD amount entered is less than order total | Show warning: "Amount entered (R{x}) is less than the order total (R{y}). Are you sure?" — require confirmation tap. Allow it — driver may have issued a discount (admin follow-up). |
| Driver marks arrive but customer not home | No specific screen for Phase 1 — driver contacts admin via phone. Phase 2: add "Customer Not Home" status. |
| Driver completes order twice | Server: if status is already `completed`, return success without updating. Idempotent. |

### Admin
| Edge Case | Handling |
|---|---|
| Admin sets price to R0 | Validation: price must be > R1. Show inline error. |
| Admin tries to dispatch order with no drivers registered | Driver dropdown is empty — show: "No drivers available. Add a driver in User Management first." |
| Admin deletes a zone that has active orders | Block deletion. Show: "Zone has active orders. Resolve orders before removing this zone." |
| Two admins approve/reject same EFT simultaneously | Supabase update is atomic. Second admin will see status already changed. Show: "This EFT has already been processed." |

### Infrastructure
| Edge Case | Handling |
|---|---|
| Supabase is down | PHP catches failed cURL request. Show generic: "Service temporarily unavailable. Please try again." Log error server-side. |
| File upload to Storage fails | Show: "Upload failed. Please try again." Do not change order status. |
| Session lost (server restart) | User returned to `/login`. Session is not used to store order totals permanently — order is created in DB on submit, not before. |

---

## 11. DOCKER SETUP

### `docker-compose.yml`
```yaml
version: '3.8'
services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./app/public:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php

  php:
    build: ./docker
    volumes:
      - ./app:/var/www/html
    env_file:
      - .env
```

### `docker/Dockerfile`
```dockerfile
FROM php:8.2-fpm-alpine
RUN apk add --no-cache curl
RUN docker-php-ext-install pdo pdo_mysql
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

### `.env.example`
```env
SUPABASE_URL=https://xxxx.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-role-key
APP_ENV=development
APP_SECRET=change-me-random-32-chars
R2_BUCKET_URL=
R2_ACCESS_KEY=
R2_SECRET_KEY=
```

---

## 12. SUPABASE SETUP CHECKLIST

- [ ] Create project at supabase.com
- [ ] Enable PostGIS: `CREATE EXTENSION postgis;`
- [ ] Run all SQL from Section 7 in the SQL editor
- [ ] Enable Row Level Security on all tables
- [ ] Apply RLS policies from Section 7
- [ ] Create Storage bucket: `eft-proofs` (private)
- [ ] Set bucket policy: authenticated users can upload; only admin can read all
- [ ] Set up Auth email templates (confirmation, password reset)
- [ ] Create trigger: on `auth.users` insert → insert into `profiles`
- [ ] Seed: insert first admin manually into `profiles` with role='admin'
- [ ] Seed: insert opening price into `price_config`
- [ ] Seed: insert bank details into `bank_config`

---

## 13. STORAGE PLAN

### Phase 1 — Supabase Storage
- Bucket: `eft-proofs`
- Path pattern: `eft-proofs/{order_id}/{timestamp}.{ext}`
- Access: private (signed URLs generated by PHP backend for admin view)

### Phase 2 — Migrate to Cloudflare R2
- R2 is S3-compatible; swap `StorageService.php` to use R2 endpoint
- No code changes in controllers needed — only `StorageService` changes
- R2 free tier: 10GB storage, 10M reads/month — sufficient for early scale

---

## 14. GROQ AI — INTEGRATION ROADMAP

Groq is not in Phase 1. Planned integration points for Phase 2:

| Feature | How Groq Helps |
|---|---|
| EFT Receipt OCR | Send uploaded image to Groq vision → extract amount + reference → auto-compare to order total |
| Admin Order Summary | Groq generates a daily plain-English summary: "Today: 12 orders, R4,100 revenue, 2 EFTs pending" |
| Customer Support Chat | Lightweight in-app chat for order status questions, routed through Groq with order context injected |
| Zone Suggestion | Groq analyses delivery patterns and suggests new zone boundaries to admin |

**Implementation note:** Groq calls will always be server-side (PHP). Never expose the API key to the browser.

---

## 15. DEFINITION OF DONE — PHASE 1 MVP

Phase 1 is complete when the following works end-to-end with real data:

- [ ] Customer can register, login, and logout
- [ ] Customer can place a gas order (EFT or COD)
- [ ] Customer can upload EFT proof
- [ ] Customer can view order tracking status (refreshes automatically)
- [ ] Admin can login to `/admin`
- [ ] Admin can update the gas price per kg
- [ ] Admin can approve or reject an EFT upload
- [ ] Admin can assign a driver and dispatch an order
- [ ] Driver can login to `/driver`
- [ ] Driver can see assigned tasks and mark delivery complete
- [ ] Driver can confirm COD cash collected
- [ ] Card Payment is shown as "Coming Soon" and is not clickable
- [ ] All pages are usable on a 375px-wide mobile screen
- [ ] No hardcoded prices — all prices come from the database

---

## 16. NAMING CONVENTIONS

| Item | Convention | Example |
|---|---|---|
| PHP Classes | PascalCase | `OrderController` |
| PHP Methods | camelCase | `placeOrder()` |
| DB Tables | snake_case | `gas_orders` |
| DB Columns | snake_case | `payment_status` |
| Routes | kebab-case | `/eft-upload` |
| JS variables | camelCase | `selectedCylinder` |
| CSS classes | kebab-case | `price-card` |
| Order refs | MG-{5 digit} | `MG-00124` |
| EFT Storage paths | `eft-proofs/{uuid}/{ts}.jpg` | `eft-proofs/abc123/1721558400.jpg` |
