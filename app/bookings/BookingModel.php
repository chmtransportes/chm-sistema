<?php
/**
 * CHM Sistema - Model de Agendamentos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Bookings;

use CHM\Core\Model;

class BookingModel extends Model
{
    protected string $table = 'bookings';
    protected array $fillable = [
        'code', 'client_id', 'driver_id', 'vehicle_id', 'service_type',
        'date', 'time', 'end_date', 'end_time', 'origin', 'destination', 'stops',
        'passengers', 'passenger_name', 'passenger_phone',
        'flight_number', 'flight_origin', 'flight_arrival',
        'distance', 'duration', 'value', 'extras', 'discount', 'total',
        'commission_rate', 'commission_value', 'payment_method', 'payment_status',
        'paid_at', 'status', 'cancelled_at', 'cancelled_reason',
        'notes', 'internal_notes', 'voucher_sent', 'voucher_sent_at', 'created_by'
    ];

    public function generateCode(): string
    {
        $prefix = 'CHM';
        $date = date('ymd');
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 10) AS UNSIGNED)) as max_num 
                FROM {$this->getTable()} WHERE code LIKE :prefix";
        $result = $this->db->fetchOne($sql, ['prefix' => "{$prefix}{$date}%"]);
        $num = ($result['max_num'] ?? 0) + 1;
        return $prefix . $date . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', strtoupper($code));
    }

    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT b.*, c.name as client_name, c.phone as client_phone, c.email as client_email,
                d.name as driver_name, d.phone as driver_phone,
                v.plate as vehicle_plate, v.model as vehicle_model, v.color as vehicle_color
                FROM {$this->getTable()} b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                LEFT JOIN " . DB_PREFIX . "vehicles v ON v.id = b.vehicle_id
                WHERE b.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function getByDate(string $date): array
    {
        $sql = "SELECT b.*, c.name as client_name, d.name as driver_name, v.model as vehicle_model
                FROM {$this->getTable()} b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                LEFT JOIN " . DB_PREFIX . "vehicles v ON v.id = b.vehicle_id
                WHERE b.date = :date AND b.deleted_at IS NULL
                ORDER BY b.time ASC";
        return $this->db->fetchAll($sql, ['date' => $date]);
    }

    public function getByDateRange(string $startDate, string $endDate, ?int $driverId = null, ?int $clientId = null): array
    {
        $sql = "SELECT b.*, c.name as client_name, d.name as driver_name
                FROM {$this->getTable()} b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                WHERE b.date BETWEEN :start AND :end AND b.deleted_at IS NULL";
        $params = ['start' => $startDate, 'end' => $endDate];
        
        if ($driverId) {
            $sql .= " AND b.driver_id = :driver_id";
            $params['driver_id'] = $driverId;
        }
        if ($clientId) {
            $sql .= " AND b.client_id = :client_id";
            $params['client_id'] = $clientId;
        }
        
        $sql .= " ORDER BY b.date ASC, b.time ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getByDriver(int $driverId, ?string $status = null): array
    {
        $conditions = ['driver_id' => $driverId];
        if ($status) $conditions['status'] = $status;
        return $this->where($conditions, 'date', 'DESC');
    }

    public function getByClient(int $clientId, ?string $status = null): array
    {
        $conditions = ['client_id' => $clientId];
        if ($status) $conditions['status'] = $status;
        return $this->where($conditions, 'date', 'DESC');
    }

    public function getUpcoming(int $limit = 10): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT b.*, c.name as client_name, d.name as driver_name
                FROM {$this->getTable()} b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                WHERE b.date >= :today AND b.status IN ('pending', 'confirmed')
                AND b.deleted_at IS NULL
                ORDER BY b.date ASC, b.time ASC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql, ['today' => $today]);
    }

    public function getPending(): array
    {
        return $this->where(['status' => 'pending'], 'date', 'ASC');
    }

    public function updateStatus(int $id, string $status): bool
    {
        $data = ['status' => $status];
        if ($status === 'cancelled') {
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        }
        return $this->update($id, $data);
    }

    public function calculateCommission(float $total, float $rate): float
    {
        return round($total * ($rate / 100), 2);
    }

    public function getForCalendar(string $startDate, string $endDate): array
    {
        $sql = "SELECT b.id, b.code, b.date, b.time, b.end_time, b.origin, b.destination,
                b.status, b.service_type, c.name as client_name, d.name as driver_name,
                d.id as driver_id
                FROM {$this->getTable()} b
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                LEFT JOIN " . DB_PREFIX . "drivers d ON d.id = b.driver_id
                WHERE b.date BETWEEN :start AND :end AND b.deleted_at IS NULL
                ORDER BY b.date ASC, b.time ASC";
        return $this->db->fetchAll($sql, ['start' => $startDate, 'end' => $endDate]);
    }

    public function getStats(string $startDate, string $endDate): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as revenue,
                SUM(CASE WHEN status = 'completed' THEN commission_value ELSE 0 END) as commissions
                FROM {$this->getTable()}
                WHERE date BETWEEN :start AND :end AND deleted_at IS NULL";
        return $this->db->fetchOne($sql, ['start' => $startDate, 'end' => $endDate]);
    }
}
