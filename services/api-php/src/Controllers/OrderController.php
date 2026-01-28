<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Utils\Auth;
use App\Utils\Db;
use App\Utils\Request;

class OrderController
{
    /**
     * 创建订单
     */
    public function store(): array
    {
        $userId = Auth::userId();
        if (!$userId) {
            return ['code' => 10002, 'msg' => '未登录', 'data' => null];
        }
        $data = Request::json();
        $items = $data['items'] ?? [];
        $fulfillmentType = $data['fulfillment_type'] ?? 'SELF';
        if (!$items) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $service = new OrderService();
        try {
            $address = ['address_id' => $data['address_id'] ?? 0, 'detail' => 'mock address'];
            $order = $service->create($userId, $items, $address, $fulfillmentType);
            return ['code' => 0, 'msg' => 'ok', 'data' => $order];
        } catch (\Throwable $e) {
            return ['code' => 20001, 'msg' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 订单列表
     */
    public function index(): array
    {
        $userId = Auth::userId();
        if (!$userId) {
            return ['code' => 10002, 'msg' => '未登录', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT order_no, total_amount, order_status, pay_status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();
        return ['code' => 0, 'msg' => 'ok', 'data' => $orders];
    }

    /**
     * 订单详情
     */
    public function show(string $orderNo): array
    {
        $userId = Auth::userId();
        if (!$userId) {
            return ['code' => 10002, 'msg' => '未登录', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_no = ? AND user_id = ?');
        $stmt->execute([$orderNo, $userId]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['code' => 404, 'msg' => '订单不存在', 'data' => null];
        }
        $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$order['id']]);
        $order['items'] = $itemsStmt->fetchAll();
        return ['code' => 0, 'msg' => 'ok', 'data' => $order];
    }
}
