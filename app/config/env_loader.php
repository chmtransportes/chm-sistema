<?php
/**
 * CHM Sistema - Loader de Ambiente
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Carrega variáveis de ambiente baseado no APP_ENV
 */

class EnvLoader
{
    private static bool $loaded = false;
    private static array $env = [];

    // Carrega ambiente de produção
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/env.production.php';
        
        if (file_exists($envFile)) {
            self::$env = require $envFile;
            
            foreach (self::$env as $key => $value) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
            
            self::$loaded = true;
        }
    }

    // Obtém variável de ambiente
    public static function get(string $key, $default = null)
    {
        return self::$env[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    // Verifica se está em produção
    public static function isProduction(): bool
    {
        return self::get('APP_ENV') === 'production';
    }

    // Verifica se está em desenvolvimento
    public static function isDevelopment(): bool
    {
        return self::get('APP_ENV') !== 'production';
    }
}
