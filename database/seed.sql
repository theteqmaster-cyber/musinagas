-- MUSINA GAS VERSION 2 — SEED DATA

-- Initial Price per KG
INSERT INTO price_config (price_per_kg, currency, notes)
VALUES (32.50, 'ZAR', 'Initial price configuration for Phase 1');

-- Delivery Zones in Musina
INSERT INTO delivery_zones (zone_name, delivery_fee, is_active)
VALUES
  ('Zone A (Central Musina / CBD)', 30.00, true),
  ('Zone B (Nancefield & Suburbs)', 50.00, true),
  ('Zone C (Outskirts / Border Area)', 80.00, true);

-- Default Inventory
INSERT INTO inventory (cylinder_size, stock_count, last_inspected)
VALUES
  (9.0, 150, CURRENT_DATE),
  (19.0, 85, CURRENT_DATE),
  (48.0, 40, CURRENT_DATE);

-- Default Bank Configuration
INSERT INTO bank_config (bank_name, account_name, account_no, branch_code, is_active)
VALUES
  ('First National Bank (FNB)', 'Musina Gas Pty Ltd', '62891048291', '250655', true);
