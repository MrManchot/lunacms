<?php

namespace LunaCMS;

use LunaCMS\Controller;
use ErrorException;
use Exception;

class Routing
{
    private static array $routes = [];

    public function __construct()
    {
        $uri = $this->getCurrentUri();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->dispatch($uri, $method);
    }

    public static function get(string $route, string $controller): void
    {
        self::$routes[$route]['GET'] = $controller;
    }

    public static function post(string $route, string $controller): void
    {
        self::$routes[$route]['POST'] = $controller;
    }

    private function dispatch(string $uri, string $method): void
    {
        foreach (self::$routes as $route => $actions) {
            $routePattern = preg_replace('/{\w+}/', '([^/]+)', $route);
            $routePattern = str_replace('/', '\/', $routePattern);
            $pattern = '/^' . $routePattern . '$/';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                if (isset($actions[$method])) {
                    $controllerClass = $actions[$method];
                    $params = $this->bindParams($route, $matches);
                    $this->callAction($controllerClass, $params);
                    return;
                } else {
                    $this->handleMethodNotAllowed();
                }
            }
        }

        $this->handleNotFound();
    }

    private function bindParams(string $route, array $matches): array
    {
        $params = [];
        preg_match_all('/{(\w+)}/', $route, $paramNames);
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }
        return $params;
    }

    private function callAction(string $controller, array $params): void
    {
        if (class_exists($controller)) {
            try {
                $config = Controller::getConfig();
                $twig = Controller::getTemplating();
                $controllerInstance = new $controller($twig, $config);
                $controllerInstance->init($params);
            } catch (Exception $e) {
                $this->handleError($e);
            }
        } else {
            $this->handleError(new ErrorException("Controller `{$controller}` not found."));
        }
    }

    private function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return trim($uri, '/');
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        echo '404 - Page not found';
        exit;
    }

    private function handleMethodNotAllowed(): void
    {
        http_response_code(405);
        echo '405 - Method not allowed';
        exit;
    }

    private function handleError(Exception $e): void
    {
        if (Controller::getConfig()['debug'] ?? false) {
            echo '500 - Internal Server Error<br>';
            echo 'Error message: ' . $e->getMessage() . '<br>';
            echo 'File: ' . $e->getFile() . '<br>';
            echo 'Line: ' . $e->getLine() . '<br>';
            echo 'Trace:<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            http_response_code(500);
            echo '500 - Internal Server Error';
        }
        exit;
    }
}