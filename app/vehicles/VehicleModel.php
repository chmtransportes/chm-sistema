<?php
/**
 * CHM Sistema - Model de Veículos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Vehicles;

use CHM\Core\Model;

class VehicleModel extends Model
{
    protected string $table = 'vehicles';
    protected array $fillable = [
        'plate', 'brand', 'model', 'year', 'color', 'renavam', 'chassis',
        'fuel', 'category', 'seats', 'owner', 'owner_name', 'owner_document',
        'insurance_company', 'insurance_policy', 'insurance_expiry',
        'ipva_paid', 'licensing_date', 'last_maintenance', 'next_maintenance',
        'odometer', 'photo', 'notes', 'status'
    ];

    public function findByPlate(string $plate): ?array
    {
        $plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate));
        return $this->findBy('plate', $plate);
    }

    public function getActive(): array
    {
        return $this->where(['status' => 'active', 'deleted_at' => null], 'model', 'ASC');
    }

    public function getForSelect(): array
    {
        $sql = "SELECT id, plate, brand, model, color FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                ORDER BY model ASC";
        return $this->db->fetchAll($sql);
    }

    public function getWithInsuranceExpiring(int $days = 30): array
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                AND insurance_expiry IS NOT NULL AND insurance_expiry <= :date
                ORDER BY insurance_expiry ASC";
        return $this->db->fetchAll($sql, ['date' => $date]);
    }

    public function getWithMaintenanceDue(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                AND next_maintenance IS NOT NULL AND next_maintenance <= :date
                ORDER BY next_maintenance ASC";
        return $this->db->fetchAll($sql, ['date' => $today]);
    }

    public function getAvailableForDate(string $date, string $time): array
    {
        $sql = "SELECT v.* FROM {$this->getTable()} v
                WHERE v.status = 'active' AND v.deleted_at IS NULL
                AND v.id NOT IN (
                    SELECT vehicle_id FROM " . DB_PREFIX . "bookings 
                    WHERE date = :date 
                    AND status IN ('pending', 'confirmed', 'in_progress')
                    AND vehicle_id IS NOT NULL
                )
                ORDER BY v.model ASC";
        return $this->db->fetchAll($sql, ['date' => $date]);
    }

    public function getByCategory(string $category): array
    {
        return $this->where(['category' => $category, 'status' => 'active']);
    }

    public function updateOdometer(int $id, int $odometer): bool
    {
        return $this->update($id, ['odometer' => $odometer]);
    }
}
