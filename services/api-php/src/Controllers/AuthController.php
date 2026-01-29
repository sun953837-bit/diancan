<?php

namespace App\Controllers;

use App\Utils\Db;
use App\Utils\Request;
use App\Utils\Jwt;

class AuthController
{
    /**
     * 小程序登录：code 换 openid + JWT
     *
     * @return array
     */
    public function wxLogin(): array
    {
        $data = Request::json();
        $code = $data['code'] ?? '';
        if (!$code) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        // MVP: mock 模式直接生成 openid
        $openid = 'openid_' . md5($code);
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE openid = ?');
        $stmt->execute([$openid]);
        $user = $stmt->fetch();
        if (!$user) {
            $insert = $pdo->prepare('INSERT INTO users (openid, nickname, avatar, created_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$openid, '新用户', '',]);
            $userId = (int)$pdo->lastInsertId();
        } else {
            $userId = (int)$user['id'];
        }
        $token = Jwt::encode(['uid' => $userId]);
        return ['code' => 0, 'msg' => 'ok', 'data' => ['token' => $token, 'openid' => $openid]];
    }
}
