<?php
/**
 * CHM Sistema - Roteador de URLs
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private static ?Router $instance = null;

    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix .= $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
    }

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $fullPath = $this->prefix . $path;
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function middleware(string $name, callable $handler): void
    {
        $this->middlewares[$name] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middlewareName) {
                    if (isset($this->middlewares[$middlewareName])) {
                        if (call_user_func($this->middlewares[$middlewareName]) === false) return;
                    }
                }

                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        $this->notFound();
    }

    private function getUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Em produção não tem basePath, em desenvolvimento tem
        if (defined('CHM_ENVIRONMENT') && CHM_ENVIRONMENT === 'development') {
            $basePath = '/chm-sistema/app';
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        }
        
        return $uri ?: '/';
    }

    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        if (file_exists(APP_PATH . 'views/errors/404.php')) {
            include APP_PATH . 'views/errors/404.php';
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
        }
    }

    public static function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }

    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
