# 商城小程序 + 管理后台 + 自建 API（MVP V1）

本仓库提供「微信小程序 + PHP(PHP-FPM) + Python(FastAPI) + MySQL + Redis + 微信支付V3」的可运行 MVP 骨架，覆盖小程序核心链路、管理后台骨架、订单状态机、库存流水、支付幂等与回调安全、以及 BI 事件表。

> ⚠️ 本版本明确不使用 Docker 环境，请按“本机 Nginx + MySQL + PHP + Python + Redis”部署。

## 一、目录结构（Monorepo）

```
/apps/miniprogram        小程序端（TS + TDesign）
/apps/admin-web          后台前端（Vue3 + ElementPlus）
/services/api-php         PHP 主 API（Laravel 风格路由）
/services/worker-py       Python FastAPI（异步任务/BI导出骨架）
/infra/nginx              Nginx 配置示例
/infra/systemd            systemd 服务示例
/docs/openapi.yaml        OpenAPI 文档
/.env.example             环境变量示例
```

## 二、本机环境准备

- Nginx
- PHP 8.2 + php-fpm
- MySQL 8
- Redis 7
- Python 3.11
- Node 20

> 以下示例以 Linux 为例，路径假设为 `/var/www/diancan`。

## 三、配置与启动（非 Docker）

### 3.1 环境变量

```bash
cp .env.example services/api-php/.env
```

### 3.2 初始化数据库（迁移 + 种子）

```bash
cd services/api-php
php bin/migrate.php
php bin/seed.php
```

### 3.3 启动 PHP-FPM

```bash
sudo systemctl enable php8.2-fpm
sudo systemctl start php8.2-fpm
```

### 3.4 启动 Nginx

```bash
sudo cp infra/nginx/diancan.conf /etc/nginx/conf.d/diancan.conf
sudo nginx -t
sudo systemctl restart nginx
```

### 3.5 启动 Python Worker

```bash
cd services/worker-py
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --host 0.0.0.0 --port 8001
```

### 3.6 启动 Admin Web

```bash
cd apps/admin-web
npm install
npm run dev -- --host 0.0.0.0 --port 5173
```

## 四、默认管理员账号

- 账号：`admin`
- 密码：`Admin@123`

> 请在首次登录后尽快修改。

## 五、服务访问入口

- PHP API: `http://localhost:8080`
- Admin Web: `http://localhost:5173`
- FastAPI: `http://localhost:8001`

## 六、核心流程验证（Mock 支付）

1. 小程序登录 -> 获取 token
2. 拉取商品列表
3. 创建订单
4. mock 支付 -> 订单置为已支付
5. 管理后台查看订单并发货
6. 用户确认收货并评价
7. BI 接口可获取 event_log

## 七、命令行测试示例（curl）

```bash
# 1) 小程序登录（mock code）
curl -X POST http://localhost:8080/api/v1/auth/wx-login \
  -H 'Content-Type: application/json' \
  -d '{"code":"mock-code"}'

# 2) 商品列表
curl http://localhost:8080/api/v1/products

# 3) 创建订单
curl -X POST http://localhost:8080/api/v1/orders \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <TOKEN>' \
  -d '{"items":[{"sku_id":1,"qty":1}],"address_id":1,"fulfillment_type":"SELF"}'

# 4) mock 支付
curl -X POST http://localhost:8080/api/v1/pay/wechat/prepay \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <TOKEN>' \
  -d '{"order_no":"ORDER_NO"}'

# 5) 管理后台登录
curl -X POST http://localhost:8080/api/v1/admin/login \
  -H 'Content-Type: application/json' \
  -d '{"account":"admin","password":"Admin@123"}'

# 6) BI 事件查询
curl "http://localhost:8080/api/v1/bi/events?from=2024-01-01&to=2030-01-01"
```

## 八、常见错误排查

1. **微信支付回调验签失败**
   - 确认 `WECHAT_MCH_CERT_SERIAL`、`WECHAT_PRIVATE_KEY_PATH` 配置正确
   - 真实支付需配置平台证书与证书序列号

2. **支付幂等冲突**
   - `payments` 表中 `out_trade_no` 唯一约束
   - 回调处理中使用 Redis 幂等 Key

3. **跨域问题**
   - Admin Web 通过 Nginx 代理或配置 CORS

4. **Token 过期**
   - JWT `exp` 设置为 7 天
   - 需重新登录换取 token

5. **requestPayment 参数错误**
   - mock 模式下不会调用微信支付
   - 真实模式下需传 `timeStamp/nonceStr/package/signType/paySign`

6. **库存不足**
   - 订单创建会写入库存流水（RESERVE）
   - 检查 `inventory_ledger` 记录

---

如需扩展功能（如真实微信支付、物流对接、多租户、多仓库等），可在当前骨架基础上持续迭代。
