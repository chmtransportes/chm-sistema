<?php
/**
 * CHM Sistema - Entry Point Produção
 * @author ch-mestriner
 * @date 27/12/2025
 */

declare(strict_types=1);

// Evita dupla definição
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/');
}

// Carrega config principal UMA VEZ
require_once BASE_PATH . 'config/config.php';

// Redireciona para o app real
require_once BASE_PATH . 'app/index.php';
