<?php
require_once __DIR__ . "/../model/salesmodel.php";

class SalesView{
	public function render(){
		// Load sales data from database
		$sales_model = new SalesModel();
		$sales_records = $sales_model->getAllSalesReports();
		?>
		<!-- Add fixed header that uses inventory.css .header/.search-wrapper/.searchbar -->
		<div class="header">
			<div style="display:flex; align-items:center; width:100%; gap:16px; padding:0 16px;">
				<div class="search-wrapper" style="position:relative; width:100%; max-width:720px;">
					<i class="fa fa-search" aria-hidden="true" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
					<input type="search" id="searchInput" class="searchbar" placeholder="Search product or order" style="width:100%;">
				</div>
				<div style="margin-left:auto; display:flex; gap:12px; align-items:center;">
					<!-- optional right-side header controls -->
				</div>
			</div>
		</div>

		<div class="sales-page-wrapper" style="font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding:32px; background:#f9fafb; min-height:100vh; margin-top:120px;">
			<!-- Spacer (buttons removed above transaction log; transaction-card has its own actions) -->
			<div style="height:8px; margin-bottom:16px;"></div>

			<!-- Transaction Log (uses CSS in public/src/css/sales.css) -->
			<div class="transactions-card">
				<div class="transactions-header">
					<h2>Transaction Log</h2>
					<div class="transactions-actions">
						<button id="addRecordBtn" class="btn-primary">Add Record</button>
						<button class="btn-secondary">Filters</button>
						<button class="btn-secondary">Download all</button>
					</div>
				</div>

				<div style="overflow-x:auto;">
					<table class="transactions-table" id="transactionsTable">
						<thead>
							<tr>
								<th>Transaction ID</th>
								<th>Date & Time</th>
								<th>Products</th>
								<th>Order Value</th>
								<th>Quantity Sold</th>
								<th>Customer Name</th>
								<th>Payment Method</th>
							</tr>
						</thead>
						<tbody id="transactionsTbody">
							<?php
							// keep renderRows logic but adjust classes in echo (no view refactor)
							foreach ($sales_records as $row) {
								$txnId = htmlspecialchars($row['transaction_ID'] ?? 'N/A');
								$dateTime = htmlspecialchars($row['date_time'] ?? '');
								$products = htmlspecialchars($row['products'] ?? '-');
								$orderValue = number_format((float)($row['order_value'] ?? 0), 2);
								$qtySold = (int)($row['quantity_sold'] ?? 0);
								$customerName = htmlspecialchars($row['customer_name'] ?? 'Guest');
								$paymentMethod = htmlspecialchars($row['payment_method'] ?? 'Cash');
								echo "<tr>
										<td class='txn'>{$txnId}</td>
										<td>{$dateTime}</td>
										<td>{$products}</td>
										<td class='order-value'>₱{$orderValue}</td>
										<td class='qty-text'>{$qtySold} Packets</td>
										<td>{$customerName}</td>
										<td><span class='payment-badge'>" . htmlspecialchars($paymentMethod) . "</span></td>
									  </tr>";
							}
							?>
						</tbody>
					</table>
				</div>

				<div class="transactions-footer">
					<div>
						<button class="btn-secondary">Previous</button>
					</div>
					<div class="pagination-center">
						Page 1 of 10<br><a href="#">See All</a>
					</div>
					<div>
						<button class="btn-secondary">Next</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal HTML -->
		<div id="addModal" class="modal-overlay" style="display:none;">
			<div class="modal-card">
				<h2 style="margin:0 0 20px 0; font-size:20px; font-weight:600; color:#111827;">Add Record</h2>
				<form id="addRecordForm">
					<div class="form-grid">
						<div class="form-row">
							<label>Customer Name</label>
							<input type="text" name="customer_name" placeholder="Enter customer name" required>
						</div>
						<div class="form-row">
							<label>Product Selection</label>
							<input type="text" name="product" placeholder="Enter product name or ID" required>
						</div>
						<div class="form-row">
							<label>Quantity Sold</label>
							<input type="number" name="quantity" placeholder="Enter quantity sold" min="1" required>
						</div>
						<div class="form-row">
							<label>Total Price</label>
							<input type="number" name="total_price" placeholder="Enter total price" min="0" step="0.01" required>
						</div>
						<div class="form-row" style="grid-column:1 / -1;">
							<label>Payment Method</label>
							<select name="payment_method" required>
								<option value="GCash">GCash</option>
								<option value="Cash">Cash</option>
								<option value="Card">Card</option>
							</select>
						</div>
					</div>

					<div class="modal-actions">
						<button type="button" id="discardBtn" class="btn-secondary">Discard</button>
						<button type="submit" class="btn-primary">Record Sale</button>
					</div>
				</form>
				<div id="modalError" style="color:#ef4444; margin-top:12px; font-size:14px; display:none;"></div>
			</div>
		</div>

		<!-- external JS -->
		<script src="./public/src/js/sales.js"></script>
 
 		<link href="./public/src/css/sales.css" rel="stylesheet">
 		<?php
	}

	private function renderRows($records){
		if (empty($records)) {
			echo "<tr><td colspan='7' style='padding:40px; text-align:center; color:#9ca3af; font-size:14px;'>No transactions yet</td></tr>";
			return;
		}

		foreach ($records as $row) {
			// Use actual salesreport column names
			$txnId = $row['transaction_ID'] ?? 'N/A';
			$dateTime = $row['date_time'] ?? '';
			$products = $row['products'] ?? '-';
			$orderValue = (float)($row['order_value'] ?? 0);
			$qtySold = (int)($row['quantity_sold'] ?? 0);
			$customerName = $row['customer_name'] ?? 'Guest';
			$paymentMethod = $row['payment_method'] ?? 'Cash';
			
			echo "<tr style='border-bottom:1px solid #f3f4f6; transition:background 0.2s;' onmouseover='this.style.background=\"#f9fafb\"' onmouseout='this.style.background=\"#fff\"'>
					<td style='padding:16px 24px; font-weight:600; color:#111827; font-size:14px;'>" . htmlspecialchars($txnId) . "</td>
					<td style='padding:16px 24px; color:#6b7280; font-size:14px;'>" . htmlspecialchars($dateTime) . "</td>
					<td style='padding:16px 24px; color:#374151; font-size:14px;'>" . htmlspecialchars($products) . "</td>
					<td style='padding:16px 24px; color:#111827; font-weight:500; font-size:14px;'>₱" . number_format($orderValue, 2) . "</td>
					<td style='padding:16px 24px; color:#374151; font-size:14px;'>" . htmlspecialchars($qtySold) . " Packets</td>
					<td style='padding:16px 24px; color:#374151; font-size:14px;'>" . htmlspecialchars($customerName) . "</td>
					<td style='padding:16px 24px;'>
						<span style='display:inline-flex; align-items:center; padding:6px 12px; background:#dbeafe; color:#1e40af; border-radius:6px; font-size:13px; font-weight:500;'>
							" . htmlspecialchars($paymentMethod) . "
						</span>
					</td>
				</tr>";
		}
	}
}
?>