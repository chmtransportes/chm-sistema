<?php
/**
 * CHM Sistema - Model de Motoristas
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Drivers;

use CHM\Core\Model;

class DriverModel extends Model
{
    protected string $table = 'drivers';
    protected array $fillable = [
        'user_id', 'name', 'driver_number', 'document', 'rg', 'cnh', 'cnh_category', 'cnh_expiry',
        'birth_date', 'email', 'phone', 'phone2', 'whatsapp', 'address', 'address_number',
        'address_complement', 'neighborhood', 'city', 'state', 'zipcode',
        'pix_key', 'bank_name', 'bank_agency', 'bank_account',
        'commission_rate', 'type', 'photo', 'car_photo', 'cnh_photo', 'notes', 'status'
    ];

    // Obtém o próximo número de motorista (começa em 130)
    public function getNextDriverNumber(): int
    {
        try {
            $sql = "SELECT MAX(CAST(driver_number AS UNSIGNED)) as max_num FROM {$this->getTable()} WHERE driver_number IS NOT NULL";
            $result = $this->db->fetch($sql);
            $maxNum = (int)($result['max_num'] ?? 0);
            return max($maxNum + 1, 130);
        } catch (\Exception $e) {
            return 130;
        }
    }

    public function findByDocument(string $document): ?array
    {
        $document = preg_replace('/\D/', '', $document);
        return $this->findBy('document', $document);
    }

    public function findByCnh(string $cnh): ?array
    {
        return $this->findBy('cnh', $cnh);
    }

    public function getActive(): array
    {
        return $this->where(['status' => 'active', 'deleted_at' => null], 'name', 'ASC');
    }

    public function getForSelect(): array
    {
        $sql = "SELECT id, name, phone FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getWithCnhExpiring(int $days = 30): array
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                AND cnh_expiry <= :date
                ORDER BY cnh_expiry ASC";
        return $this->db->fetchAll($sql, ['date' => $date]);
    }

    public function getWithBookingsStats(string $startDate, string $endDate): array
    {
        $sql = "SELECT d.*, 
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN b.total ELSE 0 END) as total_revenue,
                SUM(CASE WHEN b.status = 'completed' THEN b.commission_value ELSE 0 END) as total_commission
                FROM {$this->getTable()} d
                LEFT JOIN " . DB_PREFIX . "bookings b ON b.driver_id = d.id 
                    AND b.date BETWEEN :start AND :end
                WHERE d.deleted_at IS NULL
                GROUP BY d.id
                ORDER BY d.name ASC";
        return $this->db->fetchAll($sql, ['start' => $startDate, 'end' => $endDate]);
    }

    public function getDriverClosing(int $driverId, string $startDate, string $endDate): array
    {
        $sql = "SELECT b.*, c.name as client_name
                FROM " . DB_PREFIX . "bookings b
                INNER JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                WHERE b.driver_id = :driver_id 
                AND b.date BETWEEN :start AND :end
                AND b.status = 'completed'
                ORDER BY b.date ASC, b.time ASC";
        return $this->db->fetchAll($sql, [
            'driver_id' => $driverId,
            'start' => $startDate,
            'end' => $endDate
        ]);
    }

    public function getAvailableForDate(string $date, string $time): array
    {
        $sql = "SELECT d.* FROM {$this->getTable()} d
                WHERE d.status = 'active' AND d.deleted_at IS NULL
                AND d.id NOT IN (
                    SELECT driver_id FROM " . DB_PREFIX . "bookings 
                    WHERE date = :date 
                    AND status IN ('pending', 'confirmed', 'in_progress')
                    AND driver_id IS NOT NULL
                    AND (
                        (time <= :time AND (end_time IS NULL OR end_time >= :time))
                        OR (time >= :time AND time <= ADDTIME(:time, '02:00:00'))
                    )
                )
                ORDER BY d.name ASC";
        return $this->db->fetchAll($sql, ['date' => $date, 'time' => $time]);
    }
}
