<?php

namespace App\Services;

class SupabaseClient
{
    private string $url;
    private string $anonKey;
    private string $serviceKey;
    private bool $isMockMode;
    private static string $storagePath;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->url = rtrim($config['supabase']['url'], '/');
        $this->anonKey = $config['supabase']['anon_key'];
        $this->serviceKey = $config['supabase']['service_key'];
        
        // Use Mock mode if keys are default or blank
        $this->isMockMode = empty($this->anonKey) || strpos($this->anonKey, 'your-supabase') !== false;
        
        self::$storagePath = __DIR__ . '/../../storage/mock_db.json';
        if ($this->isMockMode) {
            $this->initMockDb();
        }
    }

    public function isMock(): bool
    {
        return $this->isMockMode;
    }

    /**
     * Authenticate user with email and password
     */
    public function signIn(string $email, string $password): array
    {
        if ($this->isMockMode) {
            $db = $this->getMockDb();
            foreach ($db['users'] as $user) {
                if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password_hash'])) {
                    $profile = $this->getProfile($user['id']);
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'role' => $profile['role'] ?? 'customer',
                            'full_name' => $profile['full_name'] ?? 'User',
                            'phone' => $profile['phone'] ?? '',
                            'account_type' => $profile['account_type'] ?? 'residential',
                        ]
                    ];
                }
            }
            return ['success' => false, 'error' => 'Email or password is incorrect.'];
        }

        // Live Supabase Auth Endpoint
        $ch = curl_init("{$this->url}/auth/v1/token?grant_type=password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode === 200 && isset($data['access_token'])) {
            $user = $data['user'];
            $profile = $this->getProfile($user['id']);
            return [
                'success' => true,
                'token' => $data['access_token'],
                'user' => array_merge($user, [
                    'role' => $profile['role'] ?? 'customer',
                    'full_name' => $profile['full_name'] ?? '',
                    'phone' => $profile['phone'] ?? '',
                    'account_type' => $profile['account_type'] ?? 'residential'
                ])
            ];
        }

        return ['success' => false, 'error' => $data['error_description'] ?? 'Invalid credentials.'];
    }

    /**
     * Register new customer account
     */
    public function signUp(string $fullName, string $phone, string $email, string $password, string $accountType = 'residential'): array
    {
        if ($this->isMockMode) {
            $db = $this->getMockDb();
            foreach ($db['users'] as $user) {
                if (strtolower($user['email']) === strtolower($email)) {
                    return ['success' => false, 'error' => 'An account with this email already exists.'];
                }
            }

            $userId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            
            $db['users'][] = [
                'id' => $userId,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ];

            $db['profiles'][] = [
                'id' => $userId,
                'full_name' => $fullName,
                'phone' => $phone,
                'role' => 'customer',
                'account_type' => $accountType,
                'created_at' => date('c')
            ];

            $this->saveMockDb($db);

            return [
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'role' => 'customer',
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'account_type' => $accountType,
                ]
            ];
        }

        // Live Supabase Auth Sign Up
        $ch = curl_init("{$this->url}/auth/v1/signup");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password,
            'data' => [
                'full_name' => $fullName,
                'phone' => $phone,
                'account_type' => $accountType
            ]
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode === 200 && isset($data['id'])) {
            // Create profile entry
            $this->insert('profiles', [
                'id' => $data['id'],
                'full_name' => $fullName,
                'phone' => $phone,
                'role' => 'customer',
                'account_type' => $accountType
            ]);

            return [
                'success' => true,
                'user' => [
                    'id' => $data['id'],
                    'email' => $email,
                    'role' => 'customer',
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'account_type' => $accountType
                ]
            ];
        }

        return ['success' => false, 'error' => $data['msg'] ?? 'Signup failed.'];
    }

    public function getProfile(string $userId): array
    {
        $profiles = $this->select('profiles', ['id' => "eq.{$userId}"]);
        return $profiles[0] ?? ['role' => 'customer', 'full_name' => '', 'phone' => '', 'account_type' => 'residential'];
    }

    public function getCurrentPrice(): array
    {
        $prices = $this->select('price_config', [], 'effective_from.desc', 1);
        if (!empty($prices)) {
            return $prices[0];
        }
        return ['price_per_kg' => 32.50, 'currency' => 'ZAR', 'effective_from' => date('c')];
    }

    public function updatePrice(float $pricePerKg, string $adminId, ?string $notes = null): array
    {
        return $this->insert('price_config', [
            'id' => $this->uuid(),
            'price_per_kg' => $pricePerKg,
            'currency' => 'ZAR',
            'effective_from' => date('c'),
            'adjusted_by' => $adminId,
            'notes' => $notes ?: 'Updated by admin'
        ]);
    }

    public function select(string $table, array $filters = [], string $order = '', int $limit = 100): array
    {
        if ($this->isMockMode) {
            $db = $this->getMockDb();
            $items = $db[$table] ?? [];

            // Simple filtering
            if (!empty($filters)) {
                $items = array_filter($items, function($row) use ($filters) {
                    foreach ($filters as $key => $val) {
                        if (str_contains($val, 'eq.')) {
                            $target = str_replace('eq.', '', $val);
                            if (($row[$key] ?? null) != $target) return false;
                        }
                    }
                    return true;
                });
            }

            // Simple ordering
            if ($order === 'effective_from.desc' || $order === 'created_at.desc') {
                usort($items, function($a, $b) {
                    return strtotime($b['created_at'] ?? $b['effective_from'] ?? 'now') <=> strtotime($a['created_at'] ?? $a['effective_from'] ?? 'now');
                });
            }

            return array_slice(array_values($items), 0, $limit);
        }

        // Live Supabase PostgREST select
        $query = [];
        foreach ($filters as $k => $v) {
            $query[] = "{$k}={$v}";
        }
        if (!empty($order)) $query[] = "order={$order}";
        if ($limit > 0) $query[] = "limit={$limit}";

        $url = "{$this->url}/rest/v1/{$table}?" . implode('&', $query);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer " . ($this->serviceKey ?: $this->anonKey),
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }

    public function insert(string $table, array $data): array
    {
        if ($this->isMockMode) {
            $db = $this->getMockDb();
            if (!isset($data['id'])) {
                $data['id'] = $this->uuid();
            }
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('c');
            }
            $db[$table][] = $data;
            $this->saveMockDb($db);
            return $data;
        }

        $ch = curl_init("{$this->url}/rest/v1/{$table}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer " . ($this->serviceKey ?: $this->anonKey),
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);
        return is_array($res) ? ($res[0] ?? $res) : [];
    }

    public function update(string $table, string $id, array $data): array
    {
        if ($this->isMockMode) {
            $db = $this->getMockDb();
            foreach ($db[$table] as &$row) {
                if ($row['id'] === $id) {
                    $data['updated_at'] = date('c');
                    $row = array_merge($row, $data);
                    $this->saveMockDb($db);
                    return $row;
                }
            }
            return [];
        }

        $ch = curl_init("{$this->url}/rest/v1/{$table}?id=eq.{$id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer " . ($this->serviceKey ?: $this->anonKey),
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);
        return is_array($res) ? ($res[0] ?? $res) : [];
    }

    // Mock DB Storage initializer
    private function initMockDb(): void
    {
        $dir = dirname(self::$storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists(self::$storagePath)) {
            $adminId = $this->uuid();
            $driverId = $this->uuid();
            $customerId = $this->uuid();

            $initial = [
                'users' => [
                    [
                        'id' => $adminId,
                        'email' => 'admin@musinagas.co.za',
                        'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                    ],
                    [
                        'id' => $driverId,
                        'email' => 'driver@musinagas.co.za',
                        'password_hash' => password_hash('driver123', PASSWORD_BCRYPT),
                    ],
                    [
                        'id' => $customerId,
                        'email' => 'client@musinagas.co.za',
                        'password_hash' => password_hash('client123', PASSWORD_BCRYPT),
                    ]
                ],
                'profiles' => [
                    [
                        'id' => $adminId,
                        'full_name' => 'System Admin',
                        'phone' => '+27 82 123 4567',
                        'role' => 'admin',
                        'account_type' => 'commercial',
                        'created_at' => date('c')
                    ],
                    [
                        'id' => $driverId,
                        'full_name' => 'Sipho Driver',
                        'phone' => '+27 71 987 6543',
                        'role' => 'driver',
                        'account_type' => 'residential',
                        'created_at' => date('c')
                    ],
                    [
                        'id' => $customerId,
                        'full_name' => 'John Customer',
                        'phone' => '+27 83 555 1234',
                        'role' => 'customer',
                        'account_type' => 'residential',
                        'created_at' => date('c')
                    ]
                ],
                'price_config' => [
                    [
                        'id' => $this->uuid(),
                        'price_per_kg' => 32.50,
                        'currency' => 'ZAR',
                        'effective_from' => date('c'),
                        'notes' => 'Opening price for Phase 1'
                    ]
                ],
                'delivery_zones' => [
                    [
                        'id' => 'zone-a',
                        'zone_name' => 'Zone A (Central Musina / CBD)',
                        'delivery_fee' => 30.00,
                        'is_active' => true,
                    ],
                    [
                        'id' => 'zone-b',
                        'zone_name' => 'Zone B (Nancefield & Suburbs)',
                        'delivery_fee' => 50.00,
                        'is_active' => true,
                    ],
                    [
                        'id' => 'zone-c',
                        'zone_name' => 'Zone C (Outskirts / Border)',
                        'delivery_fee' => 80.00,
                        'is_active' => true,
                    ]
                ],
                'delivery_locations' => [
                    [
                        'id' => 'loc-1',
                        'customer_id' => $customerId,
                        'label' => 'Home',
                        'latitude' => -22.3562,
                        'longitude' => 30.0416,
                        'digital_address' => '12 Vhembe Road, Musina CBD',
                        'access_notes' => 'Blue gate, ring bell twice',
                        'zone_id' => 'zone-a',
                        'is_default' => true,
                        'created_at' => date('c')
                    ]
                ],
                'gas_orders' => [
                    [
                        'id' => 'ord-101',
                        'order_ref' => 'MG-00123',
                        'customer_id' => $customerId,
                        'customer_name' => 'John Customer',
                        'customer_phone' => '+27 83 555 1234',
                        'location_id' => 'loc-1',
                        'digital_address' => '12 Vhembe Road, Musina CBD',
                        'cylinder_size_kg' => 9.0,
                        'quantity' => 1,
                        'price_per_kg' => 32.50,
                        'delivery_fee' => 30.00,
                        'total_amount' => 322.50,
                        'payment_method' => 'EFT',
                        'payment_status' => 'eft_submitted',
                        'eft_proof_url' => '/uploads/eft_sample.png',
                        'assigned_driver' => null,
                        'created_at' => date('c'),
                        'updated_at' => date('c')
                    ]
                ],
                'order_status_log' => [],
                'inventory' => [
                    ['id' => 'inv-9', 'cylinder_size' => 9.0, 'stock_count' => 150],
                    ['id' => 'inv-19', 'cylinder_size' => 19.0, 'stock_count' => 85],
                    ['id' => 'inv-48', 'cylinder_size' => 48.0, 'stock_count' => 40],
                ],
                'bank_config' => [
                    [
                        'id' => 'bank-1',
                        'bank_name' => 'First National Bank (FNB)',
                        'account_name' => 'Musina Gas Pty Ltd',
                        'account_no' => '62891048291',
                        'branch_code' => '250655',
                        'is_active' => true
                    ]
                ]
            ];
            file_put_contents(self::$storagePath, json_encode($initial, JSON_PRETTY_PRINT));
        }
    }

    private function getMockDb(): array
    {
        return json_decode(file_get_contents(self::$storagePath), true) ?: [];
    }

    private function saveMockDb(array $db): void
    {
        file_put_contents(self::$storagePath, json_encode($db, JSON_PRETTY_PRINT));
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
}
