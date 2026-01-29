<?php

require __DIR__ . '/../src/Bootstrap.php';

use App\Utils\Response;
use App\Utils\Router;

$router = new Router();
require __DIR__ . '/../src/routes.php';

$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

Response::json($response['code'], $response['msg'], $response['data']);
