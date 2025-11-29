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

        // Out of Stock (quantity = 0)
        $row = $conn->query("
            SELECT COUNT(*) AS outOfStock
            FROM inventory
            WHERE quantity = 0
        ")->fetch_assoc();
        $outOfStock = $row['outOfStock'] ?? 0;

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
                "footer" => "Last 7 days", 
                "highlight" => "blue"
            ],
            [
                "label" => "Total Products", 
                "value" => $totalProducts, 
                "footer" => "Last 7 days", 
                "extra" => "₱" . number_format($totalValue, 2), 
                "highlight" => "orange"
            ],
            [
                "label" => "Top Selling", 
                "value" => $totalSellingQty,  
                "footer" => "Last 7 days", 
                "extra" => "₱" . number_format($totalSellingValue, 2), 
                "highlight" => "purple"
            ],
            [
                "label" => "Low Stocks", 
                "value" => $lowStocks, 
                "footer" => "Needs Restock", 
                "extra" => $outOfStock . "Out of Stock", 
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
            INSERT INTO inventory (productID, productName, quantity, price, expiryDate, category, image)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if(!$stmt){
            die("Prepare failed: " . $this->config->error);
        }

        $stmt->bind_param(
            "ssidsss",
            $data['productID'],
            $data['productName'],
            $data['quantity'],
            $data['price'],
            $data['expiryDate'],
            $data['category'],
            $data['image']
        );

        if(!$stmt->execute()){
            die("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Update product
    public function updateProduct($data){
        $stmt = $this->config->prepare("
            UPDATE inventory
            SET productName = ?, quantity = ?, price = ?, expiryDate = ?, category = ?, image = ?
            WHERE productID = ?
        ");
        if(!$stmt){
            die("Prepare failed: " . $this->config->error);
        }

        $stmt->bind_param(
            "sidssss",
            $data['productName'],
            $data['quantity'],
            $data['price'],
            $data['expiryDate'],
            $data['category'],
            $data['image'],
            $data['productID']
        );

        if(!$stmt->execute()){
            die("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Delete product
    public function deleteProduct($product_id){
        $stmt = $this->config->prepare("DELETE FROM inventory WHERE productID = ?");
        if(!$stmt){
            die("Prepare failed: " . $this->config->error);
        }

        $stmt->bind_param("s", $product_id);

        if(!$stmt->execute()){
            die("Execute failed: " . $stmt->error);
        }

        return true;
    }

    // Close the connection
    public function close(){
            $this->config->close();
    }
}
?>