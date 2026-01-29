<?php

namespace App\Utils;

use App\Utils\Jwt;

class Auth
{
    /**
     * 校验用户 JWT，返回用户 ID
     */
    public static function userId(): ?int
    {
        $token = Request::bearerToken();
        if (!$token) {
            return null;
        }
        $payload = Jwt::decode($token);
        if (!$payload) {
            return null;
        }
        return (int)($payload['uid'] ?? 0);
    }
}
