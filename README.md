# Musina Gas Version 2

A high-performance LPG cylinder ordering, dispatching, and tracking web application for Musina and border region clients.

Built with **Docker + PHP 8.2-FPM + Nginx + Supabase (PostgreSQL / PostGIS)**.

## Architecture

- **Web Server**: Nginx Alpine (`port 8080`)
- **App Engine**: PHP 8.2-FPM with PDO PostgreSQL (`pdo_pgsql`) & cURL
- **Database**: Supabase PostgreSQL + PostGIS (with zero-config local fallback engine)
- **Frontend**: Vanilla HTML5, CSS3 Glassmorphic Design System, Leaflet.js

## Roles

1. **Customer**: Place orders, set Leaflet map drop-off pin, upload EFT proofs, live tracking.
2. **Admin**: Dashboard overview, pricing per kg controls, order dispatch board, EFT auditor.
3. **Driver**: Assigned task list, customer address & phone dialer, COD cash collection calculator.

## Running Locally

```bash
docker compose up -d
```

Access the app at `http://localhost:8080`.

### Database Connection String & Automated Migrations

Set your Supabase PostgreSQL connection string in `.env`:

```env
DATABASE_URL=postgresql://postgres:YOUR_PASSWORD@db.YOUR_PROJECT_REF.supabase.co:5432/postgres
```

To run automated schema migrations and seed data without copying/pasting into SQL editor:

```bash
php app/bin/migrate.php
```
