<?php
// Teste de conexão
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP OK\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Testar conexão com banco
try {
    $pdo = new PDO(
        'mysql:host=186.209.113.108;port=3306;dbname=chmtrans_chm-sistema;charset=utf8mb4',
        'chmtrans_chm-sistema',
        'Ca258790%Ca258790%'
    );
    echo "Database: OK\n";
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
