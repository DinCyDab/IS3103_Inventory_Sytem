<?php
session_start();

// Use the existing model file in mvc/model/
require_once __DIR__ . "/../model/salesmodel.php";

class SalesController {
	private $sales_model;

	public function __construct() {
		$this->sales_model = new SalesModel();
	}

	// Return list of active products as JSON
	public function getAvailableProducts() {
		$products = $this->sales_model->getAllProducts();
		header('Content-Type: application/json');
		echo json_encode($products);
	}

	// Record a single-product sale (uses existing SalesModel methods)
	public function recordSale() {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(405);
			echo json_encode(['error' => 'Method not allowed']);
			return;
		}

		// Read POST inputs
		$product_name = trim($_POST['product'] ?? '');
		$quantity = intval($_POST['quantity'] ?? 0);
		$total_price = floatval($_POST['total_price'] ?? 0);
		$payment_method = trim($_POST['payment_method'] ?? 'Cash');
		$customer_name = trim($_POST['customer_name'] ?? '');
		$account_id = $_SESSION['account_ID'] ?? 1;

		// Basic validation
		if ($product_name === '' || $quantity <= 0 || $total_price <= 0) {
			http_response_code(400);
			echo json_encode(['error' => 'Invalid input']);
			return;
		}

		// Find product ID (case-insensitive search implemented in model)
		$product_id = $this->sales_model->getProductIdByName($product_name);
		if (!$product_id) {
			http_response_code(404);
			echo json_encode(['error' => 'Product not found. Check product ID or name.']);
			return;
		}

		// Insert sales report with product info (no separate item insert)
		$result = $this->sales_model->insertSalesReport($account_id, $total_price, $product_id, $quantity, $payment_method, $customer_name);
		
		if ($result === 'no_inventory') {
			http_response_code(400);
			echo json_encode(['error' => 'Product not in inventory. Add inventory first.']);
			return;
		}
		
		if ($result === 'insufficient_stock') {
			$available = $this->sales_model->getInventoryByProduct($product_id);
			http_response_code(400);
			echo json_encode(['error' => "Insufficient stock. Only {$available} available."]);
			return;
		}

		if ($result === 'product_name_missing') {
			http_response_code(400);
			echo json_encode(['error' => 'Product name could not be resolved.']);
			return;
		}

		if ($result && $result !== false) {
			// $result is now the transaction_ID string (e.g., "TXN-00001")
			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'report_id' => $result, // This is the transaction_ID
				'product_id' => $product_id,
				'quantity' => $quantity,
				'total_price' => $total_price,
				'payment_method' => $payment_method,
				'customer_name' => $customer_name
			]);
		} else {
			http_response_code(500);
			echo json_encode(['error' => 'Failed to create report. Check server logs.']);
		}
	}
}

// Route AJAX/API calls to the controller (no new files created)
$controller = new SalesController();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getProducts') {
	$controller->getAvailableProducts();
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product'])) {
	$controller->recordSale();
	exit;
}

// If reached here, return minimal 400
http_response_code(400);
echo json_encode(['error' => 'Bad request']);
?>