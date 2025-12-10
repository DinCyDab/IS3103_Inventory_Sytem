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
		$stmt->bind_param("sssdiss", $transactionId, $dateTime, $productName, $orderValue, $qtySold, $custName, $payMethod);

		$ok = $stmt->execute();
		$error = $stmt->error;
		$stmt->close();

		if ($ok) {
			$this->decrementInventory($product_id, $quantity);
			return $transactionId;
		}
		error_log("Insert failed: " . $error);
		return false;
	}

	private function generateTxnId() {
		$result = $this->db->query("SELECT transaction_ID FROM salesreport ORDER BY transaction_ID DESC LIMIT 1");
		if ($result && $row = $result->fetch_assoc()) {
			$lastId = $row['transaction_ID'];
			if (preg_match('/TXN-(\d+)/', $lastId, $matches)) {
				$nextNum = intval($matches[1]) + 1;
				return 'TXN-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
			}
		}
		return 'TXN-00001';
	}

	// Get paginated sales reports
	public function getSalesReportsPaginated($page = 1, $limit = 8) {
		$page = max(1, (int)$page);
		$limit = max(1, (int)$limit);
		$offset = ($page - 1) * $limit;

		$query = "SELECT transaction_ID, date_time, products, order_value, quantity_sold, customer_name, payment_method
				  FROM salesreport
				  ORDER BY date_time DESC
				  LIMIT ? OFFSET ?";
		
		$stmt = $this->db->prepare($query);
		$stmt->bind_param("ii", $limit, $offset);
		$stmt->execute();
		$result = $stmt->get_result();

		$sales_data = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$sales_data[] = $row;
			}
		}
		$stmt->close();
		return $sales_data;
	}

	// Get total count of sales reports
	public function getTotalSalesCount() {
		$result = $this->db->query("SELECT COUNT(*) as total FROM salesreport");
		if ($result && $row = $result->fetch_assoc()) {
			return (int)$row['total'];
		}
		return 0;
	}

	public function getAllSalesReports() {
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
		if (is_numeric($product_name)) {
			$product_id = (int)$product_name;
			$stmt = $this->db->prepare("SELECT productID FROM inventory WHERE productID = ? LIMIT 1");
			$stmt->bind_param("i", $product_id);
		} else {
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

	// Updated to include price
	public function getAllProducts() {
		$query = "SELECT productID as product_ID, productName as product_name, quantity, price
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
		$stmt = $this->db->prepare("UPDATE inventory SET quantity = quantity - ? WHERE productID = ?");
		$stmt->bind_param("ii", $quantity, $product_id);
		$ok = $stmt->execute();
		$stmt->close();
		return $ok;
	}

	public function deleteAllSales() {
		$this->db->query("DELETE FROM salesreport");
		$this->db->query("ALTER TABLE salesreport AUTO_INCREMENT = 1");
		return true;
	}
}
?>