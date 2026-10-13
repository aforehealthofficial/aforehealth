<?php
namespace Models;

use PDO;

class FlashSale extends BaseModel {
    protected string $table = 'flash_sales';

    /**
     * Get currently active flash sale (strictly validates current datetime)
     */
    public function getActiveSale(): ?array {
        $stmt = $this->db->query("
            SELECT * FROM flash_sales 
            WHERE status = 'active' AND NOW() BETWEEN start_at AND end_at 
            ORDER BY end_at ASC 
            LIMIT 1
        ");
        $sale = $stmt->fetch();
        if (!$sale) return null;

        // Calculate seconds remaining for timer
        $sale['seconds_left'] = max(0, strtotime($sale['end_at']) - time());
        $sale['products'] = $this->getProducts($sale['id']);
        return $sale;
    }

    public function getProducts(int $flashSaleId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, fsp.discount_price as custom_flash_price,
                   COALESCE(fsp.discount_price, ROUND(p.price * (1 - (fs.discount_percent / 100)), 0)) as flash_price,
                   fs.discount_percent as sale_discount_percent
            FROM flash_sale_products fsp
            JOIN products p ON fsp.product_id = p.id
            JOIN flash_sales fs ON fsp.flash_sale_id = fs.id
            WHERE fsp.flash_sale_id = ? AND p.status = 'published'
        ");
        $stmt->execute([$flashSaleId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if a product has an active flash sale discount right now
     */
    public function getProductFlashDiscount(int $productId): ?array {
        $stmt = $this->db->prepare("
            SELECT fs.id as flash_sale_id, fs.title as flash_sale_title, fs.end_at, fs.discount_percent,
                   fsp.discount_price
            FROM flash_sale_products fsp
            JOIN flash_sales fs ON fsp.flash_sale_id = fs.id
            WHERE fsp.product_id = ? AND fs.status = 'active' AND NOW() BETWEEN fs.start_at AND fs.end_at
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch() ?: null;
    }

    public function getAllForAdmin(): array {
        $stmt = $this->db->query("
            SELECT fs.*, 
                   (SELECT COUNT(*) FROM flash_sale_products WHERE flash_sale_id = fs.id) as product_count,
                   (CASE 
                       WHEN NOW() < fs.start_at THEN 'UPCOMING'
                       WHEN NOW() BETWEEN fs.start_at AND fs.end_at THEN 'RUNNING'
                       ELSE 'EXPIRED'
                   END) as live_status
            FROM flash_sales fs
            ORDER BY fs.id DESC
        ");
        return $stmt->fetchAll();
    }
}
