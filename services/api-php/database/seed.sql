INSERT INTO brands (name) VALUES ('进口美妆');
INSERT INTO categories (name) VALUES ('护肤');
INSERT INTO products (brand_id, category_id, name, cover, description, price) VALUES (1, 1, '法国面霜', 'https://example.com/cream.jpg', '进口面霜示例', 199.00);
INSERT INTO skus (product_id, sku_name, barcode, batch_no, expire_at, net_content, cost_price, price, promo_price, stock) VALUES (1, '标准装 50ml', '1234567890', 'BN001', '2026-12-31', '50ml', 80.00, 199.00, 169.00, 100);

-- 默认管理员
INSERT INTO admin_users (account, password_hash, status) VALUES (
  'admin',
  '$2y$12$EuFRBE0fKxVDUEsiBdUzfep62oD0Ig.WJJ92wX4iuQ5ly0cD.GghK',
  1
);
