<?php

namespace LunaCMS\Core;

use Exception;
use LunaCMS\Core\Config;

class Routing
{
    private static array $routes = [];

    public static function get(string $route, string $controller, string $method = 'index', array $middlewares = []): void
    {
        self::addRoute('GET', $route, $controller, $method, $middlewares);
    }

    public static function post(string $route, string $controller, string $method = 'index', array $middlewares = []): void
    {
        self::addRoute('POST', $route, $controller, $method, $middlewares);
    }

    public static function addRoute(string $method, string $route, string $controller, string $action, array $middlewares = []): void
    {
        self::$routes[$method][$route] = [
            'controller' => $controller,
            'method' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = self::getCurrentUri();

        foreach (self::$routes[$method] ?? [] as $route => $routeData) {
            $routePattern = preg_replace('/{\w+}/', '([^/]+)', $route);
            $routePattern = str_replace('/', '\/', $routePattern);
            $pattern = '/^' . $routePattern . '$/';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                self::execute($routeData, $matches);
                return;
            }
        }

        self::handleNotFound();
    }

    private static function execute(array $routeData, array $params): void
    {
        $controllerName = $routeData['controller'];
        $methodName = $routeData['method'];
        $middlewares = $routeData['middlewares'];

        try {
            // Exécution des middlewares avant le contrôleur
            foreach ($middlewares as $middleware) {
                if (class_exists($middleware)) {
                    (new $middleware())->handle();
                }
            }

            if (!class_exists($controllerName)) {
                throw new Exception("Controller `$controllerName` not found.");
            }

            $config = Config::get('site');
            $controller = new $controllerName($config);
            
            if (!method_exists($controller, $methodName)) {
                throw new Exception("Method `$methodName` not found in `$controllerName`.");
            }

            call_user_func_array([$controller, $methodName], $params);
        } catch (Exception $e) {
            self::handleError($e);
        }
    }

    private static function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return trim($uri, '/');
    }

    private static function handleNotFound(): void
    {
        http_response_code(404);
        echo '404 - Page not found';
        exit;
    }

    private static function handleError(Exception $e): void
    {
        error_log('Routing Error: ' . $e->getMessage());
        http_response_code(500);
        echo '500 - Internal Server Error';
        exit;
    }
}
