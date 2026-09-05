<?php

namespace MyFolio\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): mixed
    {
        $handler = $this->routes[$method][$path] ?? null;
        $parameters = [];
        if ($handler === null) {
            foreach ($this->routes[$method] ?? [] as $route => $candidate) {
                $pattern = preg_replace('/\\\{[^}]+\\\}/', '([^/]+)', preg_quote($route, '#'));
                if ($pattern !== null && preg_match('#^' . $pattern . '$#', $path, $matches)) {
                    $handler = $candidate;
                    $parameters = array_slice($matches, 1);
                    break;
                }
            }
        }
        if ($handler === null) {
            http_response_code(404);
            return 'Page not found';
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            return (new $class())->{$action}(...$parameters);
        }

        return $handler();
    }
}