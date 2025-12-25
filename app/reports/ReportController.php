<?php
/**
 * CHM Sistema - Controller de Relatórios
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Reports;

use CHM\Core\Controller;
use CHM\Core\Database;
use CHM\Core\Helpers;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->setTitle('Relatórios');
        $this->view('reports.index');
    }

    public function bookings(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        $status = $this->input('status');

        $sql = "SELECT b.*, c.name as client_name, d.name as driver_name
                FROM " . DB_PREFIX . "bookings b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                WHERE b.date BETWEEN :start AND :end AND b.deleted_at IS NULL";
        $params = ['start' => $start, 'end' => $end];

        if ($status) {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY b.date DESC, b.time DESC";

        $bookings = $this->db->fetchAll($sql, $params);

        $this->setTitle('Relatório de Agendamentos');
        $this->setData('bookings', $bookings);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.bookings');
    }

    public function revenueByClient(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT c.id, c.name, c.document,
                COUNT(b.id) as total_services,
                SUM(b.total) as total_value,
                SUM(b.commission_value) as total_commission
                FROM " . DB_PREFIX . "clients c
                INNER JOIN " . DB_PREFIX . "bookings b ON b.client_id = c.id
                WHERE b.date BETWEEN :start AND :end 
                AND b.status = 'completed' AND b.deleted_at IS NULL
                GROUP BY c.id ORDER BY total_value DESC";

        $data = $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Faturamento por Cliente');
        $this->setData('data', $data);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.revenue-client');
    }

    public function revenueByPayment(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT payment_method,
                COUNT(*) as total_services,
                SUM(total) as total_value
                FROM " . DB_PREFIX . "bookings
                WHERE date BETWEEN :start AND :end 
                AND status = 'completed' AND deleted_at IS NULL
                GROUP BY payment_method ORDER BY total_value DESC";

        $data = $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Faturamento por Forma de Pagamento');
        $this->setData('data', $data);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.revenue-payment');
    }

    public function revenueByService(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT service_type,
                COUNT(*) as total_services,
                SUM(total) as total_value,
                AVG(total) as avg_value
                FROM " . DB_PREFIX . "bookings
                WHERE date BETWEEN :start AND :end 
                AND status = 'completed' AND deleted_at IS NULL
                GROUP BY service_type ORDER BY total_value DESC";

        $data = $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Faturamento por Tipo de Serviço');
        $this->setData('data', $data);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.revenue-service');
    }

    public function revenueByDriver(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT d.id, d.name, d.type,
                COUNT(b.id) as total_services,
                SUM(b.total) as total_value,
                SUM(b.commission_value) as total_commission
                FROM " . DB_PREFIX . "drivers d
                INNER JOIN " . DB_PREFIX . "bookings b ON b.driver_id = d.id
                WHERE b.date BETWEEN :start AND :end 
                AND b.status = 'completed' AND b.deleted_at IS NULL
                GROUP BY d.id ORDER BY total_value DESC";

        $data = $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Faturamento por Motorista');
        $this->setData('data', $data);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.revenue-driver');
    }

    public function revenueByVehicle(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT v.id, v.plate, v.model, v.brand,
                COUNT(b.id) as total_services,
                SUM(b.total) as total_value
                FROM " . DB_PREFIX . "vehicles v
                INNER JOIN " . DB_PREFIX . "bookings b ON b.vehicle_id = v.id
                WHERE b.date BETWEEN :start AND :end 
                AND b.status = 'completed' AND b.deleted_at IS NULL
                GROUP BY v.id ORDER BY total_value DESC";

        $data = $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Faturamento por Veículo');
        $this->setData('data', $data);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.revenue-vehicle');
    }

    public function commissions(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        $sql = "SELECT 
                SUM(total) as total_revenue,
                SUM(commission_value) as total_commission,
                AVG(commission_rate) as avg_rate
                FROM " . DB_PREFIX . "bookings
                WHERE date BETWEEN :start AND :end 
                AND status = 'completed' AND deleted_at IS NULL";

        $summary = $this->db->fetchOne($sql, ['start' => $start, 'end' => $end]);

        $this->setTitle('Relatório de Comissões');
        $this->setData('summary', $summary);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.commissions');
    }

    public function driverClosing(): void
    {
        $this->requireAuth();
        $driverId = $this->input('driver_id');
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        if (!$driverId) {
            $sql = "SELECT * FROM " . DB_PREFIX . "drivers WHERE status = 'active' ORDER BY name";
            $drivers = $this->db->fetchAll($sql);
            $this->setData('drivers', $drivers);
            $this->setTitle('Fechamento de Motoristas');
            $this->view('reports.driver-closing-select');
            return;
        }

        $sql = "SELECT * FROM " . DB_PREFIX . "drivers WHERE id = :id";
        $driver = $this->db->fetchOne($sql, ['id' => $driverId]);

        $sql = "SELECT b.*, c.name as client_name
                FROM " . DB_PREFIX . "bookings b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                WHERE b.driver_id = :driver_id 
                AND b.date BETWEEN :start AND :end
                AND b.status = 'completed'
                ORDER BY b.date, b.time";

        $bookings = $this->db->fetchAll($sql, [
            'driver_id' => $driverId,
            'start' => $start,
            'end' => $end
        ]);

        $totals = ['services' => 0, 'value' => 0, 'commission' => 0];
        foreach ($bookings as $b) {
            $totals['services']++;
            $totals['value'] += $b['total'];
            $totals['commission'] += $b['commission_value'];
        }

        $this->setTitle('Fechamento - ' . $driver['name']);
        $this->setData('driver', $driver);
        $this->setData('bookings', $bookings);
        $this->setData('totals', $totals);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.driver-closing');
    }

    // Fluxo de Caixa
    public function cashFlow(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        // Entradas (contas recebidas)
        $sqlReceivables = "SELECT due_date, description, value, received_value, status, category
                          FROM " . DB_PREFIX . "accounts_receivable
                          WHERE due_date BETWEEN :start AND :end AND deleted_at IS NULL
                          ORDER BY due_date ASC";
        $receivables = $this->db->fetchAll($sqlReceivables, ['start' => $start, 'end' => $end]);

        // Saídas (contas pagas)
        $sqlPayables = "SELECT due_date, description, value, paid_value, status, category
                       FROM " . DB_PREFIX . "accounts_payable
                       WHERE due_date BETWEEN :start AND :end AND deleted_at IS NULL
                       ORDER BY due_date ASC";
        $payables = $this->db->fetchAll($sqlPayables, ['start' => $start, 'end' => $end]);

        // Totais
        $totals = [
            'receivables' => array_sum(array_column($receivables, 'value')),
            'received' => array_sum(array_column($receivables, 'received_value')),
            'payables' => array_sum(array_column($payables, 'value')),
            'paid' => array_sum(array_column($payables, 'paid_value')),
        ];
        $totals['balance'] = $totals['received'] - $totals['paid'];
        $totals['pending_receive'] = $totals['receivables'] - $totals['received'];
        $totals['pending_pay'] = $totals['payables'] - $totals['paid'];

        $this->setTitle('Fluxo de Caixa');
        $this->setData('receivables', $receivables);
        $this->setData('payables', $payables);
        $this->setData('totals', $totals);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.cash-flow');
    }

    // DRE Simplificado
    public function dre(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));

        // Receita Bruta (agendamentos concluídos)
        $sqlRevenue = "SELECT SUM(total) as value FROM " . DB_PREFIX . "bookings
                      WHERE date BETWEEN :start AND :end AND status = 'completed' AND deleted_at IS NULL";
        $revenue = $this->db->fetchOne($sqlRevenue, ['start' => $start, 'end' => $end]);

        // Comissões (dedução)
        $sqlCommissions = "SELECT SUM(commission_value) as value FROM " . DB_PREFIX . "bookings
                         WHERE date BETWEEN :start AND :end AND status = 'completed' AND deleted_at IS NULL";
        $commissions = $this->db->fetchOne($sqlCommissions, ['start' => $start, 'end' => $end]);

        // Despesas por categoria
        $sqlExpenses = "SELECT category, SUM(value) as value FROM " . DB_PREFIX . "accounts_payable
                       WHERE due_date BETWEEN :start AND :end AND status = 'paid' AND deleted_at IS NULL
                       GROUP BY category ORDER BY value DESC";
        $expenses = $this->db->fetchAll($sqlExpenses, ['start' => $start, 'end' => $end]);

        $totalExpenses = array_sum(array_column($expenses, 'value'));

        // Cálculos DRE
        $dre = [
            'receita_bruta' => (float)($revenue['value'] ?? 0),
            'deducoes' => (float)($commissions['value'] ?? 0),
            'receita_liquida' => (float)($revenue['value'] ?? 0) - (float)($commissions['value'] ?? 0),
            'despesas' => $totalExpenses,
            'despesas_detalhes' => $expenses,
            'resultado' => (float)($revenue['value'] ?? 0) - (float)($commissions['value'] ?? 0) - $totalExpenses
        ];

        $this->setTitle('DRE Simplificado');
        $this->setData('dre', $dre);
        $this->setData('start', $start);
        $this->setData('end', $end);
        $this->view('reports.dre');
    }
}
