<?php
/**
 * CHM Sistema - Model de Clientes
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Clients;

use CHM\Core\Model;

class ClientModel extends Model
{
    protected string $table = 'clients';
    protected array $fillable = [
        'user_id', 'type', 'name', 'client_number', 'trade_name', 'document', 'rg',
        'email', 'phone', 'phone2', 'whatsapp', 'address', 'address_number',
        'address_complement', 'neighborhood', 'city', 'state', 'zipcode', 'notes', 'status'
    ];

    // Obtém o próximo número de cliente (começa em 23780)
    public function getNextClientNumber(): int
    {
        try {
            $sql = "SELECT MAX(CAST(client_number AS UNSIGNED)) as max_num FROM {$this->getTable()} WHERE client_number IS NOT NULL";
            $result = $this->db->fetch($sql);
            $maxNum = (int)($result['max_num'] ?? 0);
            return max($maxNum + 1, 23780);
        } catch (\Exception $e) {
            return 23780;
        }
    }

    public function findByDocument(string $document): ?array
    {
        $document = preg_replace('/\D/', '', $document);
        return $this->findBy('document', $document);
    }

    public function getActive(): array
    {
        return $this->where(['status' => 'active', 'deleted_at' => null], 'name', 'ASC');
    }

    public function search(string $term): array
    {
        $sql = "SELECT * FROM {$this->getTable()} 
                WHERE deleted_at IS NULL 
                AND (name LIKE :term OR document LIKE :term OR email LIKE :term OR phone LIKE :term)
                ORDER BY name ASC";
        return $this->db->fetchAll($sql, ['term' => "%{$term}%"]);
    }

    public function getWithBookingsCount(): array
    {
        $sql = "SELECT c.*, COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN b.total ELSE 0 END) as total_revenue
                FROM {$this->getTable()} c
                LEFT JOIN " . DB_PREFIX . "bookings b ON b.client_id = c.id
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getTopClients(int $limit = 10): array
    {
        $sql = "SELECT c.*, COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'completed' THEN b.total ELSE 0 END) as total_revenue
                FROM {$this->getTable()} c
                INNER JOIN " . DB_PREFIX . "bookings b ON b.client_id = c.id
                WHERE c.deleted_at IS NULL AND b.status = 'completed'
                GROUP BY c.id
                ORDER BY total_revenue DESC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }

    public function getForSelect(): array
    {
        $sql = "SELECT id, name, document FROM {$this->getTable()} 
                WHERE status = 'active' AND deleted_at IS NULL 
                ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
}
