<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada | CHM Sistema</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .container { max-width: 500px; }
        h1 { font-size: 120px; line-height: 1; opacity: 0.3; }
        h2 { font-size: 24px; margin: 20px 0; }
        p { opacity: 0.7; margin-bottom: 30px; }
        a {
            display: inline-block;
            padding: 12px 30px;
            background: #e94560;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        a:hover { background: #d63853; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Página não encontrada</h2>
        <p>A página que você está procurando não existe ou foi movida.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>dashboard">Voltar ao Dashboard</a>
    </div>
</body>
</html>
