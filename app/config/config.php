<?php
// Musina Gas Application Configuration

return [
    'app_name' => getenv('APP_NAME') ?: 'Musina Gas',
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8080',
    'app_env' => getenv('APP_ENV') ?: 'development',
    'app_secret' => getenv('APP_SECRET') ?: 'musinagas-secret-key-32-chars-minimum!!',

    'database_url' => getenv('DATABASE_URL') ?: '',

    'supabase' => [
        'url' => getenv('SUPABASE_URL') ?: 'https://your-project.supabase.co',
        'anon_key' => getenv('SUPABASE_ANON_KEY') ?: '',
        'service_key' => getenv('SUPABASE_SERVICE_KEY') ?: '',
    ],

    'cylinder_sizes' => [
        '9' => ['name' => '9kg Cylinder', 'size_kg' => 9, 'description' => 'Ideal for standard home cooking & heating'],
        '19' => ['name' => '19kg Cylinder', 'size_kg' => 19, 'description' => 'Great for large families & braais'],
        '48' => ['name' => '48kg Cylinder', 'size_kg' => 48, 'description' => 'Commercial & high-usage heating'],
    ],

    'cod_settings' => [
        'enabled' => true,
        'max_cap' => 1000.00, // ZAR
    ]
];
