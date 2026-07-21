-- MUSINA GAS VERSION 2 — DATABASE SCHEMA (PostgreSQL / Supabase)
-- PostGIS Extension required for boundary checking

CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Profiles (Extends Supabase auth.users)
CREATE TABLE IF NOT EXISTS profiles (
  id           UUID PRIMARY KEY,
  full_name    TEXT NOT NULL,
  phone        TEXT NOT NULL,
  role         TEXT NOT NULL DEFAULT 'customer' CHECK (role IN ('customer','driver','admin')),
  account_type TEXT NOT NULL DEFAULT 'residential' CHECK (account_type IN ('residential','commercial')),
  created_at   TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Price Configuration
CREATE TABLE IF NOT EXISTS price_config (
  id             UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  price_per_kg   NUMERIC(10,2) NOT NULL,
  currency       TEXT NOT NULL DEFAULT 'ZAR',
  effective_from TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  adjusted_by    UUID REFERENCES profiles(id),
  notes          TEXT
);

-- 3. Delivery Zones
CREATE TABLE IF NOT EXISTS delivery_zones (
  id            UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  zone_name     TEXT NOT NULL,
  zone_boundary GEOMETRY(POLYGON, 4326),
  delivery_fee  NUMERIC(10,2) NOT NULL,
  is_active     BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMPTZ DEFAULT NOW()
);

-- 4. Customer Delivery Locations
CREATE TABLE IF NOT EXISTS delivery_locations (
  id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  customer_id     UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  label           TEXT DEFAULT 'Home',
  latitude        NUMERIC(10,7) NOT NULL,
  longitude       NUMERIC(10,7) NOT NULL,
  digital_address TEXT,
  access_notes    TEXT,
  zone_id         UUID REFERENCES delivery_zones(id),
  is_default      BOOLEAN DEFAULT FALSE,
  created_at      TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Gas Orders
CREATE TABLE IF NOT EXISTS gas_orders (
  id               UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  order_ref        TEXT UNIQUE NOT NULL,
  customer_id      UUID NOT NULL REFERENCES profiles(id),
  location_id      UUID NOT NULL REFERENCES delivery_locations(id),
  cylinder_size_kg NUMERIC(5,1) NOT NULL,
  quantity         INT NOT NULL DEFAULT 1,
  price_per_kg     NUMERIC(10,2) NOT NULL,
  delivery_fee     NUMERIC(10,2) NOT NULL,
  total_amount     NUMERIC(10,2) NOT NULL,
  payment_method   TEXT NOT NULL CHECK (payment_method IN ('EFT','COD','CARD_PENDING')),
  payment_status   TEXT NOT NULL DEFAULT 'pending' CHECK (payment_status IN (
                     'pending',
                     'awaiting_eft',
                     'eft_submitted',
                     'eft_rejected',
                     'verified',
                     'dispatched',
                     'arrived',
                     'completed',
                     'cancelled'
                   )),
  eft_proof_url    TEXT,
  assigned_driver  UUID REFERENCES profiles(id),
  dispatch_notes   TEXT,
  created_at       TIMESTAMPTZ DEFAULT NOW(),
  updated_at       TIMESTAMPTZ DEFAULT NOW()
);

-- 6. Order Status Audit Log
CREATE TABLE IF NOT EXISTS order_status_log (
  id         UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  order_id   UUID NOT NULL REFERENCES gas_orders(id) ON DELETE CASCADE,
  old_status TEXT,
  new_status TEXT NOT NULL,
  changed_by UUID REFERENCES profiles(id),
  note       TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 7. Cylinder Stock Inventory
CREATE TABLE IF NOT EXISTS inventory (
  id             UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  cylinder_size  NUMERIC(5,1) NOT NULL UNIQUE,
  stock_count    INT NOT NULL DEFAULT 0,
  last_inspected DATE,
  updated_by     UUID REFERENCES profiles(id),
  updated_at     TIMESTAMPTZ DEFAULT NOW()
);

-- 8. Bank Account Details (for EFT transfers)
CREATE TABLE IF NOT EXISTS bank_config (
  id           UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  bank_name    TEXT NOT NULL,
  account_name TEXT NOT NULL,
  account_no   TEXT NOT NULL,
  branch_code  TEXT,
  is_active    BOOLEAN DEFAULT TRUE,
  updated_by   UUID REFERENCES profiles(id),
  updated_at   TIMESTAMPTZ DEFAULT NOW()
);

-- Row Level Security Rules
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE gas_orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE delivery_locations ENABLE ROW LEVEL SECURITY;
ALTER TABLE price_config ENABLE ROW LEVEL SECURITY;
ALTER TABLE delivery_zones ENABLE ROW LEVEL SECURITY;
ALTER TABLE bank_config ENABLE ROW LEVEL SECURITY;
ALTER TABLE inventory ENABLE ROW LEVEL SECURITY;
