<?php
require_once __DIR__ . "/../api/config.php";

class ProductModel{
    private $config;

    public function __construct(){
        $this->config = new Config();
    }

    private function activeCondition() {
        return "(i.status IS NULL OR i.status = '' OR LOWER(TRIM(i.status)) != 'deleted')";
    }

    // Get all categories grouped
    public function getAllCategories(){
        $sql = "SELECT 
                    category_id,
                    category_name, 
                    category_group,
                    display_order
                FROM product_categories 
                ORDER BY display_order";
        
        $result = $this->config->read($sql);
        
        $categories = [];
        foreach($result as $row){
            $group = $row['category_group'];
            if(!isset($categories[$group])){
                $categories[$group] = [];
            }
            $categories[$group][] = [
                'id' => $row['category_id'],
                'name' => $row['category_name'],
                'order' => $row['display_order']
            ];
        }
        
        return $categories;
    }

    // Get category ID by name
    public function getCategoryIdByName($categoryName){
        $stmt = $this->config->prepare("SELECT category_id FROM product_categories WHERE category_name = ?");
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()){
            return $row['category_id'];
        }
        
        return null;
    }

    public function generateNextProductId(){
        $conn = $this->config->getConnection();
        $result = $conn->query("SELECT productID FROM inventory ORDER BY id DESC LIMIT 1");
        
        if($result && $row = $result->fetch_assoc()){
            $lastId = $row['productID'];
            if(preg_match('/PRD-(\d+)/', $lastId, $matches)){
                $nextNum = intval($matches[1]) + 1;
                return 'PRD-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return 'PRD-0001';
    }

    public function productIdExists($productId, $excludeId = null){
        $conn = $this->config->getConnection();
        $condition = "(status IS NULL OR status = '' OR LOWER(TRIM(status)) != 'deleted')";

        if($excludeId){
            $stmt = $conn->prepare(
                "SELECT COUNT(*) FROM inventory 
                WHERE productID = ? AND id != ? AND $condition"
            );
            $stmt->bind_param("si", $productId, $excludeId);
        } else {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) FROM inventory 
                WHERE productID = ? AND $condition"
            );
            $stmt->bind_param("s", $productId);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0] > 0;
    }

    // Get all products with category details
    public function getAllProducts(){
        $sql = "SELECT 
                    i.id,
                    i.productID,
                    i.productName,
                    i.quantity,
                    i.unit,
                    i.price,
                    i.expiryDate,
                    i.image,
                    i.status,
                    i.created_at,
                    pc.category_id,
                    pc.category_name as category,
                    pc.category_group
                FROM inventory i
                LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                WHERE " . $this->activeCondition() . "
                ORDER BY i.id DESC";
        
        return $this->config->read($sql);
    }

    // Paginated products with category filter
    public function getPaginatedProducts($limit, $offset, $categoryNames = []){
        $sql = "SELECT 
                    i.id,
                    i.productID,
                    i.productName,
                    i.quantity,
                    i.unit,
                    i.price,
                    i.expiryDate,
                    i.image,
                    i.status,
                    pc.category_id,
                    pc.category_name AS category,
                    pc.category_group
                FROM inventory i
                LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                WHERE " . $this->activeCondition();

        $params = [];
        $types = "";

        if(!empty($categoryNames)){
            $placeholders = implode(',', array_fill(0, count($categoryNames), '?'));
            $sql .= " AND pc.category_name IN ($placeholders)";
            $params = $categoryNames;
            $types .= str_repeat('s', count($categoryNames));
        }

        $sql .= " ORDER BY i.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->config->prepare($sql);
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->getConnection()->error);

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalProductsCount($categoryNames = []){
        $sql = "SELECT COUNT(*) as total 
                FROM inventory i
                LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                WHERE " . $this->activeCondition();
        
        $params = [];

        if(!empty($categoryNames)){
            $placeholders = implode(',', array_fill(0, count($categoryNames), '?'));
            $sql .= " AND pc.category_name IN ($placeholders)";
            $params = $categoryNames;
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

    public function getOverviewStats(){
        $conn = $this->config->getConnection();

        // Total Categories (from product_categories table)
        $row = $conn->query("SELECT COUNT(*) AS totalCategories FROM product_categories")->fetch_assoc();
        $totalCategories = $row['totalCategories'] ?? 0;

        // Total Products + Total Value
        $row = $conn->query("
            SELECT
                COUNT(*) AS totalProducts,
                IFNULL(SUM(price * quantity), 0) AS totalValue
            FROM inventory i
            WHERE " . $this->activeCondition() . "
        ")->fetch_assoc();
        $totalProducts = $row['totalProducts'] ?? 0;
        $totalValue = $row['totalValue'] ?? 0;

        // Low Stocks (below 5 units)
        $row = $conn->query("
            SELECT COUNT(*) AS lowStocks
            FROM inventory i
            WHERE quantity < 5 AND quantity > 0 AND " . $this->activeCondition() . "
        ")->fetch_assoc();
        $lowStocks = $row['lowStocks'] ?? 0;

        // Top Selling (from normalized sales_items and sales_transactions)
        $row = $conn->query("
            SELECT 
                IFNULL(SUM(si.quantity_sold), 0) AS totalSold,
                IFNULL(SUM(si.subtotal), 0) AS totalRevenue
            FROM sales_items si
            JOIN sales_transactions st ON si.transaction_id = st.transaction_id
            WHERE st.status = 'completed'
        ")->fetch_assoc();

        $totalSellingQty = $row['totalSold'] ?? 0;
        $totalSellingValue = $row['totalRevenue'] ?? 0;

        return [
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
    }

    public function searchProducts($q){
        $sql = "SELECT 
                    i.id,
                    i.productID,
                    i.productName,
                    i.quantity,
                    i.unit,
                    i.price,
                    i.expiryDate,
                    i.image,
                    pc.category_id,
                    pc.category_name,
                    pc.category_group
                FROM inventory i
                LEFT JOIN product_categories pc ON i.category_id = pc.category_id
                WHERE " . $this->activeCondition() . " 
                AND (i.productName LIKE ? 
                     OR i.productID LIKE ? 
                     OR pc.category_name LIKE ?)
                ORDER BY i.id DESC";

        $stmt = $this->config->prepare($sql);
        $param = "%" . $q . "%";
        $stmt->bind_param("sss", $param, $param, $param);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addProduct($data){
        // Resolve category_id from friendly category name
        $categoryId = null;

        if(isset($data['category']) && !empty($data['category'])){
            $categoryId = $this->getCategoryIdByName($data['category']);

            if(!$categoryId){
                // Auto-create category if it doesn't exist
                $stmt = $this->config->prepare("
                    INSERT INTO product_categories (category_name, category_group, display_order)
                    VALUES (?, 'Miscellaneous', 99)
                ");
                if(!$stmt) throw new Exception("Prepare failed: " . $this->config->getConnection()->error);
                $stmt->bind_param("s", $data['category']);
                $stmt->execute();
                $categoryId = $stmt->insert_id;
            }
        } elseif(isset($data['category_id'])){
            $categoryId = $data['category_id'];
        } else {
            throw new Exception("Category is required");
        }

        // Insert product
        $stmt = $this->config->prepare("
            INSERT INTO inventory 
            (productID, productName, quantity, unit, price, expiryDate, category_id, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->getConnection()->error);

        $stmt->bind_param(
            "ssisdsis",
            $data['productID'],
            $data['productName'],
            $data['quantity'],
            $data['unit'],
            $data['price'],
            $data['expiryDate'],
            $categoryId,
            $data['image']
        );

        if(!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        return true;
    }

    public function updateProduct($data){
        // Resolve category_id from friendly category name
        $categoryId = null;

        if(isset($data['category']) && !empty($data['category'])){
            $categoryId = $this->getCategoryIdByName($data['category']);

            if(!$categoryId){
                // Auto-create category if it doesn't exist
                $stmt = $this->config->prepare("
                    INSERT INTO product_categories (category_name, category_group, display_order)
                    VALUES (?, 'Miscellaneous', 99)
                ");
                if(!$stmt) throw new Exception("Prepare failed: " . $this->config->getConnection()->error);
                $stmt->bind_param("s", $data['category']);
                $stmt->execute();
                $categoryId = $stmt->insert_id;
            }
        } elseif(isset($data['category_id'])){
            $categoryId = $data['category_id'];
        } else {
            throw new Exception("Category is required");
        }

        // Update product
        $stmt = $this->config->prepare("
            UPDATE inventory
            SET productName = ?, quantity = ?, unit = ?, price = ?, expiryDate = ?, category_id = ?, image = ?
            WHERE productID = ?
        ");
        if(!$stmt) throw new Exception("Prepare failed: " . $this->config->getConnection()->error);

        $stmt->bind_param(
            "sisdsiss",
            $data['productName'],
            $data['quantity'],
            $data['unit'],
            $data['price'],
            $data['expiryDate'],
            $categoryId,
            $data['image'],
            $data['productID']
        );

        if(!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        return true;
    }

    public function deleteProduct($product_id){
        $stmt = $this->config->prepare("UPDATE inventory SET status = 'deleted' WHERE productID = ?");
        if(!$stmt) throw new Exception("Prepare failed");

        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        return true;
    }

    public function close(){
        $this->config->close();
    }
}
?>