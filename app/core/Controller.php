<?php
/**
 * CHM Sistema - Controller Base
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

abstract class Controller
{
    protected Database $db;
    protected array $data = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function view(string $view, array $data = [], bool $withLayout = true): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = APP_PATH . 'views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: {$view}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        $layoutPath = APP_PATH . 'views/layouts/main.php';
        if ($withLayout && file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function partial(string $view, array $data = []): string
    {
        extract(array_merge($this->data, $data));
        $viewPath = APP_PATH . 'views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) return '';
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    protected function json(mixed $data, int $code = 200): void
    {
        Router::json($data, $code);
    }

    protected function success(string $message, mixed $data = null, int $code = 200): void
    {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) $response['data'] = $data;
        $this->json($response, $code);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): void
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors !== null) $response['errors'] = $errors;
        $this->json($response, $code);
    }

    protected function redirect(string $url, int $code = 302): void
    {
        Router::redirect($url, $code);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function validateCsrf(): bool
    {
        $token = $this->input('_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return Session::validateCsrfToken($token);
    }

    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            if ($this->isAjax()) {
                $this->error('Não autorizado', 401);
            }
            Session::flash('error', 'Faça login para continuar.');
            $this->redirect(APP_URL . 'login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Session::isAdmin()) {
            if ($this->isAjax()) {
                $this->error('Acesso negado', 403);
            }
            Session::flash('error', 'Acesso restrito a administradores.');
            $this->redirect(APP_URL . 'dashboard');
        }
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function isMethod(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    protected function setTitle(string $title): void
    {
        $this->data['pageTitle'] = $title . ' | CHM Sistema';
    }

    protected function setBreadcrumb(array $items): void
    {
        $this->data['breadcrumb'] = $items;
    }
}
