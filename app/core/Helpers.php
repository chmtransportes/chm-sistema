<?php
/**
 * CHM Sistema - Funções Helpers
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class Helpers
{
    // Formata data BR
    public static function dataBr(string $date): string
    {
        if (empty($date)) return '';
        return date('d/m/Y', strtotime($date));
    }

    // Formata data e hora BR
    public static function dataHoraBr(string $date): string
    {
        if (empty($date)) return '';
        return date('d/m/Y H:i', strtotime($date));
    }

    // Converte data BR para MySQL
    public static function dataMysql(string $date): string
    {
        if (empty($date)) return '';
        $parts = explode('/', $date);
        if (count($parts) !== 3) return $date;
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
    }

    // Formata moeda BR
    public static function moeda(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    // Converte moeda BR para float
    public static function moedaFloat(string $value): float
    {
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }

    // Formata CPF
    public static function formatCpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    // Formata CNPJ
    public static function formatCnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    // Formata telefone
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        }
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
    }

    // Formata CEP
    public static function formatCep(string $cep): string
    {
        $cep = preg_replace('/\D/', '', $cep);
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }

    // Formata placa de veículo
    public static function formatPlate(string $plate): string
    {
        $plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate));
        if (strlen($plate) === 7) {
            return substr($plate, 0, 3) . '-' . substr($plate, 3);
        }
        return $plate;
    }

    // Remove formatação
    public static function onlyNumbers(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    // Valida CPF
    public static function validaCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) return false;
        }
        return true;
    }

    // Valida CNPJ
    public static function validaCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;

        $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);
        if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return false;

        for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);
        if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return false;

        return true;
    }

    // Valida e-mail
    public static function validaEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Gera slug
    public static function slug(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        return strtolower($text);
    }

    // Gera token aleatório
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    // Trunca texto
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . $suffix;
    }

    // Calcula idade
    public static function idade(string $birthDate): int
    {
        return (int) date_diff(date_create($birthDate), date_create('today'))->y;
    }

    // Retorna status formatado
    public static function statusLabel(string $status): string
    {
        $labels = [
            'pending' => '<span class="badge bg-warning">Pendente</span>',
            'confirmed' => '<span class="badge bg-info">Confirmado</span>',
            'in_progress' => '<span class="badge bg-primary">Em Andamento</span>',
            'completed' => '<span class="badge bg-success">Concluído</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelado</span>',
            'active' => '<span class="badge bg-success">Ativo</span>',
            'inactive' => '<span class="badge bg-secondary">Inativo</span>'
        ];
        return $labels[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    // Retorna perfil formatado
    public static function profileLabel(mixed $profile = null): string
    {
        if ($profile === null) {
            return '<span class="badge bg-secondary">Visitante</span>';
        }
        $labels = [
            PROFILE_ADMIN => '<span class="badge bg-danger">Administrador</span>',
            PROFILE_DRIVER => '<span class="badge bg-primary">Motorista</span>',
            PROFILE_CLIENT => '<span class="badge bg-success">Cliente</span>'
        ];
        return $labels[$profile] ?? '<span class="badge bg-secondary">Desconhecido</span>';
    }

    // Retorna pagamento formatado
    public static function paymentLabel(string $payment): string
    {
        $labels = [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'credit' => 'Cartão Crédito',
            'debit' => 'Cartão Débito',
            'transfer' => 'Transferência',
            'invoice' => 'Faturado'
        ];
        return $labels[$payment] ?? ucfirst($payment);
    }

    // Retorna status financeiro formatado
    public static function financeStatus(string $status): string
    {
        $labels = [
            'pending' => '<span class="badge bg-warning">Pendente</span>',
            'partial' => '<span class="badge bg-info">Parcial</span>',
            'paid' => '<span class="badge bg-success">Pago</span>',
            'received' => '<span class="badge bg-success">Recebido</span>',
            'overdue' => '<span class="badge bg-danger">Vencido</span>',
            'cancelled' => '<span class="badge bg-secondary">Cancelado</span>'
        ];
        return $labels[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    // Retorna serviço formatado
    public static function serviceLabel(string $service): string
    {
        $labels = [
            'transfer' => 'Transfer',
            'hourly' => 'Por Hora',
            'daily' => 'Diária',
            'airport' => 'Aeroporto',
            'executive' => 'Executivo',
            'event' => 'Evento'
        ];
        return $labels[$service] ?? ucfirst($service);
    }

    // Log de ações
    public static function logAction(string $action, string $module, ?int $userId = null, ?array $data = null): void
    {
        $logFile = LOGS_PATH . 'actions-' . date('Y-m') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $userId = $userId ?? Session::getUserId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        $logEntry = "[{$timestamp}] [{$ip}] [User: {$userId}] [{$module}] {$action}";
        if ($data) {
            $logEntry .= ' | Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    // Limpa cache
    public static function clearCache(): void
    {
        $cacheDir = APP_PATH . 'cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    // Formata bytes para humano
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
