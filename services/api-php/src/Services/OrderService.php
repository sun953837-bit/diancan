<?php

namespace App\Services;

use App\Utils\Db;

class OrderService
{
    /**
     * 订单状态机常量（集中维护）
     * PENDING_PAYMENT -> PAID -> PACKING -> SHIPPED -> DELIVERED -> COMPLETED
     */
    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_PAID = 'PAID';
    public const STATUS_PACKING = 'PACKING';
    public const STATUS_SHIPPED = 'SHIPPED';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_REFUNDING = 'REFUNDING';
    public const STATUS_REFUNDED = 'REFUNDED';

    public const PAY_UNPAID = 'UNPAID';
    public const PAY_PAID = 'PAID';
    public const PAY_REFUNDED = 'REFUNDED';

    /**
     * 创建订单（锁库存并写库存流水）
     *
     * @param int $userId 用户 ID
     * @param array $items 订单商品项
     * @param array $address 地址快照
     * @param string $fulfillmentType 履约方式
     */
    public function create(int $userId, array $items, array $address, string $fulfillmentType): array
    {
        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            $orderNo = 'OD' . date('YmdHis') . rand(1000, 9999);
            $total = 0;
            foreach ($items as $item) {
                $stmt = $pdo->prepare('SELECT id, price, stock FROM skus WHERE id = ? FOR UPDATE');
                $stmt->execute([$item['sku_id']]);
                $sku = $stmt->fetch();
                if (!$sku || $sku['stock'] < $item['qty']) {
                    throw new \RuntimeException('库存不足');
                }
                $total += $sku['price'] * $item['qty'];
                $update = $pdo->prepare('UPDATE skus SET stock = stock - ? WHERE id = ?');
                $update->execute([$item['qty'], $item['sku_id']]);
                // 库存流水：RESERVE 表示预留库存
                $ledger = $pdo->prepare('INSERT INTO inventory_ledger (sku_id, change_type, qty, ref_type, ref_id, operator, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $ledger->execute([$item['sku_id'], 'RESERVE', $item['qty'], 'ORDER', $orderNo, 'system']);
            }

            $stmt = $pdo->prepare('INSERT INTO orders (order_no, user_id, total_amount, pay_amount, pay_status, order_status, fulfillment_type, address_snapshot, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$orderNo, $userId, $total, $total, self::PAY_UNPAID, self::STATUS_PENDING_PAYMENT, $fulfillmentType, json_encode($address, JSON_UNESCAPED_UNICODE)]);

            foreach ($items as $item) {
                $skuStmt = $pdo->prepare('SELECT price FROM skus WHERE id = ?');
                $skuStmt->execute([$item['sku_id']]);
                $sku = $skuStmt->fetch();
                $amount = $sku['price'] * $item['qty'];
                $stmt = $pdo->prepare('INSERT INTO order_items (order_id, sku_id, price, qty, amount) VALUES ((SELECT id FROM orders WHERE order_no = ?), ?, ?, ?, ?)');
                $stmt->execute([$orderNo, $item['sku_id'], $sku['price'], $item['qty'], $amount]);
            }

            $event = $pdo->prepare('INSERT INTO event_log (event_type, ref_type, ref_id, user_id, payload, occurred_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $event->execute(['ORDER_CREATED', 'ORDER', $orderNo, $userId, json_encode(['items' => $items], JSON_UNESCAPED_UNICODE)]);

            $pdo->commit();
            return ['order_no' => $orderNo, 'total_amount' => $total];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * 更新订单状态（集中入口，确保状态机一致）
     *
     * @param string $orderNo 订单号
     * @param string $status 目标状态
     * @param string $reason 变更原因（用于审计/追踪）
     */
    public function updateStatus(string $orderNo, string $status, string $reason): void
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('UPDATE orders SET order_status = ? WHERE order_no = ?');
        $stmt->execute([$status, $orderNo]);

        $audit = $pdo->prepare('INSERT INTO audit_log (actor_type, actor_id, action, target_type, target_id, diff, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $audit->execute(['system', 0, 'ORDER_STATUS_CHANGE', 'ORDER', $orderNo, json_encode(['status' => $status, 'reason' => $reason], JSON_UNESCAPED_UNICODE)]);
    }
}
