document.addEventListener('DOMContentLoaded', function () {
	const addBtn = document.getElementById('addRecordBtn');
	const modal = document.getElementById('addModal');
	const discardBtn = document.getElementById('discardBtn');
	const form = document.getElementById('addRecordForm');
	const tbody = document.getElementById('transactionsTbody');
	const modalError = document.getElementById('modalError');

	const paymentFilterModal = document.getElementById('paymentFilterModal');
	const applyPaymentFilter = document.getElementById('applyPaymentFilter');
	const clearPaymentFilter = document.getElementById('clearPaymentFilter');
	const filterBtn = document.querySelector('.filter-btn');
	const checkboxes = document.querySelectorAll('#paymentOptions input');

	const downloadBtn = document.querySelector('.download-btn');
	const table = document.getElementById('transactionsTable');

	// PAGINATION FIX: Use consistent variable names
	let currentPage = parseInt(localStorage.getItem('salesCurrentPage')) || 1;
	const salesPerPage = 8; // Match your limit from PHP

	const prevBtn = document.getElementById('prevBtn'); // Use correct IDs from your HTML
	const nextBtn = document.getElementById('nextBtn');
	const pageIndicator = document.getElementById('pageIndicator');

	function updatePaginationButtons(totalPages) {
		if (prevBtn) prevBtn.disabled = currentPage <= 1;
		if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

		if (pageIndicator) {
			pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
		}
	}

	async function renderSales(page = 1) {
		try {
			const url = `index.php?view=sales&action=getPaginatedSales&page=${page}&limit=${salesPerPage}`;

			const res = await fetch(url, {
				headers: { "X-Requested-With": "XMLHttpRequest" }
			});

			const data = await res.json();

			if (!tbody) return;

			tbody.innerHTML = "";

			if (!data.success || data.data.length === 0) {
				tbody.innerHTML = `
					<tr><td colspan="7" style="padding:40px; text-align:center; color:#9ca3af; font-size:14px;">
						No transactions yet
					</td></tr>`;
				updatePaginationButtons(1);
				return;
			}

			// Render rows
			tbody.innerHTML = data.data.map(row => {
				const txnId = escapeHtml(row.transaction_ID || 'N/A');
				const dateTime = escapeHtml(row.date_time || '');
				const products = escapeHtml(row.products || '-');
				const orderValue = parseFloat(row.order_value || 0).toFixed(2);
				const qtySold = parseInt(row.quantity_sold || 0);
				const customerName = escapeHtml(row.customer_name || 'Guest');
				const paymentMethod = escapeHtml(row.payment_method || 'Cash');

				return `
					<tr>
						<td class='txn'>${txnId}</td>
						<td>${dateTime}</td>
						<td>${products}</td>
						<td class='order-value'>₱${orderValue}</td>
						<td class='qty-text'>${qtySold} Packets</td>
						<td>${customerName}</td>
						<td><span class='payment-badge'>${paymentMethod} <svg class='down-arrow' width='14' height='14' viewBox='0 0 24 24'><path d='M7 10l5 5 5-5z' fill='#180d42ff'/></svg></span></td>
					</tr>
				`;
			}).join('');

			// Apply saved payment filter if any
			applySavedFilter();

			updatePaginationButtons(data.pagination.total_pages);

		} catch (err) {
			console.error("Error loading sales:", err);
			if (tbody) {
				tbody.innerHTML = `
					<tr><td colspan="7" style="padding:40px; text-align:center; color:#ef4444; font-size:14px;">
						Error loading transactions. Please refresh the page.
					</td></tr>`;
			}
		}
	}

	// Pagination button handlers
	if (prevBtn) {
		prevBtn.addEventListener('click', () => {
			if (currentPage > 1) {
				currentPage--;
				localStorage.setItem('salesCurrentPage', currentPage);
				renderSales(currentPage);
			}
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', () => {
			currentPage++;
			localStorage.setItem('salesCurrentPage', currentPage);
			renderSales(currentPage);
		});
	}

	// Initial load
	renderSales(currentPage);

	// Download All
	if(downloadBtn) {
		downloadBtn.addEventListener('click', () => {
			if(!table) return;

			let csvContent = '';
			const headers = Array.from(table.querySelectorAll('thead th'))
				.map(th => `"${th.textContent.trim()}"`)
				.join(',');
			csvContent += headers + '\n';

			const rows = Array.from(tbody.querySelectorAll('tr'));
			rows.forEach(row => {
				const cols = Array.from(row.querySelectorAll('td')).map(td => {
					let text = td.textContent.trim();
					const span = td.querySelector('span');
					if(span) text = span.textContent.trim();
					return `"${text.replace(/"/g, '""')}"`;
				});
				csvContent += cols.join(',') + '\n';
			});

			const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
			const link = document.createElement('a');
			link.href = URL.createObjectURL(blob);
			link.setAttribute('download', 'transactions.csv');
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		});
	}

	// Payment filter modal
	const openPaymentFilter = () => {
		paymentFilterModal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	};
	const closePaymentFilter = () => {
		paymentFilterModal.style.display = 'none';
		document.body.style.overflow = '';
	};

	filterBtn?.addEventListener('click', openPaymentFilter);
	document.querySelector(".payment-close-x")?.addEventListener("click", closePaymentFilter);

	window.addEventListener("click", (e) => {
		if (e.target === paymentFilterModal) closePaymentFilter();
	});

	// Apply filter function
	const applyFilter = () => {
		const selected = [...checkboxes].filter(cb => cb.checked).map(cb => cb.value.toLowerCase());

		tbody.querySelectorAll('tr').forEach(row => {
			const paymentCell = row.querySelector('td:last-child span');
			if (!paymentCell) return;

			const pm = paymentCell.textContent.trim().toLowerCase();
			row.style.display = (selected.length === 0 || selected.includes(pm)) ? '' : 'none';
		});

		localStorage.setItem('paymentFilter', JSON.stringify(selected));
		closePaymentFilter();
	};

	// Function to apply saved filter
	const applySavedFilter = () => {
		const savedFilter = JSON.parse(localStorage.getItem('paymentFilter') || '[]');
		if (savedFilter.length) {
			tbody.querySelectorAll('tr').forEach(row => {
				const paymentCell = row.querySelector('td:last-child span');
				if (!paymentCell) return;
				const pm = paymentCell.textContent.trim().toLowerCase();
				row.style.display = savedFilter.includes(pm) ? '' : 'none';
			});
		}
	};

	applyPaymentFilter?.addEventListener('click', applyFilter);

	clearPaymentFilter?.addEventListener('click', () => {
		checkboxes.forEach(cb => cb.checked = false);
		tbody.querySelectorAll('tr').forEach(row => row.style.display = '');
		localStorage.removeItem('paymentFilter');
		closePaymentFilter();
	});

	// Restore filter checkboxes on load
	const savedFilter = JSON.parse(localStorage.getItem('paymentFilter') || '[]');
	if (savedFilter.length) {
		checkboxes.forEach(cb => {
			cb.checked = savedFilter.includes(cb.value.toLowerCase());
		});
	}

	// Add Record Modal
	function openModal(){ 
		if(modal){ 
			modal.style.display = 'flex'; 
			document.body.style.overflow = 'hidden'; 
			if(modalError){ 
				modalError.style.display='none'; 
				modalError.textContent=''; 
			} 
		} 
	}

	function closeModal(){ 
		if(modal){ 
			modal.style.display = 'none'; 
			document.body.style.overflow = ''; 
			if(form){ form.reset(); } 
			if(modalError){ 
				modalError.style.display='none'; 
				modalError.textContent=''; 
			} 
		} 
	}

	if (addBtn) addBtn.addEventListener('click', openModal);
	if (discardBtn) discardBtn.addEventListener('click', closeModal);
	if (modal) modal.addEventListener('click', function(e){
		if(e.target === modal) closeModal();
	});

	// Form submission
	if (form) {
		form.addEventListener('submit', function(e){
			e.preventDefault();
			if(modalError){ 
				modalError.style.display='none'; 
				modalError.textContent=''; 
			}

			const formData = new FormData(form);

			fetch('./mvc/controller/salescontroller.php', {
				method: 'POST',
				body: formData
			})
			.then(async response => {
				let data;
				try { 
					data = await response.json(); 
				} catch(err){ 
					data = {}; 
				}

				if (!response.ok) {
					const msg = data.error || 'Failed to record sale';
					if(modalError){ 
						modalError.textContent = msg; 
						modalError.style.display = 'block'; 
					}
					return;
				}

				if (data.success) {
					// Refresh the current page to show new record
					renderSales(currentPage);
					closeModal();
				} else {
					if(modalError){ 
						modalError.textContent = data.error || 'Failed to record sale'; 
						modalError.style.display = 'block'; 
					}
				}
			})
			.catch(err => {
				console.error('Error:', err);
				if(modalError){ 
					modalError.textContent = 'Network or server error'; 
					modalError.style.display = 'block'; 
				}
			});
		});
	}

	// Helper function
	function escapeHtml(s){
		if(!s) return '';
		return String(s).replace(/[&<>"']/g, m => ({
			'&':'&amp;',
			'<':'&lt;',
			'>':'&gt;',
			'"':'&quot;',
			"'":'&#39;'
		}[m]));
	}
});