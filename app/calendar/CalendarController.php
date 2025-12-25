<?php
/**
 * CHM Sistema - Controller de Calendário
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Calendar;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Bookings\BookingModel;

class CalendarController extends Controller
{
    private BookingModel $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new BookingModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->setTitle('Agenda');
        $this->view('calendar.index');
    }

    public function events(): void
    {
        $this->requireAuth();
        
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        $driverId = Session::isDriver() ? $this->getDriverId() : $this->input('driver_id');
        $clientId = Session::isClient() ? $this->getClientId() : $this->input('client_id');

        $bookings = $this->bookingModel->getByDateRange($start, $end, $driverId, $clientId);

        $events = [];
        $colors = [
            'pending' => '#ffc107',
            'confirmed' => '#17a2b8',
            'in_progress' => '#007bff',
            'completed' => '#28a745',
            'cancelled' => '#dc3545'
        ];

        foreach ($bookings as $b) {
            $events[] = [
                'id' => $b['id'],
                'title' => ($b['client_name'] ?? 'Cliente') . ' - ' . substr($b['time'], 0, 5),
                'start' => $b['date'] . 'T' . $b['time'],
                'end' => $b['end_time'] ? $b['date'] . 'T' . $b['end_time'] : null,
                'backgroundColor' => $colors[$b['status']] ?? '#6c757d',
                'borderColor' => $colors[$b['status']] ?? '#6c757d',
                'extendedProps' => [
                    'code' => $b['code'],
                    'status' => $b['status'],
                    'origin' => $b['origin'],
                    'destination' => $b['destination'] ?? '',
                    'driver' => $b['driver_name'] ?? '',
                    'client' => $b['client_name'] ?? ''
                ]
            ];
        }

        $this->json($events);
    }

    public function dayView(): void
    {
        $this->requireAuth();
        $date = $this->input('date', date('Y-m-d'));
        
        $bookings = $this->bookingModel->getByDate($date);
        
        $this->setTitle('Agenda - ' . date('d/m/Y', strtotime($date)));
        $this->setData('date', $date);
        $this->setData('bookings', $bookings);
        $this->view('calendar.day');
    }

    public function weekView(): void
    {
        $this->requireAuth();
        $startOfWeek = $this->input('start', date('Y-m-d', strtotime('monday this week')));
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));
        
        $bookings = $this->bookingModel->getByDateRange($startOfWeek, $endOfWeek);
        
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($startOfWeek . " +{$i} days"));
            $days[$date] = array_filter($bookings, fn($b) => $b['date'] === $date);
        }

        $this->setTitle('Agenda Semanal');
        $this->setData('startOfWeek', $startOfWeek);
        $this->setData('days', $days);
        $this->view('calendar.week');
    }

    private function getDriverId(): ?int
    {
        $userId = Session::getUserId();
        $sql = "SELECT id FROM " . DB_PREFIX . "drivers WHERE user_id = :user_id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['id'] : null;
    }

    private function getClientId(): ?int
    {
        $userId = Session::getUserId();
        $sql = "SELECT id FROM " . DB_PREFIX . "clients WHERE user_id = :user_id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['id'] : null;
    }
}
