<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Utils\Db;
use App\Utils\Request;

class FulfillmentController
{
    /**
     * 自配送派单（后台操作）
     */
    public function assignSelf(): array
    {
        $data = Request::json();
        $orderNo = $data['order_no'] ?? '';
        $assignee = $data['assignee'] ?? 'rider-1';
        if (!$orderNo) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('INSERT INTO delivery_tasks (order_id, assignee, status, eta, finished_at, fail_reason) VALUES ((SELECT id FROM orders WHERE order_no = ?), ?, ?, NOW(), NULL, NULL)');
        $stmt->execute([$orderNo, $assignee, 'ASSIGNED']);

        $service = new OrderService();
        $service->updateStatus($orderNo, OrderService::STATUS_PACKING, 'assign self delivery');

        return ['code' => 0, 'msg' => 'ok', 'data' => ['order_no' => $orderNo, 'assignee' => $assignee]];
    }

    /**
     * 快递发货（绑定运单号）
     */
    public function shipCourier(): array
    {
        $data = Request::json();
        $orderNo = $data['order_no'] ?? '';
        $trackingNo = $data['tracking_no'] ?? '';
        if (!$orderNo || !$trackingNo) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $pdo = Db::conn();
        $stmt = $pdo->prepare('INSERT INTO fulfillments (order_id, type, status, shipping_fee, carrier_code, tracking_no, updated_at) VALUES ((SELECT id FROM orders WHERE order_no = ?), ?, ?, 0, ?, ?, NOW())');
        $stmt->execute([$orderNo, 'COURIER', 'SHIPPED', 'MOCK', $trackingNo]);

        $service = new OrderService();
        $service->updateStatus($orderNo, OrderService::STATUS_SHIPPED, 'courier shipped');

        return ['code' => 0, 'msg' => 'ok', 'data' => ['tracking_no' => $trackingNo]];
    }

    /**
     * 物流轨迹查询（Mock Provider）
     */
    public function trackCourier(): array
    {
        $trackingNo = $_GET['tracking_no'] ?? '';
        if (!$trackingNo) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        return ['code' => 0, 'msg' => 'ok', 'data' => [
            ['status' => 'IN_TRANSIT', 'location' => '上海分拨中心', 'occurred_at' => date('Y-m-d H:i:s')],
            ['status' => 'OUT_FOR_DELIVERY', 'location' => '上海徐汇', 'occurred_at' => date('Y-m-d H:i:s')],
        ]];
    }
}
