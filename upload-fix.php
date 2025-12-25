<?php
// Upload de arquivos - Responsividade Mobile
$conn = ftp_connect('186.209.113.108', 21, 90);
ftp_login($conn, 'chm-sistema@chm-sistema.com.br', 'Ca258790%Ca258790%');
ftp_pasv($conn, true);

// Upload app.css
if (ftp_put($conn, '/assets/css/app.css', __DIR__ . '/app/assets/css/app.css', FTP_ASCII)) {
    echo "✓ app.css\n";
}

// Upload layouts/main.php
if (ftp_put($conn, '/views/layouts/main.php', __DIR__ . '/app/views/layouts/main.php', FTP_ASCII)) {
    echo "✓ layouts/main.php\n";
}

ftp_close($conn);
echo "✓ Responsividade aplicada!\n";
