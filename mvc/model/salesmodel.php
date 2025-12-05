<?php
class SalesModel {
	private $db;

	public function __construct() {
		$this->db = new mysqli('127.0.0.1', 'root', '', 'inventory_system');
		if ($this->db->connect_error) {
			die("Connection failed: " . $this->db->connect_error);
		}
		$this->db->set_charset('utf8mb4');
	}

	private function columnExists($table, $column) {
		// Use direct query (prepared SHOW COLUMNS caused issues)
		$res = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
		return $res && $res->num_rows > 0;
	}

	public function insertSalesReport($account_id, $total_price, $product_id, $quantity, $payment_method = null, $customer_name = null) {
		// Validate inventory
		$current = $this->getInventoryByProduct($product_id);
		if ($current === null) return 'no_inventory';
		if ($current < $quantity) return 'insufficient_stock';

		// Resolve product name from inventory (productID -> productName)
		$productName = $this->getProductNameById($product_id);
		if (!$productName) {
			// Fallback: use "Product #ID" if name not found
			$productName = "Product #" . $product_id;
		}

		// Build salesreport row
		$transactionId = $this->generateTxnId();
		$dateTime = date('Y-m-d H:i:s');
		$orderValue = (float)$total_price;
		$qtySold = (int)$quantity;
		$custName = $customer_name ?? '';
		$payMethod = $payment_method ?? 'Cash';

		$stmt = $this->db->prepare(
			"INSERT INTO salesreport (transaction_ID, date_time, products, order_value, quantity_sold, customer_name, payment_method)
			 VALUES (?, ?, ?, ?, ?, ?, ?)"
		);
		// Correct bind_param: 7 values, types: s s s d i s s
		$stmt->bind_param("sssdiss", $transactionId, $dateTime, $productName, $orderValue, $qtySold, $custName, $payMethod);

		$ok = $stmt->execute();
		$error = $stmt->error; // Capture any SQL error
		$stmt->close();

		if ($ok) {
			$this->decrementInventory($product_id, $quantity);
			return $transactionId; // Return transaction_ID instead of true
		}
		// Log error for debugging
		error_log("Insert failed: " . $error);
		return false;
	}

	// Helper: generate unique transaction ID
	private function generateTxnId() {
		// Get last transaction ID from database
		$result = $this->db->query("SELECT transaction_ID FROM salesreport ORDER BY transaction_ID DESC LIMIT 1");
		if ($result && $row = $result->fetch_assoc()) {
			// Extract number from TXN-XXX format
			$lastId = $row['transaction_ID'];
			if (preg_match('/TXN-(\d+)/', $lastId, $matches)) {
				$nextNum = intval($matches[1]) + 1;
				return 'TXN-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
			}
		}
		// Default if table is empty
		return 'TXN-00001';
	}

	public function getAllSalesReports() {
		// Read back using actual columns present
		$query = "SELECT transaction_ID, date_time, products, order_value, quantity_sold, customer_name, payment_method
				  FROM salesreport
				  ORDER BY date_time DESC";
		$result = $this->db->query($query);

		$sales_data = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$sales_data[] = $row;
			}
			$result->free();
		}
		return $sales_data;
	}

	// Helper: resolve inventory.productName by productID
	private function getProductNameById($product_id) {
		$stmt = $this->db->prepare("SELECT productName FROM inventory WHERE productID = ? LIMIT 1");
		$stmt->bind_param("i", $product_id);
		$stmt->execute();
		$res = $stmt->get_result();
		$name = null;
		if ($row = $res->fetch_assoc()) $name = $row['productName'];
		$stmt->close();
		return $name;
	}

	public function getProductIdByName($product_name) {
		// Check if input is numeric (product ID)
		if (is_numeric($product_name)) {
			$product_id = (int)$product_name;
			// Use productID from your inventory table
			$stmt = $this->db->prepare("SELECT productID FROM inventory WHERE productID = ? LIMIT 1");
			$stmt->bind_param("i", $product_id);
		} else {
			// Search by product name (case-insensitive) - use productName column
			$stmt = $this->db->prepare("SELECT productID FROM inventory WHERE LOWER(productName) = LOWER(?) LIMIT 1");
			$stmt->bind_param("s", $product_name);
		}
		
		$stmt->execute();
		$result = $stmt->get_result();
		$id = null;
		if ($row = $result->fetch_assoc()) {
			$id = (int)$row['productID'];
		}
		$stmt->close();
		return $id;
	}

	public function getAllProducts() {
		// Query your actual inventory table structure
		$query = "SELECT productID as product_ID, productName as product_name, quantity
				  FROM inventory
				  WHERE quantity > 0
				  ORDER BY productName";
		$result = $this->db->query($query);

		$products = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$products[] = $row;
			}
			$result->free();
		}
		return $products;
	}

	public function getInventoryByProduct($product_id) {
		// Use productID column from your inventory table
		$stmt = $this->db->prepare("SELECT quantity FROM inventory WHERE productID = ? LIMIT 1");
		$stmt->bind_param("i", $product_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$qty = null;
		if ($row = $result->fetch_assoc()) {
			$qty = (int)$row['quantity'];
		}
		$stmt->close();
		return $qty;
	}

	public function decrementInventory($product_id, $quantity) {
		// Use productID column
		$stmt = $this->db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE productID = ?");
		$stmt->bind_param("ii", $quantity, $product_id);
		$ok = $stmt->execute();
		$stmt->close();
		return $ok;
	}

	public function deleteAllSales() {
		// Only delete from salesreport
		$this->db->query("DELETE FROM salesreport");
		// Reset auto increment
		$this->db->query("ALTER TABLE salesreport AUTO_INCREMENT = 1");
		return true;
	}
}
?>
