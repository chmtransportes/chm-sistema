<?php
/**
 * CHM Sistema - Model Base
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];
    protected bool $timestamps = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function getTable(): string
    {
        return DB_PREFIX . $this->table;
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$this->primaryKey} = :id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['id' => $id]);
        return $result ? $this->hideFields($result) : null;
    }

    public function findBy(string $field, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$field} = :value LIMIT 1";
        $result = $this->db->fetchOne($sql, ['value' => $value]);
        return $result ? $this->hideFields($result) : null;
    }

    public function all(string $orderBy = 'id', string $order = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->getTable()} ORDER BY {$orderBy} {$order}";
        return array_map([$this, 'hideFields'], $this->db->fetchAll($sql));
    }

    public function where(array $conditions, string $orderBy = 'id', string $order = 'DESC'): array
    {
        $where = [];
        foreach (array_keys($conditions) as $field) {
            $where[] = "{$field} = :{$field}";
        }
        $sql = "SELECT * FROM {$this->getTable()} WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy} {$order}";
        return array_map([$this, 'hideFields'], $this->db->fetchAll($sql, $conditions));
    }

    public function first(array $conditions): ?array
    {
        $where = [];
        foreach (array_keys($conditions) as $field) {
            $where[] = "{$field} = :{$field}";
        }
        $sql = "SELECT * FROM {$this->getTable()} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $result = $this->db->fetchOne($sql, $conditions);
        return $result ? $this->hideFields($result) : null;
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->update($this->table, $data, "{$this->primaryKey} = :where_id", ['where_id' => $id]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTable()}";
        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $field) {
                $where[] = "{$field} = :{$field}";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return (int) $this->db->fetchColumn($sql, $conditions);
    }

    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    public function paginate(int $page = 1, int $perPage = 15, array $conditions = [], string $orderBy = 'id', string $order = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        $pages = ceil($total / $perPage);

        $sql = "SELECT * FROM {$this->getTable()}";
        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $field) {
                $where[] = "{$field} = :{$field}";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY {$orderBy} {$order} LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data' => array_map([$this, 'hideFields'], $this->db->fetchAll($sql, $conditions)),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $pages,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        return $data;
    }

    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    public function beginTransaction(): bool { return $this->db->beginTransaction(); }
    public function commit(): bool { return $this->db->commit(); }
    public function rollback(): bool { return $this->db->rollback(); }
}
