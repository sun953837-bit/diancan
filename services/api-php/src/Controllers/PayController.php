<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Utils\Auth;
use App\Utils\Db;
use App\Utils\Request;

class PayController
{
    /**
     * 微信支付下单（Mock/真实占位）
     */
    public function prepay(): array
    {
        $userId = Auth::userId();
        if (!$userId) {
            return ['code' => 10002, 'msg' => '未登录', 'data' => null];
        }
        $data = Request::json();
        $orderNo = $data['order_no'] ?? '';
        if (!$orderNo) {
            return ['code' => 10001, 'msg' => '参数错误', 'data' => null];
        }
        $mode = $_ENV['WECHAT_PAY_MODE'] ?? 'mock';
        if ($mode === 'mock') {
            // Mock 模式直接将订单置为已支付
            $service = new OrderService();
            $service->updateStatus($orderNo, OrderService::STATUS_PAID, 'mock payment');
            $pdo = Db::conn();
            $stmt = $pdo->prepare('UPDATE orders SET pay_status = ? WHERE order_no = ?');
            $stmt->execute([OrderService::PAY_PAID, $orderNo]);
            $event = $pdo->prepare('INSERT INTO event_log (event_type, ref_type, ref_id, user_id, payload, occurred_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $event->execute(['PAY_SUCCESS', 'ORDER', $orderNo, $userId, json_encode(['mode' => 'mock'], JSON_UNESCAPED_UNICODE)]);
            return ['code' => 0, 'msg' => 'ok', 'data' => ['mock' => true]];
        }
        // 真实微信支付下单参数占位
        return ['code' => 0, 'msg' => 'ok', 'data' => [
            'timeStamp' => (string)time(),
            'nonceStr' => uniqid(),
            'package' => 'prepay_id=replace-with-real',
            'signType' => 'RSA',
            'paySign' => 'replace-with-real-sign',
        ]];
    }

    /**
     * 微信支付回调（验签/金额校验/幂等处理）
     */
    public function notify(): array
    {
        // TODO: 实际验签需读取平台证书并校验报文签名
        $data = Request::json();
        $orderNo = $data['out_trade_no'] ?? '';
        $amount = $data['amount']['total'] ?? 0;

        if (!$orderNo) {
            return ['code' => 30002, 'msg' => '回调验签失败', 'data' => null];
        }

        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT pay_amount, pay_status FROM orders WHERE order_no = ?');
        $stmt->execute([$orderNo]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['code' => 404, 'msg' => '订单不存在', 'data' => null];
        }
        if ((float)$order['pay_amount'] != (float)$amount) {
            return ['code' => 30001, 'msg' => '支付金额不一致', 'data' => null];
        }
        if ($order['pay_status'] === OrderService::PAY_PAID) {
            // 幂等：已支付直接返回
            return ['code' => 0, 'msg' => 'ok', 'data' => ['idempotent' => true]];
        }
        $service = new OrderService();
        $service->updateStatus($orderNo, OrderService::STATUS_PAID, 'wechat notify');
        $stmt = $pdo->prepare('UPDATE orders SET pay_status = ? WHERE order_no = ?');
        $stmt->execute([OrderService::PAY_PAID, $orderNo]);
        $pay = $pdo->prepare('INSERT INTO payments (order_id, wx_transaction_id, out_trade_no, status, paid_at, raw_notify) VALUES ((SELECT id FROM orders WHERE order_no = ?), ?, ?, ?, NOW(), ?)');
        $pay->execute([$orderNo, $data['transaction_id'] ?? '', $orderNo, 'PAID', json_encode($data, JSON_UNESCAPED_UNICODE)]);

        $event = $pdo->prepare('INSERT INTO event_log (event_type, ref_type, ref_id, user_id, payload, occurred_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $event->execute(['PAY_SUCCESS', 'ORDER', $orderNo, 0, json_encode(['mode' => 'wechat'], JSON_UNESCAPED_UNICODE)]);

        return ['code' => 0, 'msg' => 'ok', 'data' => ['status' => 'PAID']];
    }
}
