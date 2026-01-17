<?php
    // backend/public/index.php

    declare(strict_types=1);

    header("Content-Type: application/json");

    session_start();

    // Load configs
    require_once __DIR__ . '/../app/config/env.php';
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/config/security.php';

    // JSON response helper
    function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
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
        
        default:
            jsonResponse([
                "status" => "error",
                "message" => "Route not found"
            ], 404);
    }