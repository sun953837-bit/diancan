<?php

namespace App\Utils;

class Jwt
{
    /**
     * 生成 JWT（HS256）
     *
     * @param array $payload 业务载荷
     * @param int $ttl 有效期秒数
     */
    public static function encode(array $payload, int $ttl = 604800): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['exp'] = time() + $ttl;
        $segments = [self::base64UrlEncode(json_encode($header)), self::base64UrlEncode(json_encode($payload))];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $_ENV['JWT_SECRET'] ?? 'secret', true);
        $segments[] = self::base64UrlEncode($signature);
        return implode('.', $segments);
    }

    /**
     * 解码 JWT（返回 payload 或 null）
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header64, $payload64, $signature64] = $parts;
        $signingInput = $header64 . '.' . $payload64;
        $signature = self::base64UrlDecode($signature64);
        $expected = hash_hmac('sha256', $signingInput, $_ENV['JWT_SECRET'] ?? 'secret', true);
        if (!hash_equals($expected, $signature)) {
            return null;
        }
        $payload = json_decode(self::base64UrlDecode($payload64), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) {
            return null;
        }
        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
