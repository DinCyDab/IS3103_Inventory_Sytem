<?php
require_once "./mvc/api/config.php";

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = new Config();
    }

    // Get stock summary by category (limit to 4 for display)
    public function getStockSummary() {
        $query = "SELECT 
                    category,
                    COUNT(*) as total_products,
                    SUM(quantity) as remaining_quantity,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products
                  FROM inventory 
                  WHERE status = 'active'
                  GROUP BY category
                  ORDER BY category
                  LIMIT 4";
        
        $results = $this->db->read($query);

        // Calculate sold quantity and percentage for each category
        $summaryWithSales = [];
        foreach ($results as $row) {
            $soldQty = $this->getSoldQuantityByCategory($row['category']);
            $totalStock = $row['remaining_quantity'] + $soldQty;
            $percentage = $totalStock > 0 ? round(($row['remaining_quantity'] / $totalStock) * 100, 2) : 0;

            $summaryWithSales[] = [
                'category' => $row['category'],
                'sold_quantity' => $soldQty,
                'remaining_quantity' => $row['remaining_quantity'],
                'percentage' => $percentage
            ];
        }

        return $summaryWithSales;
    }

    // Get sold quantity by category from sales report (last 30 days)
    private function getSoldQuantityByCategory($category) {
        $category = $this->db->getConnection()->real_escape_string($category);
        
        $query = "SELECT 
                    COALESCE(SUM(sr.quantity_sold), 0) as sold_quantity
                  FROM salesreport sr
                  INNER JOIN inventory i ON sr.products = i.productName
                  WHERE i.category = '$category'
                  AND DATE(sr.date_time) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        
        $result = $this->db->readOne($query);
        
        return $result['sold_quantity'] ?? 0;
    }

    // Get inventory summary (total quantity in hand and to be received)
    public function getInventorySummary() {
        $query = "SELECT 
                    SUM(quantity) as quantity_in_hand,
                    COUNT(*) as total_products
                  FROM inventory 
                  WHERE status = 'active'";
        
        $result = $this->db->readOne($query);

        // Calculate to be received (items that need reordering)
        $toBeReceived = $this->getToBeReceivedQuantity();

        return [
            'quantity_in_hand' => $result['quantity_in_hand'] ?? 0,
            'to_be_received' => $toBeReceived,
            'total_products' => $result['total_products'] ?? 0
        ];
    }

    // Calculate items to be received based on low stock items
    private function getToBeReceivedQuantity() {
        // Count low stock items (quantity <= 20) and estimate reorder quantity
        $query = "SELECT 
                    COUNT(*) as low_stock_count,
                    SUM(GREATEST(20 - quantity, 0)) as total_needed
                  FROM inventory 
                  WHERE quantity <= 20 
                  AND quantity > 0
                  AND status = 'active'";
        
        $result = $this->db->readOne($query);
        
        // Return estimated quantity to be received
        return max(0, $result['total_needed'] ?? 0);
    }

    // Get recent transactions (limit to 7 for display)
    public function getRecentTransactions($limit = 7) {
        $query = "SELECT 
                    transaction_id,
                    date_time,
                    products,
                    order_value,
                    quantity_sold,
                    customer_name,
                    payment_method
                  FROM salesreport 
                  ORDER BY date_time DESC 
                  LIMIT $limit";
        
        return $this->db->read($query);
    }

    // Get sales summary (monthly data for the last 5 months)
    public function getSalesSummary() {
        $query = "SELECT 
                    DATE_FORMAT(date_time, '%Y-%m') as month,
                    DATE_FORMAT(date_time, '%b') as month_name,
                    SUM(order_value) as total_sales,
                    SUM(quantity_sold) as total_quantity,
                    COUNT(*) as total_transactions
                  FROM salesreport 
                  WHERE date_time >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                  GROUP BY DATE_FORMAT(date_time, '%Y-%m')
                  ORDER BY month ASC";
        
        return $this->db->read($query);
    }

    // Get purchase summary for comparison (if you have a purchases table)
    public function getPurchaseSummary() {
        // This is a placeholder - adjust based on your purchases/stock-in table
        // For now, returning empty array since you might not have a purchases table yet
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    DATE_FORMAT(created_at, '%b') as month_name,
                    COUNT(*) as total_purchases,
                    SUM(quantity * price) as total_value
                  FROM inventory 
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month ASC";
        
        // Check if created_at column exists, if not return empty array
        $result = $this->db->read($query);
        return $result ? $result : [];
    }

    // Get low stock items (limit to 4 for display)
    public function getLowStockItems($threshold = 20, $limit = 4) {
        $query = "SELECT 
                    id,
                    productName,
                    quantity,
                    unit,
                    category,
                    price,
                    image
                  FROM inventory 
                  WHERE quantity <= $threshold 
                  AND status = 'active'
                  ORDER BY quantity ASC 
                  LIMIT $limit";
        
        return $this->db->read($query);
    }

    // Get all low stock items count
    public function getLowStockCount($threshold = 20) {
        $query = "SELECT COUNT(*) as count
                  FROM inventory 
                  WHERE quantity <= $threshold 
                  AND status = 'active'";
        
        $result = $this->db->readOne($query);
        
        return $result['count'] ?? 0;
    }

    // Close database connection
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>