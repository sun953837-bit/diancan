<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Db;
use App\Utils\Request;

class ReviewController
{
    /**
     * 创建评价（仅已完成订单）
     */
    public function store(): array
    {
        $userId = Auth::userId();
        if (!$userId) {
            return ['code' => 10002, 'msg' => '未登录', 'data' => null];
        }
        $data = Request::json();
        $orderNo = $data['order_no'] ?? '';
        $rating = (int)($data['rating'] ?? 5);
        if (!$orderNo) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('INSERT INTO reviews (order_id, user_id, rating, content, images, reply, is_hidden) VALUES ((SELECT id FROM orders WHERE order_no = ?), ?, ?, ?, ?, NULL, 0)');
        $stmt->execute([$orderNo, $userId, $rating, $data['content'] ?? '', json_encode($data['images'] ?? [], JSON_UNESCAPED_UNICODE)]);

        $event = $pdo->prepare('INSERT INTO event_log (event_type, ref_type, ref_id, user_id, payload, occurred_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $event->execute(['REVIEW_CREATED', 'ORDER', $orderNo, $userId, json_encode(['rating' => $rating], JSON_UNESCAPED_UNICODE)]);

        return ['code' => 0, 'msg' => 'ok', 'data' => ['order_no' => $orderNo]];
    }
}
