<?php
/**
 * CHM Sistema - Configurações de Produção
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * ATENÇÃO: Este arquivo contém credenciais sensíveis.
 * NÃO commitar em repositórios públicos.
 */

return [
    // Ambiente
    'APP_ENV' => 'production',
    'APP_URL' => 'https://chm-sistema.com.br',
    'APP_DEBUG' => false,
    
    // Banco de Dados MySQL
    'DB_TYPE' => 'mysql',
    'DB_HOST' => '186.209.113.108',
    'DB_PORT' => '3306',
    'DB_NAME' => 'chmtrans_chm-sistema',
    'DB_USER' => 'chmtrans_chm-sistema',
    'DB_PASS' => 'Ca258790%Ca258790%',
    
    // FTP Napoleão
    'FTP_HOST' => '186.209.113.108',
    'FTP_PORT' => '21',
    'FTP_USER' => 'chm-sistema@chm-sistema.com.br',
    'FTP_PASS' => 'Ca258790%Ca258790%',
    'FTP_ROOT' => '/',
    
    // Segurança
    'DEPLOY_SECRET' => 'chm_deploy_' . md5('CHM-SISTEMA-2025'),
];
