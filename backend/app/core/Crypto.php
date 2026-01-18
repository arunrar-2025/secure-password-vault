<?php
    // backend/app/core/Crypto.php

    class Crypto
    {
        private string $cipher = 'aes-256-cbc';

        // Derive encryption key from master password using PBKDF2
        public function deriveKey(string $masterPassword, string $salt): string
        {
            return hash_pbkdf2('sha256', $masterPassword, $salt, 100000, 32, true);
        }

        public function encrypt(string $plainText, string $key): string
        {
            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = random_bytes($ivLength);

            $cipherText = openssl_encrypt($plainText, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($cipherText === false) {
                throw new RuntimeException("Encryption failed");
            }

            return base64_encode($iv . $cipherText);
        }

        public function decrypt(string $encryptedData, string $key): string
        {
            $data = base64_decode($encryptedData);

            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $ivLength);
            $cipherText = substr($data, $ivLength);

            $plainText = openssl_decrypt($cipherText, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($plainText === false) {
                throw new RuntimeException("Decryption failed");
            }

            return $plainText;
        }
    }
