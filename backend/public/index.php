<?php
    // backend/public/index.php

    declare(strict_types=1);

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    session_start();

    // Load required files
    require_once __DIR__ . '/../app/config/env.php';
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/config/security.php';
    require_once __DIR__ . '/../app/models/User.php';
    require_once __DIR__ . '/../app/core/JWT.php';
    require_once __DIR__ . '/../app/core/Crypto.php';
    require_once __DIR__ . '/../app/services/VaultService.php';

    define('JWT_SECRET', getAppKey());

    // JSON response helper
    function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    function requireAuth(): array
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            jsonResponse(["error" => "Missing token"], 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $payload = JWT::decode($token, JWT_SECRET);

        if (!$payload) {
            jsonResponse(["error" => "Invalid or expired token"], 401);
        }

        return $payload; // contains uid + email
    }

    // Route handling
    $route = $_GET['route'] ?? 'home';

    switch ($route) {
        case 'home':
            jsonResponse([
                "status" => "ok",
                "message" => "Password Vault API running"
            ]);
            break;

        case 'register':
            $data = json_decode(file_get_contents("php://input"), true);

            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if (!$email || !$password) {
                jsonResponse(["error" => "Email and password required"], 400);
            }

            $userModel = new User();

            if ($userModel->findByEmail($email)) {
                jsonResponse(["error" => "Email already exists"], 409);
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userModel->create($email, $hash);

            jsonResponse(["message" => "User registered"]);
            break;

        case 'login':
            $data = json_decode(file_get_contents("php://input"), true);

            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                jsonResponse(["error" => "Invalid credentials"], 401);
            }

            $payload = [
                "uid" => $user['id'],
                "email" => $user['email'],
                "exp" => time() + 3600 // 1 hour expiry
            ];

            $token = JWT::encode($payload, JWT_SECRET);

            jsonResponse(["token" => $token]);
            break;

        case 'crypto_test':
            $data = json_decode(file_get_contents("php://input"), true);

            $master = $data['master'] ?? '';
            if (!$master) jsonResponse(["error" => "master password required"], 400);

            $salt = "static-test-salt";

            $crypto = new Crypto();
            $key = $crypto->deriveKey($master, $salt);

            $encrypted = $crypto->encrypt("secret-data", $key);
            $decrypted = $crypto->decrypt($encrypted, $key);

            jsonResponse([
                "encrypted" => $encrypted,
                "decrypted" => $decrypted
            ]);
            break;

        case 'vault_add':
            $payload = requireAuth(); // JWT verified
            $userId = $payload['uid'];

            $data = json_decode(file_get_contents("php://input"), true);

            $master = $data['master'] ?? '';
            $title = trim($data['title'] ?? '');
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $url = trim($data['url'] ?? '');
            $notes = trim($data['notes'] ?? '');

            if (!$master || !$title || !$username || !$password) {
                jsonResponse(["error" => "Missing required fields"], 400);
            }

            $vaultService = new VaultService();
            $vaultService->addEntry($userId, $master, $title, $username, $password, $url, $notes);

            jsonResponse(["message" => "Vault entry added"]);
            break;

        case 'vault_list':
            $payload = requireAuth();
            $userId = $payload['uid'];

            $vaultService = new VaultService();
            $entries = $vaultService->listEntries($userId);

            // Only send id + title (no encrypted data)
            $result = array_map(fn($e) => [
                "id" => $e['id'],
                "title" => $e['title']
            ], $entries);

            jsonResponse($result);
            break;

        case 'vault_get':
            $payload = requireAuth();
            $userId = $payload['uid'];

            $data = json_decode(file_get_contents("php://input"), true);
            $entryId = (int)($data['id'] ?? 0);
            $master = $data['master'] ?? '';

            if (!$entryId || !$master) {
                jsonResponse(["error" => "Missing entry id or master password"], 400);
            }

            $vaultService = new VaultService();
            $vaultModel = new Vault();
            $entryRaw = $vaultModel->findById($entryId, $userId);

            if (!$entryRaw) {
                jsonResponse(["error" => "Entry not found"], 404);
            }

            try {
                $decrypted = $vaultService->decryptEntry($entryRaw, $master);
                jsonResponse($decrypted);
            } catch (RuntimeException $e) {
                jsonResponse(["error" => "Invalid master password"], 401);
            }

            break;

        case 'vault_delete':
            $payload = requireAuth();
            $userId = $payload['uid'];

            $data = json_decode(file_get_contents("php://input"), true);
            $entryId = (int)($data['id'] ?? 0);

            if (!$entryId) {
                jsonResponse(["error" => "Missing entry id"], 400);
            }

            $vaultService = new VaultService();
            $vaultService->decryptEntry($entryId, $userId);

            jsonResponse(["message" => "Entry deleted"]);
            break;

        default:
            jsonResponse([
                "status" => "error",
                "message" => "Route not found"
            ], 404);
    }