<?php
require_once __DIR__ . '/../model/inventorymodel.php';

class ProductController{
    private $inventory_model;

    public function __construct(){
        $this->inventory_model = new ProductModel();
    }

    // Show all products
    public function index(){
        $products = $this->inventory_model->getAllProducts();
        return $products; // return data only
    }

    // Add a new product
    public function create(){
        header('Content-Type: application/json'); // Always JSON
        try{
        // Handle image upload
        $imagePath = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
            $publicDir = __DIR__ . '/../../public/images/uploads/';
            if(!is_dir($publicDir)) mkdir($publicDir, 0755, true);

            $filename = uniqid() . "_" . basename($_FILES['image']['name']);
            $targetFile = $publicDir . $filename;

            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)){
                $imagePath = 'images/uploads/' . $filename; // path to store in DB
            }
        }

        $data = [
            "productID" => $_POST["productID"],
            "productName" => $_POST["productName"],
            "quantity" => (int) $_POST["quantity"],
            "price" => (float) $_POST["price"],
            "expiryDate" => $_POST["expiryDate"],
            "category" => $_POST["category"],
            "image" => $imagePath // save path
        ];

        $this->inventory_model->addProduct($data);

        echo json_encode(["success" => true, "message" => "Product added successfully"]);

    } catch(Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
        // Check if request is via AJAX
        // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        //     echo json_encode(["success" => true]);
        // } else{
        //     header("Location: index.php?view=inventory");
        // }
        exit();

    }

    // Update an existing product
    public function update(){
        header('Content-Type: application/json');
        try {
            $imagePath = $_POST['image'] ?? ''; // Existing image path

            // Handle new image upload
            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
                $uploadDir = __DIR__ . '/../../public/images/uploads/';
                if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $filename = uniqid() . "_" . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $filename;

                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)){
                    $imagePath = 'images/uploads/' . $filename;
                }
            }

            $data = [
                "productID" => $_POST["productID"],
                "productName" => $_POST["productName"],
                "quantity" => (int) $_POST["quantity"],
                "price" => (float) $_POST["price"],
                "expiryDate" => $_POST["expiryDate"],
                "category" => $_POST["category"],
                "image" => $imagePath
            ];

            $this->inventory_model->updateProduct($data);

            echo json_encode(["success" => true, "message" => "Product updated successfully"]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        exit();
    }

    // Delete a product
    public function delete(){
        header('Content-Type: application/json');
        try {
            $id = $_POST['productID'] ?? $_GET['id'] ?? '';
            if(!$id) throw new Exception("Product ID missing");
            $this->inventory_model->deleteProduct($id);
            echo json_encode(["success" => true, "message" => "Product deleted successfully"]);
        } catch(Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        exit();
    }
}
?>