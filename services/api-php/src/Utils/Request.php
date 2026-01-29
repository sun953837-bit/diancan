<?php

namespace App\Utils;

class Request
{
    /**
     * 获取 JSON 请求体
     */
    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * 获取 Bearer Token
     */
    public static function bearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)/', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
