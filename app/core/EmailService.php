<?php
/**
 * CHM Sistema - Serviço de E-mail
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 18:00
 * @version 1.0.0
 */

namespace CHM\Core;

class EmailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private bool $useTls;
    
    public function __construct()
    {
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $this->username = defined('SMTP_USER') ? SMTP_USER : '';
        $this->password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->fromEmail = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@chm-sistema.com.br';
        $this->fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'CHM Sistema';
        $this->useTls = $this->port === 587;
    }
    
    // Envia e-mail via SMTP
    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        // Se SMTP não configurado, usa mail() nativo
        if (empty($this->host) || empty($this->username)) {
            return $this->sendNative($to, $subject, $body, $isHtml);
        }
        
        try {
            return $this->sendSmtp($to, $subject, $body, $isHtml);
        } catch (\Exception $e) {
            $this->log("Erro SMTP: " . $e->getMessage());
            // Fallback para mail() nativo
            return $this->sendNative($to, $subject, $body, $isHtml);
        }
    }
    
    // Envio via SMTP direto
    private function sendSmtp(string $to, string $subject, string $body, bool $isHtml): bool
    {
        $socket = @fsockopen(
            ($this->useTls ? 'tls://' : '') . $this->host,
            $this->port,
            $errno,
            $errstr,
            30
        );
        
        if (!$socket) {
            throw new \Exception("Não foi possível conectar ao servidor SMTP: {$errstr}");
        }
        
        $this->getResponse($socket);
        
        // EHLO
        $this->sendCommand($socket, "EHLO " . gethostname());
        
        // AUTH LOGIN
        $this->sendCommand($socket, "AUTH LOGIN");
        $this->sendCommand($socket, base64_encode($this->username));
        $this->sendCommand($socket, base64_encode($this->password));
        
        // MAIL FROM
        $this->sendCommand($socket, "MAIL FROM:<{$this->fromEmail}>");
        
        // RCPT TO
        $this->sendCommand($socket, "RCPT TO:<{$to}>");
        
        // DATA
        $this->sendCommand($socket, "DATA");
        
        // Headers + Body
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $message = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: {$contentType}; charset=UTF-8\r\n";
        $message .= "X-Mailer: CHM-Sistema/2.5.0\r\n";
        $message .= "\r\n";
        $message .= $body;
        $message .= "\r\n.";
        
        $this->sendCommand($socket, $message);
        
        // QUIT
        $this->sendCommand($socket, "QUIT");
        
        fclose($socket);
        
        $this->log("E-mail enviado para {$to}: {$subject}");
        return true;
    }
    
    private function sendCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->getResponse($socket);
    }
    
    private function getResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }
    
    // Envio via mail() nativo do PHP
    private function sendNative(string $to, string $subject, string $body, bool $isHtml): bool
    {
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        
        $headers = [
            "From: {$this->fromName} <{$this->fromEmail}>",
            "Reply-To: {$this->fromEmail}",
            "MIME-Version: 1.0",
            "Content-Type: {$contentType}; charset=UTF-8",
            "X-Mailer: CHM-Sistema/2.5.0"
        ];
        
        $result = @mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($result) {
            $this->log("E-mail enviado (nativo) para {$to}: {$subject}");
        } else {
            $this->log("Falha ao enviar e-mail para {$to}");
        }
        
        return $result;
    }
    
    // Envia e-mail com template HTML
    public function sendTemplate(string $to, string $subject, string $template, array $data = []): bool
    {
        $body = $this->renderTemplate($template, $data);
        return $this->send($to, $subject, $body, true);
    }
    
    // Renderiza template de e-mail
    private function renderTemplate(string $template, array $data): string
    {
        $templateFile = APP_PATH . 'views/emails/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            extract($data);
            ob_start();
            include $templateFile;
            return ob_get_clean();
        }
        
        // Template padrão
        return $this->getDefaultTemplate($data);
    }
    
    // Template padrão
    private function getDefaultTemplate(array $data): string
    {
        $title = $data['title'] ?? 'Notificação';
        $message = $data['message'] ?? '';
        $actionUrl = $data['action_url'] ?? '';
        $actionText = $data['action_text'] ?? 'Acessar Sistema';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <tr>
                    <td style='padding: 30px 40px; background-color: #1a73e8; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>CHM Sistema</h1>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 40px;'>
                        <h2 style='color: #333; margin: 0 0 20px;'>{$title}</h2>
                        <div style='color: #666; line-height: 1.6;'>{$message}</div>
                        " . ($actionUrl ? "
                        <div style='margin-top: 30px; text-align: center;'>
                            <a href='{$actionUrl}' style='display: inline-block; padding: 12px 30px; background-color: #1a73e8; color: #ffffff; text-decoration: none; border-radius: 4px;'>{$actionText}</a>
                        </div>
                        " : "") . "
                    </td>
                </tr>
                <tr>
                    <td style='padding: 20px 40px; background-color: #f5f5f5; text-align: center; font-size: 12px; color: #999;'>
                        <p>Este e-mail foi enviado automaticamente pelo CHM Sistema.</p>
                        <p>© " . date('Y') . " CHM Transportes - Todos os direitos reservados.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
    
    // Log de e-mails
    private function log(string $message): void
    {
        $logFile = (defined('LOGS_PATH') ? LOGS_PATH : dirname(dirname(__DIR__)) . '/logs/') . 'email.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }
    
    // Verifica se e-mail é válido
    public static function isValid(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Envia e-mail em massa (com delay para evitar spam)
    public function sendBulk(array $recipients, string $subject, string $body, int $delayMs = 500): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        
        foreach ($recipients as $email) {
            if (!self::isValid($email)) {
                $results['failed']++;
                $results['errors'][] = "E-mail inválido: {$email}";
                continue;
            }
            
            if ($this->send($email, $subject, $body)) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Falha ao enviar para: {$email}";
            }
            
            usleep($delayMs * 1000);
        }
        
        return $results;
    }
}
