<?php

use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\PayController;
use App\Controllers\FulfillmentController;
use App\Controllers\ReviewController;
use App\Controllers\AdminController;
use App\Controllers\BiController;

$router->add('POST', '/api/v1/auth/wx-login', [new AuthController(), 'wxLogin']);
$router->add('GET', '/api/v1/products', [new ProductController(), 'index']);
$router->add('GET', '/api/v1/products/{id}', [new ProductController(), 'show']);
$router->add('POST', '/api/v1/orders', [new OrderController(), 'store']);
$router->add('GET', '/api/v1/orders', [new OrderController(), 'index']);
$router->add('GET', '/api/v1/orders/{order_no}', [new OrderController(), 'show']);
$router->add('POST', '/api/v1/pay/wechat/prepay', [new PayController(), 'prepay']);
$router->add('POST', '/api/v1/pay/wechat/notify', [new PayController(), 'notify']);
$router->add('POST', '/api/v1/fulfillment/self/assign', [new FulfillmentController(), 'assignSelf']);
$router->add('POST', '/api/v1/fulfillment/courier/ship', [new FulfillmentController(), 'shipCourier']);
$router->add('GET', '/api/v1/fulfillment/courier/track', [new FulfillmentController(), 'trackCourier']);
$router->add('POST', '/api/v1/reviews', [new ReviewController(), 'store']);
$router->add('POST', '/api/v1/admin/login', [new AdminController(), 'login']);
$router->add('GET', '/api/v1/bi/orders', [new BiController(), 'orders']);
$router->add('GET', '/api/v1/bi/events', [new BiController(), 'events']);
