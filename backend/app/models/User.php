<?php
    // backend/app/models/User.php

    require_once __DIR__ . '/../config/database.php';

    class User
    {
        private PDO $pdo;

        public function __construct()
        {
            $this->pdo = getPDO();
        }

        public function findByEmail(string $email): ?array
        {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        }

        public function findById(int $id): ?array
        {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        }

        public function create(string $email, string $passwordHash): bool
        {
            $stmt = $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)");
            return $stmt->execute([
                ':email' => $email,
                ':password_hash' => $passwordHash
            ]);
        }
    }