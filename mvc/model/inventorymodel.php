<?php
require_once __DIR__ . "/../api/config.php";

class ProductModel{
    private $config;

    public function __construct(){
        $this->config = new Config(); // mysqli connection
    }

    // Base condition for active products
    private function activeCondition() {
        return "(status IS NULL OR status = '' OR LOWER(TRIM(status)) != 'deleted')";
    }

    // Get all products
    public function getAllProducts(){
        $stmt = $this->config->prepare("SELECT * FROM inventory WHERE " . $this->activeCondition() . " ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Paginated Method -- Limit and Offset
    public function getPaginatedProducts($limit, $offset, $categories = []){
        $sql = "SELECT * FROM inventory WHERE " . $this->activeCondition();
        $params = [];

        if(!empty($categories)){
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql .= " AND category IN ($placeholders)";
            $params = $categories;
        }

        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->config->prepare($sql);

        $types = str_repeat('s', count($categories)) . "ii"; // categories = string, limit & offset = int
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Total products count
    public function getTotalProductsCount($categories = []){
        $sql = "SELECT COUNT(*) as total FROM inventory WHERE " . $this->activeCondition();
        $params = [];

        if(!empty($categories)){
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql .= " AND category IN ($placeholders)";
            $params = $categories;
        }

        $stmt = $this->config->prepare($sql);
        if(!empty($params)){
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)$result['total'];
    }

    // Get Overall Inventory
    public function getOverviewStats(){
        $stats = [];

        $conn = $this->config->getConnection();

        // Total Categories
        $row = $conn->query("SELECT COUNT(DISTINCT category) AS totalCategories FROM inventory WHERE (status IS NULL OR TRIM(LOWER(status)) != 'deleted')")->fetch_assoc();
        $totalCategories = $row['totalCategories'] ?? 0;

        // Total Products + Revenue
        $row = $conn->query("
            SELECT
                COUNT(*) AS totalProducts,
                IFNULL(SUM(price * quantity), 0) AS totalValue
            FROM inventory
            WHERE (status IS NULL OR TRIM(LOWER(status)) != 'deleted')
        ")->fetch_assoc();
        $totalProducts = $row['totalProducts'] ?? 0;
        $totalValue = $row['totalValue'] ?? 0;

        // Low Stocks below 5(units)
        $row = $conn->query("
            SELECT COUNT(*) AS lowStocks
            FROM inventory
            WHERE quantity < 5 AND quantity > 0 AND (status IS NULL OR TRIM(LOWER(status)) != 'deleted')
        ")->fetch_assoc();
        $lowStocks = $row['lowStocks'] ?? 0;

        // Top Selling (If their is a sold column)
            $row = $conn->query("
                SELECT 
                    IFNULL(SUM(s.quantity_sold), 0) AS totalSold,
                    IFNULL(SUM(s.quantity_sold * i.price), 0) AS totalRevenue
                FROM salesreport s
                LEFT JOIN inventory i 
                    ON TRIM(LOWER(i.productName)) = TRIM(LOWER(s.products))
                WHERE " . $this->activeCondition() . "
            ")->fetch_assoc();

            $totalSellingQty = $row['totalSold'] ?? 0;
            $totalSellingValue = $row['totalRevenue'] ?? 0;

        // Building overviewStats array
        $overviewStats = [
            [
                "label" => "Categories", 
                "value" => $totalCategories, 
                "footer" => "Total Categories", 
                "highlight" => "blue"
            ],
            [
                "label" => "Total Products", 
                "value" => $totalProducts, 
                "footer" => "Total Products", 
                "extra" => "₱" . number_format($totalValue, 2), 
                "highlight" => "orange"
            ],
            [
                "label" => "Top Selling", 
                "value" => $totalSellingQty,  
                "footer" => "Products Sold", 
                "extra" => "₱" . number_format($totalSellingValue, 2), 
                "highlight" => "purple"
            ],
            [
                "label" => "Low Stocks", 
                "value" => $lowStocks, 
                "footer" => "Needs Restock", 
                "highlight" => "red"
            ]
        ];

        return $overviewStats;
    }

    // Utility -- Check if a column exists in a table
    private function columnExists($table, $column){
        // Sanitize identifiers to prevent SQL injection
        $table = preg_replace("/[^a-zA-Z0-9_]/", "", $table);
        $column = preg_replace("/[^a-zA-Z0-9_]/", "", $column);

        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
        $conn = $this->config->getConnection();
        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        return $result->num_rows > 0;
    }

    // Search Product
    public function searchProducts($q){
        $conn = $this->config->getConnection();

        $sql = "SELECT * FROM inventory 
                WHERE " . $this->activeCondition() . " 
                AND (productName LIKE ? OR category LIKE ?)
                ORDER BY id DESC";

        $stmt = $conn->prepare($sql);
        if(!$stmt){
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $param = "%" . $q . "%";
        $stmt->bind_param("ss", $param, $param);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Insert product
    public function addProduct($data){
        $stmt = $this->config->prepare("
            INSERT INTO inventory (productID, productName, quantity, unit, price, expiryDate, category, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->error);

        $expiryDateVar = $data['expiryDate'];

        $stmt->bind_param(
            "ssisdsss",
            $data['productID'],
            $data['productName'],
            $data['quantity'],
            $data['unit'],
            $data['price'],
            $expiryDateVar,
            $data['category'],
            $data['image']
        );

        if(!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        return true;
    }

    // Update product
    public function updateProduct($data){
        $stmt = $this->config->prepare("
            UPDATE inventory
            SET productName = ?, quantity = ?, unit = ?, price = ?, expiryDate = ?, category = ?, image = ?
            WHERE productID = ?
        ");
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->error);

        $expiryDateVar = $data['expiryDate'];

        $stmt->bind_param(
            "sisdssss",
            $data['productName'],
            $data['quantity'],
            $data['unit'],
            $data['price'],
            $expiryDateVar,
            $data['category'],
            $data['image'],
            $data['productID']
        );

        if(!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        return true;
    }

    // Delete product
    public function deleteProduct($product_id){
        $stmt = $this->config->prepare("UPDATE inventory SET status = 'deleted' WHERE productID = ?");
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->error);

        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        return true;
    }

    // Close the connection
    public function close(){
        $this->config->close();
    }
}
?>