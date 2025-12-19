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
                    pc.category_name as category,
                    COUNT(i.productID) as total_products,
                    SUM(i.quantity) as remaining_quantity,
                    COUNT(CASE WHEN (i.status IS NULL OR i.status != 'deleted') THEN 1 END) as active_products
                  FROM inventory i
                  LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                  WHERE (i.status IS NULL OR i.status != 'deleted')
                  GROUP BY pc.category_name
                  ORDER BY pc.category_name
                  LIMIT 4";
        
        $results = $this->db->read($query);

        // Calculate sold quantity and percentage for each category
        $summaryWithSales = [];
        foreach ($results as $row) {
            $soldQty = $this->getSoldQuantityByCategory($row['category']);
            $totalStock = $row['remaining_quantity'] + $soldQty;
            $percentage = $totalStock > 0 ? round(($row['remaining_quantity'] / $totalStock) * 100, 2) : 0;

            $summaryWithSales[] = [
                'category' => $row['category'] ?? 'Uncategorized',
                'sold_quantity' => $soldQty,
                'remaining_quantity' => $row['remaining_quantity'],
                'percentage' => $percentage
            ];
        }

        return $summaryWithSales;
    }

    // Get sold quantity by category from sales (last 30 days)
    private function getSoldQuantityByCategory($category) {
        if (empty($category)) {
            $category = 'Uncategorized';
        }
        
        $query = "SELECT 
                    COALESCE(SUM(si.quantity_sold), 0) as sold_quantity
                  FROM sales_items si
                  JOIN sales_transactions st ON si.transaction_id = st.transaction_id
                  JOIN inventory i ON si.product_id = i.productID
                  LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                  WHERE pc.category_name = ?
                  AND st.status = 'completed'
                  AND DATE(st.date_time) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['sold_quantity'] ?? 0;
    }

    // Get inventory summary (total quantity in hand and to be received)
    public function getInventorySummary() {
        $query = "SELECT 
                    SUM(quantity) as quantity_in_hand,
                    COUNT(*) as total_products
                  FROM inventory 
                  WHERE (status IS NULL OR status != 'deleted')";
        
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
                  AND (status IS NULL OR status != 'deleted')";
        
        $result = $this->db->readOne($query);
        
        // Return estimated quantity to be received
        return max(0, $result['total_needed'] ?? 0);
    }

    // Get recent transactions (limit to 7 for display)
    public function getRecentTransactions($limit = 7) {
        $query = "SELECT 
                    st.transaction_id,
                    st.date_time,
                    GROUP_CONCAT(si.product_name SEPARATOR ', ') as products,
                    st.total_amount as order_value,
                    SUM(si.quantity_sold) as quantity_sold,
                    st.customer_name,
                    st.payment_method
                  FROM sales_transactions st
                  JOIN sales_items si ON st.transaction_id = si.transaction_id
                  WHERE st.status = 'completed'
                  GROUP BY st.transaction_id
                  ORDER BY st.date_time DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        return $transactions;
    }

    // Get sales summary (monthly data for the current year)
    public function getSalesSummary() {
        $query = "
            SELECT 
                MONTH(st.date_time) AS month_number,
                DATE_FORMAT(st.date_time, '%M') AS month_name,
                SUM(CAST(st.total_amount AS DECIMAL(10,2))) AS total_sales,
                SUM(CAST(si.unit_price AS DECIMAL(10,2)) * CAST(si.quantity_sold AS UNSIGNED)) AS total_purchase_cost
            FROM sales_transactions st
            JOIN sales_items si ON st.transaction_id = si.transaction_id
            WHERE st.status = 'completed'
            AND YEAR(st.date_time) = YEAR(CURDATE())
            GROUP BY month_number, month_name
            ORDER BY month_number ASC
        ";

        $rows = $this->db->read($query);

        $labels = [];
        $sales = [];
        $purchase_cost = [];

        if (!empty($rows) && is_array($rows)) {
            foreach ($rows as $row) {
                $labels[] = $row['month_name'];
                $sales[] = (float)$row['total_sales'];
                $purchase_cost[] = (float)$row['total_purchase_cost'];
            }
        }

        return [
            "labels" => $labels,
            "sales" => $sales,
            "purchase_cost" => $purchase_cost
        ];
    }

    // Get low stock items (limit to 4 for display)
    public function getLowStockItems($threshold = 20, $limit = 4) {
        $query = "SELECT 
                    i.id,
                    i.productID,
                    i.productName,
                    i.quantity,
                    i.unit,
                    pc.category_name as category,
                    i.price,
                    i.image
                  FROM inventory i
                  LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                  WHERE i.quantity <= ? 
                  AND (i.status IS NULL OR i.status != 'deleted')
                  ORDER BY i.quantity ASC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $threshold, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }

    // Get all low stock items count
    public function getLowStockCount($threshold = 20) {
        $query = "SELECT COUNT(*) as count
                  FROM inventory 
                  WHERE quantity <= ? 
                  AND (status IS NULL OR status != 'deleted')";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }

    // Close database connection
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>