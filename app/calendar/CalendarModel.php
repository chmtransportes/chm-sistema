<?php
/**
 * CHM Sistema - Model de Calendário/Agenda
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 17:50
 * @version 2.5.0
 */

namespace CHM\Calendar;

use CHM\Core\Model;
use CHM\Core\Database;

class CalendarModel extends Model
{
    protected string $table = 'calendar_events';
    protected array $fillable = [
        'google_uid', 'title', 'description', 'location',
        'start_datetime', 'end_datetime', 'all_day', 'color',
        'event_type', 'status', 'source', 'booking_id',
        'client_id', 'driver_id', 'user_id',
        'notify_email', 'notify_client', 'notify_driver',
        'email_sent', 'email_sent_at',
        'recurrence_rule', 'recurrence_end',
        'created_at', 'updated_at'
    ];

    // Feriados nacionais brasileiros (fixos e móveis)
    public function getBrazilianHolidays(int $year): array
    {
        $holidays = [];
        
        // Feriados fixos
        $fixedHolidays = [
            ['date' => "{$year}-01-01", 'title' => 'Confraternização Universal', 'type' => 'national'],
            ['date' => "{$year}-04-21", 'title' => 'Tiradentes', 'type' => 'national'],
            ['date' => "{$year}-05-01", 'title' => 'Dia do Trabalho', 'type' => 'national'],
            ['date' => "{$year}-09-07", 'title' => 'Independência do Brasil', 'type' => 'national'],
            ['date' => "{$year}-10-12", 'title' => 'Nossa Senhora Aparecida', 'type' => 'national'],
            ['date' => "{$year}-11-02", 'title' => 'Finados', 'type' => 'national'],
            ['date' => "{$year}-11-15", 'title' => 'Proclamação da República', 'type' => 'national'],
            ['date' => "{$year}-12-25", 'title' => 'Natal', 'type' => 'national'],
        ];
        
        // Calcula Páscoa (algoritmo de Gauss)
        $easter = $this->calculateEaster($year);
        
        // Feriados móveis baseados na Páscoa
        $mobileHolidays = [
            ['date' => date('Y-m-d', strtotime($easter . ' -47 days')), 'title' => 'Carnaval', 'type' => 'national'],
            ['date' => date('Y-m-d', strtotime($easter . ' -46 days')), 'title' => 'Carnaval', 'type' => 'national'],
            ['date' => date('Y-m-d', strtotime($easter . ' -2 days')), 'title' => 'Sexta-feira Santa', 'type' => 'national'],
            ['date' => $easter, 'title' => 'Páscoa', 'type' => 'national'],
            ['date' => date('Y-m-d', strtotime($easter . ' +60 days')), 'title' => 'Corpus Christi', 'type' => 'national'],
        ];
        
        $holidays = array_merge($fixedHolidays, $mobileHolidays);
        
        // Ordena por data
        usort($holidays, fn($a, $b) => strcmp($a['date'], $b['date']));
        
        return $holidays;
    }

    // Algoritmo de Gauss para calcular a Páscoa
    private function calculateEaster(int $year): string
    {
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    // Busca eventos do calendário por período
    public function getEventsByDateRange(string $start, string $end, ?int $userId = null): array
    {
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE DATE(start_datetime) BETWEEN :start AND :end";
        $params = ['start' => $start, 'end' => $end];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $sql .= " ORDER BY start_datetime ASC";
        
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    // Busca eventos por data
    public function getEventsByDate(string $date): array
    {
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE DATE(start_datetime) = :date
                ORDER BY start_datetime ASC";
        return $this->db->fetchAll($sql, ['date' => $date]) ?: [];
    }

    // Cria evento de agenda
    public function createEvent(array $data): ?int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (empty($data['google_uid'])) {
            $data['google_uid'] = 'chm_' . uniqid() . '_' . time();
        }
        
        return $this->create($data);
    }

    // Atualiza evento
    public function updateEvent(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    // Busca evento por UID
    public function findByUid(string $uid): ?array
    {
        return $this->findBy('google_uid', $uid);
    }

    // Verifica conflito de horário
    public function hasConflict(string $start, string $end, ?int $excludeId = null, ?int $driverId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->getTable()} 
                WHERE ((start_datetime < :end AND end_datetime > :start))
                AND status != 'cancelled'";
        $params = ['start' => $start, 'end' => $end];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        if ($driverId) {
            $sql .= " AND driver_id = :driver_id";
            $params['driver_id'] = $driverId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    // Marca e-mail como enviado
    public function markEmailSent(int $id): bool
    {
        return $this->update($id, [
            'email_sent' => 1,
            'email_sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Busca eventos para envio de notificação
    public function getEventsForNotification(): array
    {
        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "SELECT e.*, c.email as client_email, c.name as client_name,
                d.email as driver_email, d.name as driver_name
                FROM {$this->getTable()} e
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = e.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = e.driver_id
                WHERE e.start_datetime BETWEEN :now AND :future
                AND e.email_sent = 0
                AND e.notify_email = 1
                AND e.status != 'cancelled'";
        
        return $this->db->fetchAll($sql, ['now' => $now, 'future' => $future]) ?: [];
    }

    // Estatísticas do calendário
    public function getStats(string $start, string $end): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN event_type = 'booking' THEN 1 ELSE 0 END) as bookings,
                SUM(CASE WHEN event_type = 'personal' THEN 1 ELSE 0 END) as personal
                FROM {$this->getTable()}
                WHERE DATE(start_datetime) BETWEEN :start AND :end";
        
        return $this->db->fetchOne($sql, ['start' => $start, 'end' => $end]) ?: [];
    }
}
