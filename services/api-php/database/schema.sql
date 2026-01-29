-- 用户表
CREATE TABLE IF NOT EXISTS users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  openid VARCHAR(64) NOT NULL UNIQUE,
  unionid VARCHAR(64) NULL,
  nickname VARCHAR(50) NOT NULL,
  avatar VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_users_openid (openid)
);

-- 管理员表
CREATE TABLE IF NOT EXISTS admin_users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  account VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS role_permission (
  role_id BIGINT NOT NULL,
  permission_id BIGINT NOT NULL,
  PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS admin_user_role (
  admin_user_id BIGINT NOT NULL,
  role_id BIGINT NOT NULL,
  PRIMARY KEY (admin_user_id, role_id)
);

CREATE TABLE IF NOT EXISTS brands (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  brand_id BIGINT NULL,
  category_id BIGINT NULL,
  name VARCHAR(200) NOT NULL,
  cover VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS skus (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT NOT NULL,
  sku_name VARCHAR(200) NOT NULL,
  barcode VARCHAR(64) NULL,
  batch_no VARCHAR(64) NULL,
  expire_at DATE NULL,
  net_content VARCHAR(50) NULL,
  cost_price DECIMAL(10,2) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  promo_price DECIMAL(10,2) NULL,
  stock INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory_ledger (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sku_id BIGINT NOT NULL,
  change_type ENUM('IN','OUT','ADJUST','RESERVE','RELEASE') NOT NULL,
  qty INT NOT NULL,
  ref_type VARCHAR(50) NOT NULL,
  ref_id VARCHAR(50) NOT NULL,
  operator VARCHAR(50) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_inventory_ref (ref_type, ref_id)
);

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_no VARCHAR(50) NOT NULL UNIQUE,
  user_id BIGINT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  pay_amount DECIMAL(10,2) NOT NULL,
  pay_status ENUM('UNPAID','PAID','REFUNDED') NOT NULL,
  order_status VARCHAR(50) NOT NULL,
  fulfillment_type ENUM('SELF','COURIER') NOT NULL,
  address_snapshot JSON NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_status (order_status)
);

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  sku_id BIGINT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  INDEX idx_order_items_order (order_id)
);

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  wx_transaction_id VARCHAR(64) NOT NULL,
  out_trade_no VARCHAR(64) NOT NULL UNIQUE,
  status VARCHAR(20) NOT NULL,
  paid_at DATETIME NOT NULL,
  raw_notify JSON NOT NULL
);

CREATE TABLE IF NOT EXISTS refunds (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  refund_no VARCHAR(64) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS fulfillments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  type ENUM('SELF','COURIER') NOT NULL,
  status VARCHAR(20) NOT NULL,
  shipping_fee DECIMAL(10,2) NOT NULL,
  carrier_code VARCHAR(50) NOT NULL,
  tracking_no VARCHAR(64) NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_fulfillment_tracking (tracking_no)
);

CREATE TABLE IF NOT EXISTS delivery_tasks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  assignee VARCHAR(50) NOT NULL,
  status VARCHAR(20) NOT NULL,
  eta DATETIME NOT NULL,
  finished_at DATETIME NULL,
  fail_reason VARCHAR(200) NULL
);

CREATE TABLE IF NOT EXISTS tracking_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tracking_no VARCHAR(64) NOT NULL,
  status VARCHAR(20) NOT NULL,
  location VARCHAR(100) NOT NULL,
  occurred_at DATETIME NOT NULL,
  raw JSON NOT NULL,
  INDEX idx_tracking_no (tracking_no)
);

CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  rating TINYINT NOT NULL,
  content TEXT NOT NULL,
  images JSON NOT NULL,
  reply TEXT NULL,
  is_hidden TINYINT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS event_log (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  event_type VARCHAR(50) NOT NULL,
  ref_type VARCHAR(50) NOT NULL,
  ref_id VARCHAR(50) NOT NULL,
  user_id BIGINT NOT NULL,
  payload JSON NOT NULL,
  occurred_at DATETIME NOT NULL,
  INDEX idx_event_type_time (event_type, occurred_at)
);

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  actor_type VARCHAR(50) NOT NULL,
  actor_id BIGINT NOT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(50) NOT NULL,
  target_id VARCHAR(50) NOT NULL,
  diff JSON NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_audit_action (action, created_at)
);
