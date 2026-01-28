<?php

namespace App\Controllers;

use App\Utils\Db;
use App\Utils\Request;
use App\Utils\Jwt;

class AdminController
{
    /**
     * 管理员登录
     */
    public function login(): array
    {
        $data = Request::json();
        $account = $data['account'] ?? '';
        $password = $data['password'] ?? '';
        if (!$account || !$password) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE account = ? AND status = 1');
        $stmt->execute([$account]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return ['code' => 10002, 'msg' => '账号或密码错误', 'data' => null];
        }
        $token = Jwt::encode(['uid' => (int)$admin['id'], 'role' => 'admin']);
        return ['code' => 0, 'msg' => 'ok', 'data' => ['token' => $token]];
    }
}
