<?php
    // backend/app/models/Vault.php

    require_once __DIR__ . '/../config/database.php';

    class Vault
    {
        private PDO $pdo;

        public  function __construct()
        {
            $this->pdo = getPDO();
        }

        public function create(int $userId, string $title, string $usernameEnc, string $passwordEnc, ?string $urlEnc, ?string $notesEnc): bool
        {
            $stmt = $this->pdo->prepare("INSERT INTO vault_entries (user_id, title, username_encrypted, password_encrypted, url_encrypted, notes_encrypted) VALUES (:user_id, :title, :username, :password, :url, :notes)");

            return $stmt->execute([
                ':user_id' => $userId,
                ':title' => $title,
                ':username' => $usernameEnc,
                ':password' => $passwordEnc,
                ':url' => $urlEnc,
                ':notes' => $notesEnc
            ]);
        }

        public function findByUser(int $userId): array
        {
            $stmt = $this->pdo->prepare("SELECT * FROM vault_entries WHERE user_id = :uid ORDER BY created_at DESC");
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll();
        }

        public function findById(int $id, int $userId): ?array
        {
            $stmt = $this->pdo->prepare("SELECT * FROM vault_entries WHERE id = :id AND user_id = :uid LIMIT 1");
            $stmt->execute([':id' => $id, ':uid' => $userId]);
            $row = $stmt->fetch();
            return $row ?: null;
        }

        public function delete(int $id, int $userId): bool
        {
            $stmt = $this->pdo->prepare("DELETE FROM vault_entries WHERE id = :id AND user_id = :uid");
            return $stmt->execute([':id' => $id, ':uid' => $userId]);
        }
    }