<?php
    // backend/app/core/JWT.php

    class JWT
    {
        private static string $algo = 'HS256';

        private static function base64UrlEncode(string $data): string
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        private static function base64UrlDecode(string $data): string
        {
            return base64_decode(strtr($data, '-_', '+/'));
        }

        public static function encode(array $payload, string $secret): string
        {
            $header = ['typ' => 'JWT', 'alg' => self::$algo];

            $segments = [];
            $segments[] = self::base64UrlEncode(json_encode($header));
            $segments[] = self::base64UrlEncode(json_encode($payload));

            $signingInput = implode('.', $segments);
            $signature = hash_hmac('sha256', $signingInput, $secret, true);
            $segments[] = self::base64UrlEncode($signature);

            return implode('.', $segments);
        }

        public static function decode(string $jwt, string $secret): ?array
        {
            $segments = explode('.', $jwt);
            if (count($segments) !== 3) return null;

            [$headerB64, $payloadB64, $sigB64] = $segments;

            $signatureCheck = self::base64UrlEncode(hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true));

            if (!hash_equals($signatureCheck, $sigB64)) {
                return null;
            }

            $payload = json_decode(self::base64UrlDecode($payloadB64), true);

            // Expiry check
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            return $payload;
        }
    }