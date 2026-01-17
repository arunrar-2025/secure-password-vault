<?php
    // backend/public/index.php

    declare(strict_types=1);

    header("Content-Type: application/json");

    session_start();

    // Load required files
    require_once __DIR__ . '/../app/config/env.php';
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/config/security.php';
    require_once __DIR__ . '/../app/models/User.php';
    require_once __DIR__ . '/../app/core/JWT.php';

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
        
        default:
            jsonResponse([
                "status" => "error",
                "message" => "Route not found"
            ], 404);
    }