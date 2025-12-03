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