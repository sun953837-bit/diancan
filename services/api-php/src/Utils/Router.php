<?php

namespace App\Utils;

class Router
{
    private array $routes = [];

    /**
     * 注册路由
     */
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    /**
     * 匹配并执行路由
     */
    public function dispatch(string $method, string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return call_user_func_array($route['handler'], $matches);
            }
        }

        return ['code' => 404, 'msg' => 'Not Found', 'data' => null];
    }
}
