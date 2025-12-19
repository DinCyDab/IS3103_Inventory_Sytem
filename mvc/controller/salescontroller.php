<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/salesmodel.php";

class SalesController {
    private $sales_model;

    public function __construct() {
        $this->sales_model = new SalesModel();
    }

    // Search products by any identifier (ID, name, barcode, serial)
    public function searchProducts() {
        header('Content-Type: application/json');
        
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            echo json_encode(['success' => true, 'products' => []]);
            exit();
        }
        
        // Get products that match the search term
        $products = $this->sales_model->searchProducts($searchTerm);
        
        echo json_encode([
            'success' => true,
            'products' => $products
        ]);
        exit();
    }

    // Debug session info
    public function debugSession() {
        header('Content-Type: application/json');
        echo json_encode([
            'session_exists' => isset($_SESSION['account']),
            'session_data' => $_SESSION ?? 'No session',
            'account_data' => $_SESSION['account'] ?? 'No account in session'
        ]);
        exit();
    }

    // Return list of available products
    public function getAvailableProducts() {
        header('Content-Type: application/json');
        echo json_encode($this->sales_model->getAllProducts());
        exit();
    }

    // Pagination for sales reports
    public function getPaginatedSales() {
        header('Content-Type: application/json');
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
        
        $data = $this->sales_model->getSalesReportsPaginated($page, $limit);
        $total = $this->sales_model->getTotalSalesCount();
        $totalPages = max(1, ceil($total / $limit));

        echo json_encode([
            "success" => true,
            "data" => $data,
            "pagination" => [
                "current_page" => $page,
                "total_pages" => $totalPages,
                "total_items" => $total,
                "limit" => $limit
            ]
        ]);
        exit();
    }

    // Record a sale
    public function recordSale() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        // Check if user is logged in
        if (!isset($_SESSION['account'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        // Get account_ID from session (note: capital ID based on your login structure)
        $account_id = $_SESSION['account']['account_ID'] ?? null;
        
        if (!$account_id) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid session - account ID not found']);
            return;
        }

        $payment_method = trim($_POST['payment_method'] ?? 'Cash');
        $customer_name = trim($_POST['customer_name'] ?? '');
        $products = [];

        // Expect arrays of product_ids and quantities
        if (!empty($_POST['product_id']) && !empty($_POST['quantity'])) {
            $product_ids = $_POST['product_id'];
            $quantities = $_POST['quantity'];

            foreach ($product_ids as $index => $pid) {
                $qty = isset($quantities[$index]) ? intval($quantities[$index]) : 0;
                if ($qty <= 0) continue;

                $info = $this->sales_model->getProductInfo($pid);
                if (!$info) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => "Product ID {$pid} not found"]);
                    return;
                }

                // Check stock availability
                if ($info['quantity'] < $qty) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => "Insufficient stock for {$info['productName']}"]);
                    return;
                }

                $products[] = [
                    'product_id' => trim($pid),
                    'product_name' => $info['productName'],
                    'quantity' => $qty,
                    'unit_price' => floatval($info['price'])
                ];
            }
        }

        if (empty($products)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No valid products provided']);
            return;
        }

        // Insert sale
        try {
            $transaction_id = $this->sales_model->insertSale(
                $account_id,
                $products,
                $payment_method,
                null,           // customer_id auto-resolved in model
                $customer_name
            );
        } catch (Exception $e) {
            error_log('Failed insertSale: ' . $e->getMessage() . ' | POST data: ' . json_encode($_POST));
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            return;
        }

        if (!$transaction_id) {
            error_log('Failed insertSale: returned false/null | POST data: ' . json_encode($_POST));
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to record sale. Please try again.']);
            return;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'transaction_id' => $transaction_id,
            'products' => $products,
            'payment_method' => $payment_method,
            'customer_name' => $customer_name
        ]);
    }
}

// Direct access handler
if (basename($_SERVER['PHP_SELF']) === 'salescontroller.php') {
    $controller = new SalesController();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->recordSale();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Debug endpoint
        if (isset($_GET['action']) && $_GET['action'] === 'debugSession') {
            $controller->debugSession();
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'getProducts') {
            $controller->getAvailableProducts();
            exit;
        }
        if (isset($_GET['action']) && $_GET['action'] === 'getSales') {
            $controller->getPaginatedSales();
            exit;
        }
    }

    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Bad request']);
}
?>