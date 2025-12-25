<?php
/**
 * CHM Sistema - Controller do Dashboard
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Users;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Bookings\BookingModel;
use CHM\Clients\ClientModel;
use CHM\Drivers\DriverModel;
use CHM\Finance\FinanceModel;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $profile = Session::getUserProfile();

        switch ($profile) {
            case PROFILE_ADMIN:
                $this->adminDashboard();
                break;
            case PROFILE_DRIVER:
                $this->driverDashboard();
                break;
            case PROFILE_CLIENT:
                $this->clientDashboard();
                break;
            default:
                $this->adminDashboard();
        }
    }

    private function adminDashboard(): void
    {
        $bookingModel = new BookingModel();
        $clientModel = new ClientModel();
        $driverModel = new DriverModel();
        $financeModel = new FinanceModel();

        $today = date('Y-m-d');
        $startMonth = date('Y-m-01');
        $endMonth = date('Y-m-t');

        // Estatísticas gerais
        $stats = [
            'bookings_today' => count($bookingModel->getByDate($today)),
            'bookings_pending' => $bookingModel->count(['status' => 'pending']),
            'clients_total' => $clientModel->count(['status' => 'active']),
            'drivers_total' => $driverModel->count(['status' => 'active'])
        ];

        // Estatísticas do mês
        $monthStats = $bookingModel->getStats($startMonth, $endMonth);
        
        // Resumo financeiro
        $financeSummary = $financeModel->getSummary($startMonth, $endMonth);

        // Próximos agendamentos
        $upcomingBookings = $bookingModel->getUpcoming(5);

        // Contas vencidas
        $overduePayables = $financeModel->getOverduePayables();
        $overdueReceivables = $financeModel->getOverdueReceivables();

        // CNHs próximas de vencer
        $expiringCnh = $driverModel->getWithCnhExpiring(30);

        $this->setTitle('Dashboard');
        $this->setData('stats', $stats);
        $this->setData('monthStats', $monthStats);
        $this->setData('financeSummary', $financeSummary);
        $this->setData('upcomingBookings', $upcomingBookings);
        $this->setData('overduePayables', $overduePayables);
        $this->setData('overdueReceivables', $overdueReceivables);
        $this->setData('expiringCnh', $expiringCnh);
        $this->view('dashboard.admin');
    }

    private function driverDashboard(): void
    {
        $userId = Session::getUserId();
        
        // Busca o motorista vinculado ao usuário
        $sql = "SELECT * FROM " . DB_PREFIX . "drivers WHERE user_id = :user_id LIMIT 1";
        $driver = $this->db->fetchOne($sql, ['user_id' => $userId]);

        if (!$driver) {
            $this->setTitle('Dashboard');
            $this->setData('error', 'Perfil de motorista não encontrado.');
            $this->view('dashboard.driver');
            return;
        }

        $bookingModel = new BookingModel();
        $today = date('Y-m-d');
        $startMonth = date('Y-m-01');
        $endMonth = date('Y-m-t');

        // Serviços de hoje
        $todayBookings = $bookingModel->getByDateRange($today, $today, $driver['id']);

        // Próximos serviços
        $upcomingBookings = $this->db->fetchAll(
            "SELECT b.*, c.name as client_name 
             FROM " . DB_PREFIX . "bookings b
             LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
             WHERE b.driver_id = :driver_id AND b.date >= :today 
             AND b.status IN ('pending', 'confirmed')
             ORDER BY b.date, b.time LIMIT 10",
            ['driver_id' => $driver['id'], 'today' => $today]
        );

        // Estatísticas do mês
        $monthStats = $this->db->fetchOne(
            "SELECT COUNT(*) as total, 
             SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as revenue,
             SUM(CASE WHEN status = 'completed' THEN commission_value ELSE 0 END) as commission
             FROM " . DB_PREFIX . "bookings 
             WHERE driver_id = :driver_id AND date BETWEEN :start AND :end",
            ['driver_id' => $driver['id'], 'start' => $startMonth, 'end' => $endMonth]
        );

        $this->setTitle('Dashboard');
        $this->setData('driver', $driver);
        $this->setData('todayBookings', $todayBookings);
        $this->setData('upcomingBookings', $upcomingBookings);
        $this->setData('monthStats', $monthStats);
        $this->view('dashboard.driver');
    }

    private function clientDashboard(): void
    {
        $userId = Session::getUserId();
        
        // Busca o cliente vinculado ao usuário
        $sql = "SELECT * FROM " . DB_PREFIX . "clients WHERE user_id = :user_id LIMIT 1";
        $client = $this->db->fetchOne($sql, ['user_id' => $userId]);

        if (!$client) {
            $this->setTitle('Dashboard');
            $this->setData('error', 'Perfil de cliente não encontrado.');
            $this->view('dashboard.client');
            return;
        }

        $bookingModel = new BookingModel();
        $today = date('Y-m-d');

        // Próximos serviços
        $upcomingBookings = $this->db->fetchAll(
            "SELECT b.*, d.name as driver_name, v.model as vehicle_model
             FROM " . DB_PREFIX . "bookings b
             LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
             LEFT JOIN " . DB_PREFIX . "vehicles v ON v.id = b.vehicle_id
             WHERE b.client_id = :client_id AND b.date >= :today 
             AND b.status IN ('pending', 'confirmed', 'in_progress')
             ORDER BY b.date, b.time LIMIT 10",
            ['client_id' => $client['id'], 'today' => $today]
        );

        // Histórico recente
        $recentBookings = $this->db->fetchAll(
            "SELECT b.*, d.name as driver_name
             FROM " . DB_PREFIX . "bookings b
             LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
             WHERE b.client_id = :client_id AND b.status = 'completed'
             ORDER BY b.date DESC, b.time DESC LIMIT 10",
            ['client_id' => $client['id']]
        );

        $this->setTitle('Dashboard');
        $this->setData('client', $client);
        $this->setData('upcomingBookings', $upcomingBookings);
        $this->setData('recentBookings', $recentBookings);
        $this->view('dashboard.client');
    }
}
