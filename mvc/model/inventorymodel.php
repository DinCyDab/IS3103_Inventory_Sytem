<?php
require_once __DIR__ . "/../api/config.php";

class ProductModel{
    private $config;

    public function __construct(){
        $this->config = new Config(); // mysqli connection
    }

    // Get all products
    public function getAllProducts(){
        $stmt = $this->config->prepare("SELECT * FROM inventory ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC); // Fetch all records as associative array
    }

    // Paginated Method -- Limit and Offset
    public function getPaginatedProducts($limit, $offset, $categories = []){
        $sql = "SELECT * FROM inventory";
        $params = [];

        if(!empty($categories)){
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql .= " WHERE category IN ($placeholders)";
            $params = $categories;
        }

        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->config->prepare($sql);

        // Bind all parameters dynamically
        $types = str_repeat('s', count($categories)) . "ii"; // categories = string, limit & offset = int
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // For Total Count -- To calculate total pages
    public function getTotalProductsCount($categories = []){
        $sql = "SELECT COUNT(*) as total FROM inventory";
        $params = [];

        if(!empty($categories)){
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql .= " WHERE category IN ($placeholders)";
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
        $row = $conn->query("SELECT COUNT(DISTINCT category) AS totalCategories FROM inventory")->fetch_assoc();
        $totalCategories = $row['totalCategories'] ?? 0;

        // Total Products + Revenue
        $row = $conn->query("
            SELECT
                COUNT(*) AS totalProducts,
                IFNULL(SUM(price * quantity), 0) AS totalValue
            FROM inventory
        ")->fetch_assoc();
        $totalProducts = $row['totalProducts'] ?? 0;
        $totalValue = $row['totalValue'] ?? 0;

        // Low Stocks below 5(units)
        $row = $conn->query("
            SELECT COUNT(*) AS lowStocks
            FROM inventory
            WHERE quantity < 5 AND quantity > 0
        ")->fetch_assoc();
        $lowStocks = $row['lowStocks'] ?? 0;

        // Top Selling (If their is a sold column)
        // If not, then 0
        if($this->columnExists("inventory", "sold")){
            $row = $conn->query("
                SELECT IFNULL(SUM(sold), 0) AS totalSold,
                    IFNULL(SUM(sold * price), 0) AS totalCost
                FROM inventory
            ")->fetch_assoc();
            $totalSellingQty = $row['totalSellingQty'] ?? 0;
            $totalSellingValue = $row['totalSellingValue'] ?? 0;
        } else{
            $totalSellingQty = 0;
            $totalSellingValue = 0;
        }

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
                "footer" => "Top Selling Products", 
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

    // Utility -- Check if a column exists
    private function columnExists($table, $column){
        // Sanitize identifiers to prevent SQL injection
        $table = preg_replace("/[^a-zA-Z0-9_]/", "", $table);
        $column = preg_replace("/[^a-zA-Z0-9_]/", "", $column);

        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
        $conn = $this->config->getConnection();
        $result = $conn->query($sql);

        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        return $result->num_rows > 0;
    }

    // Insert product
    public function addProduct($data){
        $stmt = $this->config->prepare("
            INSERT INTO inventory (productID, productName, quantity, unit, price, expiryDate, category, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if(!$stmt){
            throw new Exception("Prepare failed: " . $this->config->error);
        }

    // Ensure expiryDate is either null or valid YYYY-MM-DD
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

        if(!$stmt->execute()){
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Update product
    public function updateProduct($data){
        $stmt = $this->config->prepare("
            UPDATE inventory
            SET productName = ?, quantity = ?, unit = ?, price = ?, expiryDate = ?, category = ?, image = ?
            WHERE productID = ?
        ");
        if(!$stmt){
            throw new Exception("Prepare failed: " . $this->config->error);
        }

    // Ensure expiryDate is either null or valid YYYY-MM-DD
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

        if(!$stmt->execute()){
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Search Product
    public function searchProducts($q){
        $sql = "SELECT * FROM inventory WHERE product_name LIKE ? OR category LIKE ? ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt){
            die("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
        }

        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Delete product
    public function deleteProduct($product_id){
        $stmt = $this->config->prepare("DELETE FROM inventory WHERE productID = ?");
        if(!$stmt){
            throw new Exception("Prepare failed: " . $this->config->error);
        }

        $stmt->bind_param("s", $product_id);

        if(!$stmt->execute()){
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Close the connection
    public function close(){
            $this->config->close();
    }
}
?>