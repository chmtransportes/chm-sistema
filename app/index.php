<?php
/**
 * TESTE CONTROLADO - app/index.php
 * NÃO é definitivo
 * Serve apenas para diagnóstico
 */

http_response_code(200);

echo '<h1>TESTE OK</h1>';
echo '<p>Arquivo: app/index.php</p>';
echo '<p>PHP está executando corretamente.</p>';
echo '<hr>';

echo '<pre>';
echo 'DIR: ' . __DIR__ . PHP_EOL;
echo 'PHP VERSION: ' . phpversion() . PHP_EOL;
echo '</pre>';

exit;
