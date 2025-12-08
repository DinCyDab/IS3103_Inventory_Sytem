<?php
require_once __DIR__ . "/../model/salesmodel.php";

class SalesView{
	public function render(){
		// Load sales data from database
		$sales_model = new SalesModel();
		$sales_records = $sales_model->getAllSalesReports();
		?>
		<!-- Add fixed header that uses inventory.css .header/.search-wrapper/.searchbar -->
		<div class="header"></div>

		<div class="topbar">
			<div class="search-wrapper">
				<i class='bx bx-search' ></i>
				<input type="search" class="searchbar" placeholder="Search product or order" />
			</div>
		</div>

		<div class="sales-page-wrapper">

			<!-- Transaction Log (uses CSS in public/src/css/sales.css) -->
			<div class="transactions-card">
				<div class="transactions-header">
					<h3>Transaction Log</h3>
					<div class="transactions-actions">
						<button id="addRecordBtn" class="btn-primary">Add Record</button>
						<button class="filter-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(149, 145, 145, 1);transform: ;msFilter:;"><path d="M7 11h10v2H7zM4 7h16v2H4zm6 8h4v2h-4z"></path></svg>Filters</button>
						<button class="download-btn">Download all</button>
					</div>
				</div>

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
										<td><span class='payment-badge'>" . htmlspecialchars($paymentMethod) . " <svg class='down-arrow' width='14' height='14' viewBox='0 0 24 24'><path d='M7 10l5 5 5-5z' fill='#180d42ff'/></svg></span></td>
									  </tr>";
							}
							?>
						</tbody>
					</table>

				        <div class="table-nav">
							<button class="prev-btn">Previous</button>

							<div class="pages">
								<span class="page-indicator">Page 1</span>
							</div>

							<button class="next-btn">Next</button>
						</div>
		</div>

		<!-- Modal HTML -->
		<div id="addModal" class="modal-overlay" style="display:none;">
			<div class="modal-card">
				<h3 class="modal-title">Add Record</h3>
				<form id="addRecordForm">
						<div class="form-row">
							<label class="field-label">Customer Name</label>
							<input type="text" name="customer_name" placeholder="Enter customer name" required>
						</div>
						<div class="form-row">
							<label class="field-label">Product Selection</label>
							<input type="text" name="product" placeholder="Enter product name or ID" required>
						</div>
						<div class="form-row">
							<label class="field-label">Quantity Sold</label>
							<input type="number" name="quantity" placeholder="Enter quantity sold" min="1" required>
						</div>
						<div class="form-row">
							<label class="field-label">Total Price</label>
							<input type="number" name="total_price" placeholder="Enter total price" min="0" step="0.01" required>
						</div>
						<div class="form-row" style="grid-column:1 / -1;">
							<label class="field-label">Payment Method</label>
							<select name="payment_method" required>
								<option value="" disabled selected>Select payment method</option>
								<option value="GCash">GCash</option>
								<option value="Cash">Cash</option>
								<option value="Card">Card</option>
							</select>
						</div>

					<div class="modal-actions">
						<button type="button" id="discardBtn" class="btn-secondary">Discard</button>
						<button type="submit" class="btn-primary">Record Sale</button>
					</div>
				</form>
				<div id="modalError" style="color:#ef4444; margin-top:12px; font-size:14px; display:none;"></div>
			</div>
		</div>

		<!-- Payment filter modal -->
		<div id="paymentFilterModal" class="modal">
			<div class="modal-content" style="position:relative; width: 380px;">
				
				<!-- Close Button -->
				<button type="button" class="close-modal payment-close-x" aria-label="Close"
					style="position: absolute; top: 22px; right: 22px; background: none; border: none;">
					<svg width="25" height="25" viewBox="0 0 24 24" fill="none">
						<path d="M18 6L6 18" stroke="#999" stroke-width="2" stroke-linecap="round"/>
						<path d="M6 6L18 18" stroke="#999" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>

				<h3 style="margin-top: 8px;">Payment Method</h3>

				<div id="paymentOptions">
					<label>
						<input type="checkbox" value="Cash"> Cash
					</label>
					<label>
						<input type="checkbox" value="GCash"> GCash
					</label>
					<label>
						<input type="checkbox" value="Card"> Card
					</label>
				</div>

				<div class="filter-actions">
					<button class="apply-filter-btn" id="applyPaymentFilter">Apply</button>
					<button class="clear-filter-btn" id="clearPaymentFilter">Clear</button>
				</div>

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
					<td style='padding:16px 24px; color:#111827; font-size:14px;'>₱" . number_format($orderValue, 2) . "</td>
					<td style='padding:16px 24px; color:#374151; font-size:14px;'>" . htmlspecialchars($qtySold) . " Packets</td>
					<td style='padding:16px 24px; color:#374151; font-size:14px;'>" . htmlspecialchars($customerName) . "</td>
					<td style='padding:16px 24px;'>
						<span style='display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#dbeafe; color:#1e40af; border-radius:6px; font-size:13px;'>
							" . htmlspecialchars($paymentMethod) . "
						</span>
					</td>
				</tr>";
		}
	}
}
?>