<?php
namespace Models;

use PDO;

abstract class BaseModel {
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBy(string $column, $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = 'id DESC'): array {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function count(string $where = '1=1', array $params = []): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): int {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        foreach ($data as $col => $val) {
            $fields[] = "`{$col}` = ?";
        }
        $fieldsSql = implode(', ', $fields);
        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE `{$this->table}` SET {$fieldsSql} WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }
}
