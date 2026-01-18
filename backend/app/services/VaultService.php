<?php
    // backend/app/services/VaultService.php

    require_once __DIR__ . '/../models/Vault.php';
    require_once __DIR__ . '/../core/Crypto.php';

    class VaultService
    {
        private Vault $vaultModel;
        private Crypto $crypto;

        public function __construct()
        {
            $this->vaultModel = new Vault();
            $this->crypto = new Crypto();
        }

        public function addEntry(int $userId, string $masterPassword, string $title, string $username, string $password, ?string $url, ?string $notes): bool
        {
            // Derive key (salt will later be stored per user, for now userId is used)
            $salt = "user-salt-" . $userId;
            $key = $this->crypto->deriveKey($masterPassword, $salt);

            // Encrypt fields
            $usernameEnc = $this->crypto->encrypt($username, $key);
            $passwordEnc = $this->crypto->encrypt($password, $key);
            $urlEnc = $url ? $this->crypto->encrypt($url, $key) : null;
            $notesEnc = $notes ? $this->crypto->encrypt($notes, $key) : null;

            return $this->vaultModel->create($userId, $title, $usernameEnc, $passwordEnc, $urlEnc, $notesEnc);
        }

        public function listEntries(int $userId): array
        {
            return $this->vaultModel->findByUser($userId);
        }

        public function decryptEntry(array $entry, string $masterPassword): array
        {
            $salt = "user-salt-" . $entry['user_id'];
            $key = $this->crypto->deriveKey($masterPassword, $salt);

            return [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'username' => $this->crypto->decrypt($entry['username_encrypted'], $key),
                'password' => $this->crypto->decrypt($entry['password_encrypted'], $key),
                'url' => $entry['url_encrypted'] ? $this->crypto->decrypt($entry['url_encrypted'], $key) : null,
                'notes' => $entry['notes_encrypted'] ? $this->crypto->decrypt($entry['notes_encrypted'], $key) : null
            ];
        }

        public function deleteEntry(int $entryId, int $userId): bool
        {
            return $this->vaultModel->delete($entryId, $userId);
        }
    }