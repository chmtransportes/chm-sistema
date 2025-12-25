<?php
/**
 * CHM Sistema - Serviço de Notificações
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class NotificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Obtém todas as notificações/alertas pendentes
    public function getAlerts(): array
    {
        $alerts = [];

        // CNHs vencendo em 30 dias
        $alerts = array_merge($alerts, $this->getCnhAlerts());

        // Contas a pagar vencidas ou próximas do vencimento
        $alerts = array_merge($alerts, $this->getPayableAlerts());

        // Contas a receber vencidas
        $alerts = array_merge($alerts, $this->getReceivableAlerts());

        // Agendamentos pendentes para hoje
        $alerts = array_merge($alerts, $this->getTodayBookingAlerts());

        // Ordenar por prioridade
        usort($alerts, fn($a, $b) => $b['priority'] - $a['priority']);

        return $alerts;
    }

    // CNHs vencendo
    private function getCnhAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');
        $limit30 = date('Y-m-d', strtotime('+30 days'));

        $sql = "SELECT id, name, cnh_expiry FROM " . DB_PREFIX . "drivers 
                WHERE status = 'active' AND cnh_expiry <= :limit AND deleted_at IS NULL
                ORDER BY cnh_expiry ASC";
        $drivers = $this->db->fetchAll($sql, ['limit' => $limit30]);

        foreach ($drivers as $d) {
            $daysLeft = (strtotime($d['cnh_expiry']) - strtotime($today)) / 86400;
            $isExpired = $daysLeft < 0;

            $alerts[] = [
                'type' => 'cnh',
                'priority' => $isExpired ? 10 : 7,
                'icon' => 'bi-person-badge',
                'color' => $isExpired ? 'danger' : 'warning',
                'title' => $isExpired ? 'CNH Vencida' : 'CNH Vencendo',
                'message' => "{$d['name']} - " . ($isExpired ? 'Venceu em ' : 'Vence em ') . Helpers::dataBr($d['cnh_expiry']),
                'link' => APP_URL . "drivers/{$d['id']}/edit",
                'date' => $d['cnh_expiry']
            ];
        }

        return $alerts;
    }

    // Contas a pagar
    private function getPayableAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');
        $limit7 = date('Y-m-d', strtotime('+7 days'));

        $sql = "SELECT id, description, due_date, value FROM " . DB_PREFIX . "accounts_payable 
                WHERE status IN ('pending', 'partial') AND due_date <= :limit AND deleted_at IS NULL
                ORDER BY due_date ASC LIMIT 20";
        $payables = $this->db->fetchAll($sql, ['limit' => $limit7]);

        foreach ($payables as $p) {
            $isOverdue = $p['due_date'] < $today;

            $alerts[] = [
                'type' => 'payable',
                'priority' => $isOverdue ? 9 : 5,
                'icon' => 'bi-arrow-up-circle',
                'color' => $isOverdue ? 'danger' : 'warning',
                'title' => $isOverdue ? 'Conta Vencida' : 'Conta a Vencer',
                'message' => Helpers::truncate($p['description'], 30) . ' - ' . Helpers::moeda($p['value']),
                'link' => APP_URL . 'finance',
                'date' => $p['due_date']
            ];
        }

        return $alerts;
    }

    // Contas a receber
    private function getReceivableAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');

        $sql = "SELECT id, description, due_date, value FROM " . DB_PREFIX . "accounts_receivable 
                WHERE status IN ('pending', 'partial') AND due_date < :today AND deleted_at IS NULL
                ORDER BY due_date ASC LIMIT 10";
        $receivables = $this->db->fetchAll($sql, ['today' => $today]);

        foreach ($receivables as $r) {
            $alerts[] = [
                'type' => 'receivable',
                'priority' => 6,
                'icon' => 'bi-arrow-down-circle',
                'color' => 'info',
                'title' => 'Recebimento Pendente',
                'message' => Helpers::truncate($r['description'], 30) . ' - ' . Helpers::moeda($r['value']),
                'link' => APP_URL . 'finance',
                'date' => $r['due_date']
            ];
        }

        return $alerts;
    }

    // Agendamentos de hoje
    private function getTodayBookingAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');

        $sql = "SELECT b.id, b.code, b.time, b.origin, c.name as client_name 
                FROM " . DB_PREFIX . "bookings b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                WHERE b.date = :today AND b.status IN ('pending', 'confirmed') AND b.deleted_at IS NULL
                ORDER BY b.time ASC";
        $bookings = $this->db->fetchAll($sql, ['today' => $today]);

        foreach ($bookings as $b) {
            $alerts[] = [
                'type' => 'booking',
                'priority' => 8,
                'icon' => 'bi-calendar-event',
                'color' => 'primary',
                'title' => 'Agendamento Hoje',
                'message' => substr($b['time'], 0, 5) . ' - ' . $b['client_name'] . ' - ' . Helpers::truncate($b['origin'], 20),
                'link' => APP_URL . "bookings/{$b['id']}",
                'date' => $today
            ];
        }

        return $alerts;
    }

    // Conta total de alertas
    public function countAlerts(): int
    {
        return count($this->getAlerts());
    }

    // Obtém resumo de alertas por tipo
    public function getAlertsSummary(): array
    {
        $alerts = $this->getAlerts();
        $summary = [
            'total' => count($alerts),
            'critical' => 0,
            'warning' => 0,
            'info' => 0
        ];

        foreach ($alerts as $a) {
            if ($a['color'] === 'danger') $summary['critical']++;
            elseif ($a['color'] === 'warning') $summary['warning']++;
            else $summary['info']++;
        }

        return $summary;
    }
}
