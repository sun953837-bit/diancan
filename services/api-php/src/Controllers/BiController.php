<?php

namespace App\Controllers;

use App\Utils\Db;

class BiController
{
    /**
     * BI 订单增量导出
     */
    public function orders(): array
    {
        $from = $_GET['from'] ?? '1970-01-01';
        $to = $_GET['to'] ?? date('Y-m-d');
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT order_no, user_id, total_amount, order_status, created_at FROM orders WHERE created_at BETWEEN ? AND ?');
        $stmt->execute([$from, $to]);
        return ['code' => 0, 'msg' => 'ok', 'data' => $stmt->fetchAll()];
    }

    /**
     * BI 事件增量导出
     */
    public function events(): array
    {
        $from = $_GET['from'] ?? '1970-01-01';
        $to = $_GET['to'] ?? date('Y-m-d');
        $type = $_GET['type'] ?? '';
        $pdo = Db::conn();
        if ($type) {
            $stmt = $pdo->prepare('SELECT * FROM event_log WHERE event_type = ? AND occurred_at BETWEEN ? AND ?');
            $stmt->execute([$type, $from, $to]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM event_log WHERE occurred_at BETWEEN ? AND ?');
            $stmt->execute([$from, $to]);
        }
        return ['code' => 0, 'msg' => 'ok', 'data' => $stmt->fetchAll()];
    }
}
