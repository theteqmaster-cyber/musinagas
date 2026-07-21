# MUSINA GAS — DEVELOPER SPECIFICATION
## Part 1: Architecture, Screens & User Flows
**Version:** 1.0 | **Date:** July 2026 | **Status:** Pre-build

---

## 1. TECH STACK DECISIONS

| Layer | Technology | Reason |
|---|---|---|
| Language | PHP 8.2+ | Developer preference, server-side rendering |
| Web Server | Nginx (in Docker) | Performance, easy config |
| Containerisation | Docker + Docker Compose | Consistent dev/prod environments |
| Database | Supabase (PostgreSQL + PostGIS) | Managed, spatial queries, auth built-in |
| Storage (Phase 1) | Supabase Storage | EFT proof uploads, simple to start |
| Storage (Phase 2) | Cloudflare R2 | Cost-effective at scale, S3-compatible |
| Maps | Leaflet.js + OpenStreetMap | Free, no billing surprises |
| AI | Groq API | TBD — order summarisation or admin insights |
| Frontend | Vanilla HTML/CSS/JS (served by PHP) | No build step, fast mobile load |
| Payments | Coming Soon placeholder | No live gateway in Phase 1 |
| SMS Alerts | Africa's Talking or BulkSMS (Phase 2) | SA-local, affordable per-SMS pricing |

---

## 2. SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────┐
│               BROWSER / MOBILE              │
│     Customer / Driver / Admin Web App       │
│        (HTML + CSS + Vanilla JS)            │
└──────────────────┬──────────────────────────┘
                   │ HTTPS
┌──────────────────▼──────────────────────────┐
│            DOCKER CONTAINER                 │
│     Nginx  ──►  PHP 8.2 App (FPM)          │
│     - Routing (index.php front controller) │
│     - Session management                   │
│     - Role middleware                       │
│     - Supabase REST API calls (cURL/Guzzle)│
└──────────────────┬──────────────────────────┘
                   │ HTTPS (REST / PostgREST)
┌──────────────────▼──────────────────────────┐
│               SUPABASE                      │
│   PostgreSQL + PostGIS  │  Auth (JWT)       │
│   Row Level Security    │  Storage Buckets  │
└─────────────────────────────────────────────┘
```

### Docker Compose Services
```yaml
services:
  nginx:    # Port 80/443, proxies to php-fpm
  php:      # PHP 8.2-fpm, app code mounted
  # No local DB — Supabase is remote
```

---

## 3. USER ROLES

| Role | Access Level | Primary Device |
|---|---|---|
| **Customer** | Own orders, own locations, view price | Mobile (phone) |
| **Driver** | Assigned tasks only, COD confirmation | Mobile (phone) |
| **Admin** | Full system access | Desktop browser |

> **Rule:** Role is stored in the `profiles` table in Supabase. A single email cannot hold two roles. Admins are seeded manually — no self-registration for Admin or Driver roles.

---

## 4. SCREEN INVENTORY

### 4A — CUSTOMER SCREENS (13 screens)

| # | Screen Name | Route |
|---|---|---|
| C1 | Splash / Landing | `/` |
| C2 | Login | `/login` |
| C3 | Register | `/register` |
| C4 | Home Dashboard | `/home` |
| C5 | New Order — Select Cylinder | `/order/new` |
| C6 | New Order — Set Location | `/order/location` |
| C7 | New Order — Checkout | `/order/checkout` |
| C8 | Order Confirmation | `/order/confirm/{id}` |
| C9 | Order Tracking | `/order/track/{id}` |
| C10 | Order History | `/orders` |
| C11 | Saved Addresses | `/addresses` |
| C12 | Profile & Account Settings | `/profile` |
| C13 | EFT Upload Screen | `/order/{id}/eft-upload` |

### 4B — ADMIN SCREENS (7 screens)

| # | Screen Name | Route |
|---|---|---|
| A1 | Admin Dashboard Overview | `/admin` |
| A2 | Pricing Control Panel | `/admin/pricing` |
| A3 | Order & Dispatch Board | `/admin/orders` |
| A4 | EFT Auditor | `/admin/eft` |
| A5 | Zone & Location Manager | `/admin/zones` |
| A6 | Inventory & Cylinders | `/admin/inventory` |
| A7 | User Management | `/admin/users` |

### 4C — DRIVER SCREENS (5 screens)

| # | Screen Name | Route |
|---|---|---|
| D1 | Driver Dashboard | `/driver` |
| D2 | Task List | `/driver/tasks` |
| D3 | Delivery Detail | `/driver/task/{id}` |
| D4 | COD Confirmation | `/driver/task/{id}/cod` |
| D5 | Delivery Complete | `/driver/task/{id}/complete` |

---

## 5. WIREFRAMES

---

### C1 — SPLASH / LANDING
```
┌─────────────────────────┐
│                         │
│                         │
│    🔥 [LOGO MARK]       │
│                         │
│    MUSINA GAS           │
│    ─────────────────    │
│    Fast. Safe.          │
│    Delivered to you.    │
│                         │
│                         │
│  ┌───────────────────┐  │
│  │    GET STARTED    │  │  ← Orange gradient button
│  └───────────────────┘  │
│                         │
│  Already a member?      │
│  [Sign In]              │
│                         │
└─────────────────────────┘
```

---

### C2 — LOGIN
```
┌─────────────────────────┐
│  ← Back                 │
│                         │
│  Welcome back           │
│  Sign in to continue    │
│                         │
│  Email                  │
│  ┌───────────────────┐  │
│  │ you@email.com     │  │
│  └───────────────────┘  │
│                         │
│  Password               │
│  ┌───────────────────┐  │
│  │ ••••••••          │  │
│  └───────────────────┘  │
│                         │
│  [Forgot password?]     │
│                         │
│  ┌───────────────────┐  │
│  │     SIGN IN       │  │
│  └───────────────────┘  │
│                         │
│  No account? [Register] │
└─────────────────────────┘
```
**Validation:**
- Email: required, valid format
- Password: required, min 8 chars
- On fail: inline error under the failing field, no page reload
- On success: redirect based on role (`/home`, `/driver`, `/admin`)

---

### C3 — REGISTER
```
┌─────────────────────────┐
│  ← Back                 │
│                         │
│  Create account         │
│                         │
│  Full Name              │
│  ┌───────────────────┐  │
│  │                   │  │
│  └───────────────────┘  │
│  Phone Number           │
│  ┌───────────────────┐  │
│  │ +27 ___ ___ ____  │  │
│  └───────────────────┘  │
│  Email                  │
│  ┌───────────────────┐  │
│  └───────────────────┘  │
│  Password               │
│  ┌───────────────────┐  │
│  └───────────────────┘  │
│  Account Type           │
│  ( ) Residential        │
│  ( ) Commercial         │
│                         │
│  ┌───────────────────┐  │
│  │   CREATE ACCOUNT  │  │
│  └───────────────────┘  │
└─────────────────────────┘
```
**Rules:**
- Phone: SA format +27, required (used for SMS alerts later)
- Account type: determines pricing scheme and order UI shown
- Role defaults to `customer` — cannot be changed by user

---

### C4 — HOME DASHBOARD (Customer)
```
┌─────────────────────────┐
│  Musina Gas    [👤]      │
│                         │
│  ┌─────────────────────┐│
│  │  Current Gas Price  ││  ← Glass panel
│  │                     ││
│  │    R 32.50 / kg     ││  ← Live from DB
│  │                     ││
│  │  Last updated:      ││
│  │  Today 09:14        ││
│  └─────────────────────┘│
│                         │
│  ACTIVE ORDER           │  ← Only shown if order exists
│  ┌─────────────────────┐│
│  │  Order #MG-00123    ││
│  │  Status: Dispatched ││
│  │  [Track My Order →] ││
│  └─────────────────────┘│
│                         │
│  ┌───────────────────┐  │
│  │   ORDER GAS NOW   │  │
│  └───────────────────┘  │
│                         │
│  [History] [Addresses]  │
│         [Profile]       │  ← Bottom nav
└─────────────────────────┘
```

---

### C5 — NEW ORDER: SELECT CYLINDER
```
┌─────────────────────────┐
│  ← Back   New Order     │
│  Step 1 of 3            │
│  ━━━━━━━━░░░░░░░░░░░░   │
│                         │
│  Select Cylinder Size   │
│                         │
│  ┌───────┐┌───────┐┌────│
│  │  9kg  ││ 19kg  ││48kg│  ← Tappable cards
│  │       ││       ││    │
│  │ R292  ││ R617  ││R156│  ← Auto-calc from price
│  └───────┘└───────┘└────│
│                         │
│  Quantity               │
│  ┌──────────────────┐   │
│  │  [ − ]  1  [ + ] │   │  ← Stepper, max 10
│  └──────────────────┘   │
│                         │
│  ORDER SUMMARY          │
│  ┌──────────────────┐   │
│  │ 9kg × 1 = R 292  │   │
│  │ Delivery: TBD    │   │  ← Set on next step
│  └──────────────────┘   │
│                         │
│  ┌───────────────────┐  │
│  │   NEXT: LOCATION  │  │
│  └───────────────────┘  │
└─────────────────────────┘
```
**Rules:**
- Prices are fetched fresh from DB on page load — never cached client-side
- Bulk option shown only for Commercial accounts
- Quantity min: 1, max: 10 (residential) / 50 (commercial)

---

### C6 — NEW ORDER: SET LOCATION
```
┌─────────────────────────┐
│  ← Back   New Order     │
│  Step 2 of 3            │
│  ━━━━━━━━━━━━━━░░░░░░   │
│                         │
│  ┌─────────────────────┐│
│  │                     ││
│  │    [ MAP VIEW ]     ││  ← Leaflet.js map
│  │                     ││
│  │      📍 [pin]       ││  ← Draggable pin
│  │                     ││
│  └─────────────────────┘│
│                         │
│  [Use My Current GPS]   │  ← Geolocation API button
│                         │
│  OR pick saved address: │
│  ┌──────────────────┐   │
│  │ 12 Vhembe Rd  ▼  │   │  ← Dropdown of saved addresses
│  └──────────────────┘   │
│                         │
│  Access Instructions    │  ← Optional free text
│  ┌──────────────────┐   │
│  │ e.g. Blue gate,  │   │
│  │ ring bell twice  │   │
│  └──────────────────┘   │
│                         │
│  ┌───────────────────┐  │
│  │  NEXT: CHECKOUT   │  │
│  └───────────────────┘  │
└─────────────────────────┘
```
**Rules:**
- If pin placed outside all defined delivery zones → show error: "Sorry, we don't deliver to this area yet."
- Do not proceed to checkout until a valid zone pin is set
- "Save this address" checkbox — if checked, saves to `delivery_locations` table

---

### C7 — CHECKOUT
```
┌─────────────────────────┐
│  ← Back   Checkout      │
│  Step 3 of 3            │
│  ━━━━━━━━━━━━━━━━━━━━━  │
│                         │
│  ORDER SUMMARY          │
│  ─────────────────────  │
│  9kg cylinder × 1       │
│  Gas:           R 292   │
│  Delivery fee:  R  50   │  ← From zone config
│  ─────────────────────  │
│  TOTAL:         R 342   │
│                         │
│  Delivery to:           │
│  12 Vhembe Rd, Musina   │
│  [Change]               │
│                         │
│  PAYMENT METHOD         │
│  ─────────────────────  │
│  ┌──────────────────┐   │
│  │  💳 Card Payment │   │  ← Disabled, "Coming Soon" badge
│  │     COMING SOON  │   │
│  └──────────────────┘   │
│                         │
│  ┌──────────────────┐   │
│  │  🏦 EFT Transfer │   │  ← Active
│  └──────────────────┘   │
│                         │
│  ┌──────────────────┐   │
│  │  💵 Cash on Del. │   │  ← Active (if COD enabled by admin)
│  └──────────────────┘   │
│                         │
│  ┌───────────────────┐  │
│  │   PLACE ORDER     │  │
│  └───────────────────┘  │
└─────────────────────────┘
```
**Rules:**
- Card Payment button is visible but disabled with a "Coming Soon" label — never clickable
- COD option only shows if admin has enabled COD globally AND the order total is below admin-set COD cap
- Delivery fee is looked up from `delivery_zones` table using the pinned coordinates
- Placing order creates `gas_order` record with `status = pending`

---

### C8 — ORDER CONFIRMATION
```
┌─────────────────────────┐
│                         │
│        ✅               │
│   Order Placed!         │
│                         │
│   Order #MG-00124       │
│                         │
│   What happens next:    │
│   ─────────────────     │
│   IF EFT chosen:        │
│   Upload your proof of  │
│   payment below.        │
│   ┌──────────────────┐  │
│   │  UPLOAD EFT PROOF│  │
│   └──────────────────┘  │
│                         │
│   IF COD chosen:        │
│   Your order is pending │
│   admin approval.       │
│   Have R 342 ready.     │
│                         │
│   ┌──────────────────┐  │
│   │  TRACK MY ORDER  │  │
│   └──────────────────┘  │
│                         │
│   [Back to Home]        │
└─────────────────────────┘
```

---

### C13 — EFT UPLOAD
```
┌─────────────────────────┐
│  ← Back                 │
│  Upload Payment Proof   │
│  Order #MG-00124        │
│                         │
│  Bank Details:          │
│  ┌──────────────────┐   │
│  │ Bank: FNB        │   │
│  │ Acc: 623XXXXXXX  │   │  ← Loaded from admin config
│  │ Ref: MG-00124    │   │
│  │ Amount: R 342.00 │   │
│  └──────────────────┘   │
│                         │
│  Upload Screenshot      │
│  ┌──────────────────┐   │
│  │                  │   │
│  │  [ TAP TO       ]│   │
│  │  [ UPLOAD FILE  ]│   │  ← jpg/png/pdf, max 5MB
│  │                  │   │
│  └──────────────────┘   │
│                         │
│  ┌───────────────────┐  │
│  │   SUBMIT PROOF    │  │
│  └───────────────────┘  │
│                         │
│  ⚠ Admin will verify    │
│  within 2 business hrs  │
└─────────────────────────┘
```

---

### C9 — ORDER TRACKING
```
┌─────────────────────────┐
│  ← Back   Order Tracking│
│  #MG-00124              │
│                         │
│  ●━━━━━━━━━━━━━━━━━●    │  ← Progress dots
│  Placed  Verified  ETA  │
│                         │
│  Current Status:        │
│  ┌──────────────────┐   │
│  │ 🚗 Dispatched    │   │
│  │ Driver on the way│   │
│  └──────────────────┘   │
│                         │
│  Estimated Arrival:     │
│  14:30 — 15:00          │
│                         │
│  Delivering to:         │
│  12 Vhembe Rd, Musina   │
│                         │
│  ─────────────────────  │
│  STATUS HISTORY         │
│  ✅ 12:00 — Order placed│
│  ✅ 12:45 — EFT verified│
│  ✅ 13:10 — Dispatched  │
│  ⏳ -- :-- — Arrived    │
└─────────────────────────┘
```

---

### A1 — ADMIN DASHBOARD
```
┌──────────────────────────────────────┐
│  MUSINA GAS ADMIN    [⚙] [Logout]   │
│  ─────────────────────────────────  │
│                                      │
│  TODAY AT A GLANCE                   │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌─────┐ │
│  │  12  │ │  3   │ │  R   │ │  2  │ │
│  │Orders│ │Pend. │ │4,100 │ │EFT  │ │
│  │      │ │EFT   │ │Rev.  │ │Pend.│ │
│  └──────┘ └──────┘ └──────┘ └─────┘ │
│                                      │
│  LIVE ORDERS FEED                    │
│  ┌────────────────────────────────┐  │
│  │ #MG-124 │ John M. │ 9kg │ COD  │  │
│  │ Pending ── ── ── ── [Dispatch] │  │
│  ├────────────────────────────────┤  │
│  │ #MG-123 │ Sarah L.│19kg │ EFT  │  │
│  │ Awaiting EFT ── ── [View EFT] │  │
│  └────────────────────────────────┘  │
│                                      │
│  CURRENT PRICE: R 32.50/kg  [Edit]   │
│                                      │
│  [Pricing][Orders][EFT][Zones][Inv.] │
└──────────────────────────────────────┘
```

---

### A2 — PRICING CONTROL
```
┌──────────────────────────────────────┐
│  ← Back   Pricing Control Panel      │
│                                      │
│  RESIDENTIAL PRICING                 │
│  ─────────────────────               │
│  Price per KG:                       │
│  ┌──────────────────────┐            │
│  │  R  [ 32.50 ]        │  [Update]  │
│  └──────────────────────┘            │
│                                      │
│  DELIVERY ZONE FEES                  │
│  ─────────────────────               │
│  Zone A (0–5 km):   R [ 30 ] [Save]  │
│  Zone B (5–15 km):  R [ 50 ] [Save]  │
│  Zone C (15–30 km): R [ 80 ] [Save]  │
│                                      │
│  COD SETTINGS                        │
│  ─────────────────────               │
│  Enable COD:  [ ON / OFF toggle ]    │
│  COD Max Cap: R [ 1000 ]  [Save]     │
│                                      │
│  PRICE HISTORY                       │
│  ┌──────────────────────────────┐    │
│  │ Jul 21 │ R32.50 │ by Admin   │    │
│  │ Jul 15 │ R30.00 │ by Admin   │    │
│  └──────────────────────────────┘    │
└──────────────────────────────────────┘
```
**Rules:**
- Price update takes effect IMMEDIATELY for all new orders
- Existing pending orders keep the price they were created with (locked at order time)
- Price history is read-only log — cannot be edited

---

### A4 — EFT AUDITOR
```
┌──────────────────────────────────────┐
│  ← Back   EFT Payment Auditor        │
│                                      │
│  Filter: [All ▼]  [Today ▼]  [Search]│
│                                      │
│  ┌────────────────────────────────┐  │
│  │ #MG-123 | Sarah L. | R617     │  │
│  │ Submitted: 13:22 today        │  │
│  │ [View Proof Image]            │  │
│  │                               │  │
│  │ [✅ APPROVE]  [❌ REJECT]     │  │
│  └────────────────────────────────┘  │
│  ┌────────────────────────────────┐  │
│  │ #MG-119 | Mike D. | R292      │  │
│  │ Submitted: yesterday 16:00    │  │
│  │ Status: ✅ Approved           │  │
│  └────────────────────────────────┘  │
└──────────────────────────────────────┘
```

---

### D2 — DRIVER TASK LIST
```
┌─────────────────────────┐
│  My Tasks  [Logout]     │
│                         │
│  TODAY: 4 deliveries    │
│                         │
│  ┌──────────────────┐   │
│  │ #MG-124          │   │
│  │ 9kg × 1          │   │
│  │ 12 Vhembe Rd     │   │
│  │ Payment: COD R342│   │
│  │ [START DELIVERY] │   │
│  └──────────────────┘   │
│                         │
│  ┌──────────────────┐   │
│  │ #MG-125          │   │
│  │ 19kg × 2         │   │
│  │ 5 Kruger St      │   │
│  │ Payment: EFT ✅  │   │
│  │ [START DELIVERY] │   │
│  └──────────────────┘   │
│                         │
│  COMPLETED TODAY: 2 ✅  │
└─────────────────────────┘
```

---

### D4 — COD CONFIRMATION
```
┌─────────────────────────┐
│  ← Back  COD Collection │
│  Order #MG-124          │
│                         │
│  Amount to collect:     │
│                         │
│       R 342.00          │
│                         │
│  ─────────────────────  │
│  Cash Received?         │
│                         │
│  Amount Given by        │
│  Customer:              │
│  ┌──────────────────┐   │
│  │ R [ ___________ ]│   │
│  └──────────────────┘   │
│                         │
│  Change to give back:   │
│  R 0.00 (auto calc)     │
│                         │
│  ┌───────────────────┐  │
│  │  CONFIRM RECEIPT  │  │
│  └───────────────────┘  │
└─────────────────────────┘
```

---

## 6. USER JOURNEY FLOWS

### Customer — Place an Order (Happy Path)
```
[Splash] → [Login] → [Home Dashboard]
→ Tap "ORDER GAS NOW"
→ [Select Cylinder] → Pick 9kg, qty 1
→ Tap "NEXT: LOCATION"
→ [Set Location] → Drop pin or use GPS → Tap "NEXT: CHECKOUT"
→ [Checkout] → Review total R342 → Select EFT
→ Tap "PLACE ORDER"
→ [Order Confirmation] → Tap "UPLOAD EFT PROOF"
→ [EFT Upload] → Upload screenshot → Tap "SUBMIT"
→ Status changes to "Awaiting EFT Verification"
→ [Order Tracking] → Polls every 30 seconds for status update
→ Admin approves EFT → Status: "Dispatched"
→ Driver completes → Status: "Arrived" → DONE
```

### Admin — Approve EFT and Dispatch
```
[Admin Dashboard] → See badge: "2 EFT Pending"
→ Tap [View EFT]
→ [EFT Auditor] → Opens proof image
→ Amount matches? → Tap [✅ APPROVE]
→ System sets order status = "verified"
→ Admin goes to [Order & Dispatch Board]
→ Finds order → Assigns driver from dropdown
→ Taps [DISPATCH]
→ Order status = "dispatched"
→ Driver sees task appear in their task list
```

### Driver — Complete a COD Delivery
```
[Driver Dashboard] → [Task List]
→ Tap [START DELIVERY] on #MG-124
→ [Delivery Detail] → See address + access instructions
→ Arrive at location → Tap [MARK ARRIVED]
→ Order status = "arrived"
→ [COD Confirmation] → Enter amount received
→ Tap [CONFIRM RECEIPT]
→ Order status = "completed"
→ Task moves to "Completed Today" list
```
