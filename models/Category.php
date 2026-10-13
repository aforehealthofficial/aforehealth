<?php
namespace Models;

use PDO;

class Category extends BaseModel {
    protected string $table = 'categories';

    public function allActive(): array {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'published'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.id ASC
        ");
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        $cat = $stmt->fetch();
        if (!$cat) return null;

        $cat['subcategories'] = $this->getSubcategories($cat['id']);
        return $cat;
    }

    public function getSubcategories(int $categoryId): array {
        $stmt = $this->db->prepare("SELECT * FROM subcategories WHERE category_id = ? AND status = 'active' ORDER BY sort_order ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
}
