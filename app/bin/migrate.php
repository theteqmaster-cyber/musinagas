<?php

/**
 * Musina Gas - Automated Database Migration CLI Script
 * Executes schema.sql and seed.sql directly via PostgreSQL connection string
 */

require_once __DIR__ . '/../config/config.php';

$dbUrl = getenv('DATABASE_URL') ?: '';

if (empty($dbUrl) || strpos($dbUrl, 'your-project') !== false) {
    echo "⚠️ DATABASE_URL not configured in .env file.\n";
    echo "Please set DATABASE_URL=postgresql://postgres:PASSWORD@db.PROJECT.supabase.co:5432/postgres in .env\n";
    exit(1);
}

// Parse PostgreSQL DSN connection URL
$dbParts = parse_url($dbUrl);

if (!$dbParts || !isset($dbParts['host'])) {
    echo "❌ Invalid DATABASE_URL format.\n";
    exit(1);
}

$host = $dbParts['host'];
$port = $dbParts['port'] ?? 5432;
$user = $dbParts['user'] ?? 'postgres';
$pass = $dbParts['pass'] ?? '';
$dbname = ltrim($dbParts['path'] ?? '/postgres', '/');

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

try {
    echo "🔌 Connecting to Supabase PostgreSQL at {$host}:{$port}...\n";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connection established successfully!\n";

    // 1. Run Schema SQL
    $schemaFile = __DIR__ . '/../../database/schema.sql';
    if (file_exists($schemaFile)) {
        echo "📜 Running database/schema.sql...\n";
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        echo "✅ Schema applied successfully!\n";
    }

    // 2. Run Seed SQL
    $seedFile = __DIR__ . '/../../database/seed.sql';
    if (file_exists($seedFile)) {
        echo "🌱 Running database/seed.sql...\n";
        $sql = file_get_contents($seedFile);
        $pdo->exec($sql);
        echo "✅ Seed data inserted successfully!\n";
    }

    echo "🎉 Database setup complete! No manual SQL pasting required.\n";

} catch (PDOException $e) {
    echo "❌ Database Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
