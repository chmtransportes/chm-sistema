<?php
/**
 * CHM Sistema - Model Financeiro
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Finance;

use CHM\Core\Model;
use CHM\Core\Database;

class FinanceModel extends Model
{
    protected string $table = 'accounts_payable';

    // Contas a Pagar
    public function getPayables(string $startDate, string $endDate, ?string $status = null): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "accounts_payable 
                WHERE due_date BETWEEN :start AND :end AND deleted_at IS NULL";
        $params = ['start' => $startDate, 'end' => $endDate];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY due_date ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getOverduePayables(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM " . DB_PREFIX . "accounts_payable 
                WHERE due_date < :today AND status IN ('pending', 'partial') AND deleted_at IS NULL
                ORDER BY due_date ASC";
        return $this->db->fetchAll($sql, ['today' => $today]);
    }

    public function createPayable(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('accounts_payable', $data);
    }

    public function payPayable(int $id, float $value, string $method): bool
    {
        $payable = $this->db->fetchOne("SELECT * FROM " . DB_PREFIX . "accounts_payable WHERE id = :id", ['id' => $id]);
        if (!$payable) return false;

        $newPaidValue = $payable['paid_value'] + $value;
        $status = $newPaidValue >= $payable['value'] ? 'paid' : 'partial';
        
        $sql = "UPDATE " . DB_PREFIX . "accounts_payable 
                SET paid_value = :paid_value, status = :status, payment_method = :method, 
                    paid_at = :paid_at, updated_at = NOW()
                WHERE id = :id";
        $this->db->query($sql, [
            'id' => $id,
            'paid_value' => $newPaidValue,
            'status' => $status,
            'method' => $method,
            'paid_at' => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    // Contas a Receber
    public function getReceivables(string $startDate, string $endDate, ?string $status = null): array
    {
        $sql = "SELECT r.*, c.name as client_name 
                FROM " . DB_PREFIX . "accounts_receivable r
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = r.client_id
                WHERE r.due_date BETWEEN :start AND :end AND r.deleted_at IS NULL";
        $params = ['start' => $startDate, 'end' => $endDate];
        
        if ($status) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY r.due_date ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getOverdueReceivables(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT r.*, c.name as client_name 
                FROM " . DB_PREFIX . "accounts_receivable r
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = r.client_id
                WHERE r.due_date < :today AND r.status IN ('pending', 'partial') AND r.deleted_at IS NULL
                ORDER BY r.due_date ASC";
        return $this->db->fetchAll($sql, ['today' => $today]);
    }

    public function createReceivable(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('accounts_receivable', $data);
    }

    public function receivePayment(int $id, float $value, string $method): bool
    {
        $receivable = $this->db->fetchOne("SELECT * FROM " . DB_PREFIX . "accounts_receivable WHERE id = :id", ['id' => $id]);
        if (!$receivable) return false;

        $newReceivedValue = $receivable['received_value'] + $value;
        $status = $newReceivedValue >= $receivable['value'] ? 'received' : 'partial';
        
        $sql = "UPDATE " . DB_PREFIX . "accounts_receivable 
                SET received_value = :received_value, status = :status, payment_method = :method,
                    received_at = :received_at, updated_at = NOW()
                WHERE id = :id";
        $this->db->query($sql, [
            'id' => $id,
            'received_value' => $newReceivedValue,
            'status' => $status,
            'method' => $method,
            'received_at' => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    // Resumo financeiro
    public function getSummary(string $startDate, string $endDate): array
    {
        $payables = $this->db->fetchOne(
            "SELECT SUM(value) as total, SUM(paid_value) as paid 
             FROM " . DB_PREFIX . "accounts_payable 
             WHERE due_date BETWEEN :start AND :end AND deleted_at IS NULL",
            ['start' => $startDate, 'end' => $endDate]
        );

        $receivables = $this->db->fetchOne(
            "SELECT SUM(value) as total, SUM(received_value) as received 
             FROM " . DB_PREFIX . "accounts_receivable 
             WHERE due_date BETWEEN :start AND :end AND deleted_at IS NULL",
            ['start' => $startDate, 'end' => $endDate]
        );

        $bookings = $this->db->fetchOne(
            "SELECT SUM(total) as revenue, SUM(commission_value) as commissions 
             FROM " . DB_PREFIX . "bookings 
             WHERE date BETWEEN :start AND :end AND status = 'completed' AND deleted_at IS NULL",
            ['start' => $startDate, 'end' => $endDate]
        );

        return [
            'payables_total' => (float)($payables['total'] ?? 0),
            'payables_paid' => (float)($payables['paid'] ?? 0),
            'receivables_total' => (float)($receivables['total'] ?? 0),
            'receivables_received' => (float)($receivables['received'] ?? 0),
            'bookings_revenue' => (float)($bookings['revenue'] ?? 0),
            'bookings_commissions' => (float)($bookings['commissions'] ?? 0),
            'balance' => (float)(($receivables['received'] ?? 0) - ($payables['paid'] ?? 0))
        ];
    }
}
