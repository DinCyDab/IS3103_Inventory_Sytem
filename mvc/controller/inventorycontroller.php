<?php
require_once __DIR__ . '/../model/inventorymodel.php';

class ProductController{
    private $inventory_model;

    public function __construct(){
        $this->inventory_model = new ProductModel();
    }

    // Show all products with overview stats
    public function index(){
        $products = $this->inventory_model->getAllProducts();
        $overviewStats = $this->inventory_model->getOverviewStats();

        return [
            "products" => $products,
            "overviewStats" => $overviewStats
        ];
    }

    // Get next product ID
    public function getNextProductId(){
        header('Content-Type: application/json');
        $nextId = $this->inventory_model->generateNextProductId();
        echo json_encode(['success' => true, 'productID' => $nextId]);
        exit();
    }

    // Pagination Method -- Limit and Offset
    public function paginated(){
        header('Content-Type: application/json');

        $page = max((int)($_GET['page'] ?? 1), 1);
        $limit = max((int)($_GET['limit'] ?? 5), 1);

        error_log("PAGINATION DEBUG → Page: $page | Limit: $limit");
        $offset = ($page - 1) * $limit;

        $categories = json_decode($_GET['categories'] ?? '[]', true);
        if(!is_array($categories)) $categories = [];

        $products = $this->inventory_model->getPaginatedProducts($limit, $offset, $categories);
        $total = $this->inventory_model->getTotalProductsCount($categories);
        $overviewStats = $this->inventory_model->getOverviewStats();

        echo json_encode([
            "success" => true,
            "products" => $products,
            "page" => $page,
            "limit" => $limit,
            "total" => $total,
            "totalPages" => ceil($total / $limit),
            "overviewStats" => $overviewStats
        ]);

        exit();
    }

    // Add a new product
    public function create(){
        header('Content-Type: application/json');
        try{
            $imagePath = $this->handleImageUpload($_FILES['productImage'] ?? null);
            $expiryDate = $this->normalizeExpiryDate($_POST['expiryDate'] ?? null);
            $unit = trim($_POST['unit']) ?: 'pcs';

            // Auto-generate Product ID if not provided or empty
            $productID = trim($_POST["productID"] ?? '');
            if(empty($productID)){
                $productID = $this->inventory_model->generateNextProductId();
            }

            $data = [
                "productID" => $productID,
                "productName" => $_POST["productName"],
                "quantity" => (int) $_POST["quantity"],
                "unit" => $unit,
                "price" => (float) $_POST["price"],
                "expiryDate" => $expiryDate,
                "category" => $_POST["category"],
                "image" => $imagePath
            ];

            $this->inventory_model->addProduct($data);

            echo json_encode(["success" => true, "message" => "Product added successfully", "productID" => $productID]);

        } catch(Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        exit();
    }

    // Update an existing product
    public function update(){
        header('Content-Type: application/json');
        try {
            $imagePath = $_POST['existingImage'] ?? '';

            if(isset($_FILES['productImage']) && $_FILES['productImage']['error'] === 0){
                $imagePath = $this->handleImageUpload($_FILES['productImage']);
            }

            $expiryDate = $this->normalizeExpiryDate($_POST['expiryDate'] ?? null);
            $unit = trim($_POST['unit']) ?: 'pcs';

            $data = [
                "productID" => $_POST["productID"],
                "productName" => $_POST["productName"],
                "quantity" => (int) $_POST["quantity"],
                "unit" => $unit,
                "price" => (float) $_POST["price"],
                "expiryDate" => $expiryDate,
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

    // Search product/s
    public function search() {
        header('Content-Type: application/json');

        if(!isset($_GET['q'])) {
            echo json_encode(['products'=>[]]);
            exit;
        }

        $query = trim($_GET['q']);
        $products = $this->inventory_model->searchProducts($query);

        echo json_encode(['products' => $products]);
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

    public function fetchStats(){
        header('Content-Type: application/json');
        $stats = $this->inventory_model->getOverviewStats();
        echo json_encode($stats);
        exit();
    }

    public function allProducts(){
        header('Content-Type: application/json');
        
        $products = $this->inventory_model->getAllProducts();

        echo json_encode(['products' => $products]);
        exit();
    }

    // Helper Methods
    private function handleImageUpload($file) {
        if(!$file || $file['error'] !== 0) return '';

        $uploadDir = __DIR__ . '/../../public/images/uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid() . "_" . basename($file['name']);
        $targetFile = $uploadDir . $filename;

        if(move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $filename;
        }
        return '';
    }

    // Helper to normalize expiry date
    private function normalizeExpiryDate($expiryDate) {
        $expiryDate = trim($expiryDate);
        if(empty($expiryDate)) return null;

        if(preg_match('/^\d{4}$/', $expiryDate)) $expiryDate .= '-01-01';
        elseif(preg_match('/^\d{4}-\d{2}$/', $expiryDate)) $expiryDate .= '-01';

        $d = date_create($expiryDate);
        return $d ? date_format($d, 'Y-m-d') : null;
    }
}
?>