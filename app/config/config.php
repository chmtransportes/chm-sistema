<?php
/**
 * CHM Sistema - Configurações Globais
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 27/12/2025 01:56
 * @version 2.0.0
 */

// Previne acesso direto
if (!defined('CHM_SISTEMA')) {
    http_response_code(403);
    exit('Acesso negado.');
}

// Carregar ambiente de produção se existir (com fallback seguro)
$envLoaderPath = __DIR__ . '/env_loader.php';
if (file_exists($envLoaderPath)) {
    require_once $envLoaderPath;
    EnvLoader::load();
} else {
    // Fallback: classe EnvLoader mínima para não quebrar produção
    class EnvLoader {
        private static $env = [];
        public static function load() {}
        public static function get($key, $default = null) {
            return $_ENV[$key] ?? getenv($key) ?: $default;
        }
        public static function isProduction() {
            return self::get('APP_ENV') === 'production';
        }
        public static function isDevelopment() {
            return !self::isProduction();
        }
    }
}

// Versão do sistema
define('CHM_VERSION', '2.6.4');
define('CHM_VERSION_DATE', '2025-12-29');

// Ambiente (production ou development)
define('CHM_ENVIRONMENT', EnvLoader::get('APP_ENV', 'production'));

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Banco de dados - usa produção se disponível
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_NAME', EnvLoader::get('DB_NAME', 'chm_sistema'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'chm_');

// URLs - usa produção se disponível
if (EnvLoader::isProduction()) {
    define('BASE_URL', 'https://chm-sistema.com.br/');
    define('APP_URL', BASE_URL);
    define('ASSETS_URL', BASE_URL . 'app/assets/');
    define('API_URL', BASE_URL . 'api/');
} else {
    define('BASE_URL', 'http://localhost/chm-sistema/');
    define('APP_URL', BASE_URL . 'app/');
    define('ASSETS_URL', APP_URL . 'assets/');
    define('API_URL', APP_URL . 'api/');
}

// Caminhos - só define se não existir
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', EnvLoader::isProduction() 
        ? dirname(__DIR__) . '/' 
        : dirname(dirname(__DIR__)) . '/');
}
if (!defined('APP_PATH')) {
    define('APP_PATH', EnvLoader::isProduction() 
        ? dirname(__DIR__) . '/' 
        : ROOT_PATH . 'app/');
}
define('CORE_PATH', APP_PATH . 'core/');
define('CONFIG_PATH', APP_PATH . 'config/');
define('ASSETS_PATH', APP_PATH . 'assets/');
define('UPLOADS_PATH', APP_PATH . 'uploads/');
define('BACKUP_PATH', ROOT_PATH . 'backup/');
define('LOGS_PATH', ROOT_PATH . 'logs/');

// Sessão
define('SESSION_NAME', 'CHM_SESSION');
define('SESSION_LIFETIME', 86400);
define('SESSION_SECURE', false);
define('SESSION_HTTPONLY', true);

// Segurança
define('HASH_COST', 12);
define('TOKEN_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// Upload
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Backup - Zero Trust / Fail-Safe
define('BACKUP_INTERVAL', 21600); // 6 horas em segundos
define('BACKUP_RETENTION_DAYS', 30);
define('MAX_BACKUPS', 100);
define('BACKUP_DAILY_RETENTION', 7);
define('BACKUP_WEEKLY_RETENTION', 30);
define('BACKUP_MONTHLY_RETENTION', 90);

// FTP para espelhamento de backup (segundo local independente)
define('FTP_HOST', EnvLoader::get('FTP_HOST', '186.209.113.108'));
define('FTP_PORT', EnvLoader::get('FTP_PORT', '21'));
define('FTP_USER', EnvLoader::get('FTP_USER', ''));
define('FTP_PASS', EnvLoader::get('FTP_PASS', ''));
define('FTP_BACKUP_DIR', '/backups/');

// WhatsApp Business API
define('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0/');
define('WHATSAPP_PHONE_ID', '');
define('WHATSAPP_TOKEN', '');
define('WHATSAPP_VERIFY_TOKEN', '');

// E-mail
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@chm-sistema.com.br');
define('SMTP_FROM_NAME', 'CHM Sistema');

// Comissão
define('COMMISSION_RATE', 0.11);

// PWA
define('PWA_NAME', 'CHM Sistema');
define('PWA_SHORT_NAME', 'CHM');
define('PWA_THEME_COLOR', '#1a1a2e');
define('PWA_BACKGROUND_COLOR', '#ffffff');

// Perfis
define('PROFILE_ADMIN', 1);
define('PROFILE_DRIVER', 2);
define('PROFILE_CLIENT', 3);

// Status de agendamento
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_IN_PROGRESS', 'in_progress');
define('BOOKING_COMPLETED', 'completed');
define('BOOKING_CANCELLED', 'cancelled');

// Formas de pagamento
define('PAYMENT_CASH', 'cash');
define('PAYMENT_PIX', 'pix');
define('PAYMENT_CREDIT', 'credit');
define('PAYMENT_DEBIT', 'debit');
define('PAYMENT_TRANSFER', 'transfer');
define('PAYMENT_INVOICE', 'invoice');

// Tipos de serviço
define('SERVICE_TRANSFER', 'transfer');
define('SERVICE_HOURLY', 'hourly');
define('SERVICE_DAILY', 'daily');
define('SERVICE_AIRPORT', 'airport');
define('SERVICE_EXECUTIVE', 'executive');
define('SERVICE_EVENT', 'event');

// Debug
define('DEBUG_MODE', CHM_ENVIRONMENT === 'development');

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . 'php-errors.log');
