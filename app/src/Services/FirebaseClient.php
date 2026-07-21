<?php

namespace App\Services;

class FirebaseClient
{
    private string $apiKey;
    private string $projectId;
    private string $firestoreUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->apiKey = $config['firebase']['api_key'];
        $this->projectId = $config['firebase']['project_id'];
        $this->firestoreUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /**
     * Firebase Authentication - Sign In with Email & Password
     */
    public function signIn(string $email, string $password): array
    {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$this->apiKey}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['idToken'])) {
            return [
                'success' => true,
                'idToken' => $data['idToken'],
                'localId' => $data['localId'],
                'email' => $data['email'],
            ];
        }

        return [
            'success' => false,
            'error' => $data['error']['message'] ?? 'Firebase authentication failed.'
        ];
    }

    /**
     * Firebase Authentication - Sign Up with Email & Password
     */
    public function signUp(string $email, string $password): array
    {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key={$this->apiKey}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['idToken'])) {
            return [
                'success' => true,
                'idToken' => $data['idToken'],
                'localId' => $data['localId'],
                'email' => $data['email'],
            ];
        }

        return [
            'success' => false,
            'error' => $data['error']['message'] ?? 'Firebase sign up failed.'
        ];
    }

    /**
     * Firestore - Add or update a document in a collection
     */
    public function setDocument(string $collection, string $documentId, array $fields): array
    {
        $url = "{$this->firestoreUrl}/{$collection}/{$documentId}";

        $firestoreFields = $this->encodeFirestoreFields($fields);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'fields' => $firestoreFields
        ]));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }

    /**
     * Firestore - Fetch document by ID
     */
    public function getDocument(string $collection, string $documentId): ?array
    {
        $url = "{$this->firestoreUrl}/{$collection}/{$documentId}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $this->decodeFirestoreFields($data['fields'] ?? []);
        }

        return null;
    }

    /**
     * Firestore - Fetch all documents in a collection
     */
    public function getCollection(string $collection): array
    {
        $url = "{$this->firestoreUrl}/{$collection}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $documents = [];
            foreach ($data['documents'] ?? [] as $doc) {
                $parts = explode('/', $doc['name']);
                $id = end($parts);
                $fields = $this->decodeFirestoreFields($doc['fields'] ?? []);
                $fields['id'] = $id;
                $documents[] = $fields;
            }
            return $documents;
        }

        return [];
    }

    private function encodeFirestoreFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $val) {
            if (is_int($val)) {
                $fields[$key] = ['integerValue' => (string)$val];
            } elseif (is_float($val) || is_double($val)) {
                $fields[$key] = ['doubleValue' => (float)$val];
            } elseif (is_bool($val)) {
                $fields[$key] = ['booleanValue' => $val];
            } else {
                $fields[$key] = ['stringValue' => (string)$val];
            }
        }
        return $fields;
    }

    private function decodeFirestoreFields(array $fields): array
    {
        $decoded = [];
        foreach ($fields as $key => $valObj) {
            if (isset($valObj['stringValue'])) {
                $decoded[$key] = $valObj['stringValue'];
            } elseif (isset($valObj['integerValue'])) {
                $decoded[$key] = (int)$valObj['integerValue'];
            } elseif (isset($valObj['doubleValue'])) {
                $decoded[$key] = (float)$valObj['doubleValue'];
            } elseif (isset($valObj['booleanValue'])) {
                $decoded[$key] = (bool)$valObj['booleanValue'];
            }
        }
        return $decoded;
    }
}
