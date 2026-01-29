<?php

namespace App\Controllers;

use App\Utils\Db;

class ProductController
{
    /**
     * 商品列表
     */
    public function index(): array
    {
        $pdo = Db::conn();
        $stmt = $pdo->query('SELECT id, name, cover, price FROM products ORDER BY id DESC');
        $products = $stmt->fetchAll();
        return ['code' => 0, 'msg' => 'ok', 'data' => $products];
    }

    /**
     * 商品详情（含 SKU）
     */
    public function show(string $id): array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id, name, cover, description FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) {
            return ['code' => 404, 'msg' => '商品不存在', 'data' => null];
        }
        $skuStmt = $pdo->prepare('SELECT id, sku_name, price, stock FROM skus WHERE product_id = ?');
        $skuStmt->execute([$id]);
        $skus = $skuStmt->fetchAll();
        $product['skus'] = $skus;
        return ['code' => 0, 'msg' => 'ok', 'data' => $product];
    }
}
