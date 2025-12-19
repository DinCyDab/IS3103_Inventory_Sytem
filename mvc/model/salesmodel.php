<?php
class SalesModel {
    private mysqli $db;

    public function __construct() {
        $this->db = new mysqli("127.0.0.1", "root", "", "inventory_system");
        if ($this->db->connect_error) {
            die("DB connection failed: " . $this->db->connect_error);
        }
        $this->db->set_charset("utf8mb4");
    }

    /* =========================================================
       GET AVAILABLE PRODUCTS
    ========================================================= */
    public function getAllProducts(): array {
        $sql = "
            SELECT 
                productID AS product_ID,
                productName AS product_name,
                quantity,
                price,
                category_id AS category,
                unit
            FROM inventory
            WHERE quantity > 0
              AND (status IS NULL OR status != 'deleted')
            ORDER BY productName
        ";

        $res = $this->db->query($sql);
        if (!$res) {
            throw new Exception("MySQL Error in getAllProducts: " . $this->db->error);
        }

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /* =========================================================
       INSERT SALE
    ========================================================= */
    public function insertSale(
        int $account_id,
        array $products,
        string $payment_method,
        ?int $customer_id,
        ?string $customer_name
    ): string {

        if (empty($products)) {
            throw new Exception("No products provided");
        }

        foreach ($products as $p) {
            if (empty($p['product_id']) || $p['product_id'] === '0') {
                throw new Exception("Invalid product ID");
            }
        }

        $transactionId = "TXN-" . time();
        $dateTime = date("Y-m-d H:i:s");

        $this->db->begin_transaction();

        try {
            /* ---------- CUSTOMER ---------- */
            if (!$customer_id && $customer_name) {
                $stmt = $this->db->prepare(
                    "SELECT customer_id FROM customers WHERE LOWER(customer_name)=LOWER(?) LIMIT 1"
                );
                $stmt->bind_param("s", $customer_name);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($row = $res->fetch_assoc()) {
                    $customer_id = (int)$row['customer_id'];
                } else {
                    $stmt2 = $this->db->prepare(
                        "INSERT INTO customers (customer_name) VALUES (?)"
                    );
                    $stmt2->bind_param("s", $customer_name);
                    $stmt2->execute();
                    $customer_id = $stmt2->insert_id;
                    $stmt2->close();
                }
                $stmt->close();
            }

            /* ---------- CALCULATE TOTAL ---------- */
            $totalAmount = 0;
            foreach ($products as $p) {
                $info = $this->getProductInfo($p['product_id']);
                if (!$info) {
                    throw new Exception("Product ID '{$p['product_id']}' not found");
                }
                if ($info['quantity'] < $p['quantity']) {
                    throw new Exception("Insufficient stock for {$info['productName']}");
                }
                $totalAmount += $info['price'] * $p['quantity'];
            }

            /* ---------- INSERT TRANSACTION ---------- */
            $stmt = $this->db->prepare("
                INSERT INTO sales_transactions
                (transaction_id, customer_id, customer_name, date_time, total_amount, payment_method, served_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')
            ");

            $stmt->bind_param(
                "sissdsi",
                $transactionId,
                $customer_id,
                $customer_name,
                $dateTime,
                $totalAmount,
                $payment_method,
                $account_id
            );
            $stmt->execute();
            $stmt->close();

            /* ---------- INSERT ITEMS ---------- */
            $stmt = $this->db->prepare("
                INSERT INTO sales_items
                (transaction_id, product_id, product_name, quantity_sold, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($products as $p) {
                $info = $this->getProductInfo($p['product_id']);
                $qty = (int)$p['quantity'];
                $unit = (float)$info['price'];
                $subtotal = $qty * $unit;

                $stmt->bind_param(
                    "sssidd",
                    $transactionId,
                    $info['productID'],
                    $info['productName'],
                    $qty,
                    $unit,
                    $subtotal
                );
                $stmt->execute();

                $this->decrementInventory($info['productID'], $qty);
            }
            $stmt->close();

            $this->db->commit();
            return $transactionId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /* =========================================================
       GET PRODUCT INFO
    ========================================================= */
    public function getProductInfo(string $productID): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                productID,
                productName,
                quantity,
                price,
                category_id AS category,
                unit
            FROM inventory
            WHERE productID = ?
              AND (status IS NULL OR status != 'deleted')
            LIMIT 1
        ");

        $stmt->bind_param("s", $productID);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) {
            throw new Exception("MySQL Error in getProductInfo: " . $this->db->error);
        }

        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ?: null;
    }

    /* =========================================================
       UPDATE INVENTORY
    ========================================================= */
    private function decrementInventory(string $productID, int $qty): void {
        $stmt = $this->db->prepare("
            UPDATE inventory
            SET quantity = quantity - ?
            WHERE productID = ?
        ");
        $stmt->bind_param("is", $qty, $productID);
        $stmt->execute();
        $stmt->close();
    }

    /* =========================================================
       SALES REPORT
    ========================================================= */
    public function getSalesReportsPaginated(int $page = 1, int $limit = 8): array {
        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT 
                st.transaction_id,
                st.date_time,
                GROUP_CONCAT(si.product_name SEPARATOR ', ') AS products,
                st.total_amount AS order_value,
                SUM(si.quantity_sold) AS quantity_sold,
                st.customer_name,
                st.payment_method
            FROM sales_transactions st
            JOIN sales_items si ON st.transaction_id = si.transaction_id
            WHERE st.status = 'completed'
            GROUP BY st.transaction_id
            ORDER BY st.date_time DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) {
            throw new Exception("MySQL Error in getSalesReportsPaginated: " . $this->db->error);
        }

        $data = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function getTotalSalesCount(): int {
        $res = $this->db->query(
            "SELECT COUNT(*) AS total FROM sales_transactions WHERE status='completed'"
        );

        if (!$res) {
            throw new Exception("MySQL Error in getTotalSalesCount: " . $this->db->error);
        }

        return (int)$res->fetch_assoc()['total'];
    }

    /* =========================================================
       SEARCH PRODUCTS
    ========================================================= */
    public function searchProducts(string $term): array {
        $term = "%{$term}%";
        $stmt = $this->db->prepare("
            SELECT 
                productID AS product_ID,
                productName AS product_name,
                quantity,
                price,
                category_id AS category,
                unit
            FROM inventory
            WHERE (productID LIKE ? OR productName LIKE ?)
              AND quantity > 0
              AND (status IS NULL OR status != 'deleted')
            ORDER BY productName
            LIMIT 50
        ");

        $stmt->bind_param("ss", $term, $term);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) {
            throw new Exception("MySQL Error in searchProducts: " . $this->db->error);
        }

        $data = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
?>