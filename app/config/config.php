<?php
// Musina Gas Application Configuration

return [
    'app_name' => getenv('APP_NAME') ?: 'Musina Gas',
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8085',
    'app_env' => getenv('APP_ENV') ?: 'development',
    'app_secret' => getenv('APP_SECRET') ?: 'musinagas-secret-key-32-chars-minimum!!',

    'firebase' => [
        'api_key' => getenv('FIREBASE_API_KEY') ?: 'AIzaSyD0uQBAt15z4uwYqz9dNhjtDndkb9rvn3s',
        'auth_domain' => getenv('FIREBASE_AUTH_DOMAIN') ?: 'musinagas-42910.firebaseapp.com',
        'project_id' => getenv('FIREBASE_PROJECT_ID') ?: 'musinagas-42910',
        'storage_bucket' => getenv('FIREBASE_STORAGE_BUCKET') ?: 'musinagas-42910.firebasestorage.app',
        'messaging_sender_id' => getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '494442545277',
        'app_id' => getenv('FIREBASE_APP_ID') ?: '1:494442545277:web:7b0afeb76a57c6ab547114',
    ],

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
