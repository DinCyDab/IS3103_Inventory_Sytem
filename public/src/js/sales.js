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

	// Product selection elements
	const productSelect = document.getElementById('productSelect');
	const productSearchInput = document.getElementById('productSearchInput');
	const productSearchResults = document.getElementById('productSearchResults');
	const quantityInput = document.getElementById('quantityInput');
	const unitPriceInput = document.getElementById('unitPriceInput');
	const totalPriceInput = document.getElementById('totalPriceInput');
	const stockInfo = document.getElementById('stockInfo');
	const quantityError = document.getElementById('quantityError');

	let allProducts = []; // Store all products for searching

	// Load all products on page load
	if (productSelect) {
		allProducts = Array.from(productSelect.options)
			.filter(opt => opt.value)
			.map(opt => ({
				id: opt.value,
				name: opt.getAttribute('data-name'),
				price: parseFloat(opt.getAttribute('data-price')) || 0,
				stock: parseInt(opt.getAttribute('data-stock')) || 0,
				displayText: opt.textContent
			}));
	}

	// Product search functionality
	if (productSearchInput && productSearchResults) {
		let searchTimeout;
		
		productSearchInput.addEventListener('input', function() {
			clearTimeout(searchTimeout);
			const searchTerm = this.value.trim().toLowerCase();
			
			if (searchTerm.length < 1) {
				productSearchResults.style.display = 'none';
				productSearchResults.innerHTML = '';
				return;
			}
			
			searchTimeout = setTimeout(() => {
				// Filter products based on search term
				const matches = allProducts.filter(product => {
					return product.id.toLowerCase().includes(searchTerm) ||
						   product.name.toLowerCase().includes(searchTerm) ||
						   product.displayText.toLowerCase().includes(searchTerm);
				});
				
				if (matches.length === 0) {
					productSearchResults.innerHTML = '<div style="padding: 10px; color: #9ca3af;">No products found</div>';
					productSearchResults.style.display = 'block';
					return;
				}
				
				// Display search results
				productSearchResults.innerHTML = matches.slice(0, 10).map(product => `
					<div class="product-search-result" 
						 data-product-id="${escapeHtml(product.id)}"
						 style="padding: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0; hover: background: #f9fafb;">
						<strong>${escapeHtml(product.name)}</strong><br>
						<small style="color: #6b7280;">ID: ${escapeHtml(product.id)} | Stock: ${product.stock} | ₱${product.price.toFixed(2)}</small>
					</div>
				`).join('');
				
				productSearchResults.style.display = 'block';
				
				// Add click handlers to search results
				document.querySelectorAll('.product-search-result').forEach(result => {
					result.addEventListener('mouseenter', function() {
						this.style.background = '#f9fafb';
					});
					result.addEventListener('mouseleave', function() {
						this.style.background = 'white';
					});
					result.addEventListener('click', function() {
						const productId = this.getAttribute('data-product-id');
						selectProduct(productId);
					});
				});
			}, 300); // Debounce search
		});
		
		// Close search results when clicking outside
		document.addEventListener('click', function(e) {
			if (!productSearchInput.contains(e.target) && !productSearchResults.contains(e.target)) {
				productSearchResults.style.display = 'none';
			}
		});
	}
	
	// Function to select a product
	function selectProduct(productId) {
		if (productSelect) {
			productSelect.value = productId;
			
			// Trigger change event to update prices
			const event = new Event('change', { bubbles: true });
			productSelect.dispatchEvent(event);
			
			// Update search input with selected product name
			if (productSearchInput) {
				const selectedProduct = allProducts.find(p => p.id === productId);
				if (selectedProduct) {
					productSearchInput.value = selectedProduct.name;
				}
			}
			
			// Hide search results
			if (productSearchResults) {
				productSearchResults.style.display = 'none';
			}
		}
	}

	let currentPage = parseInt(localStorage.getItem('salesCurrentPage')) || 1;
	const salesPerPage = 8;

	const prevBtn = document.getElementById('prevBtn');
	const nextBtn = document.getElementById('nextBtn');
	const pageIndicator = document.getElementById('pageIndicator');

	// Payment filter modal handlers
	if (filterBtn && paymentFilterModal) {
		filterBtn.addEventListener('click', () => {
			paymentFilterModal.style.display = 'flex';
		});

		// Close modal on X button
		const closeModalBtn = paymentFilterModal.querySelector('.payment-close-x');
		if (closeModalBtn) {
			closeModalBtn.addEventListener('click', () => {
				paymentFilterModal.style.display = 'none';
			});
		}

		// Close modal on outside click
		paymentFilterModal.addEventListener('click', (e) => {
			if (e.target === paymentFilterModal) {
				paymentFilterModal.style.display = 'none';
			}
		});
	}

	// Auto-calculate total price when product or quantity changes
	if (productSelect) {
		productSelect.addEventListener('change', function() {
			const selectedOption = this.options[this.selectedIndex];
			const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
			const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
			
			// Update unit price
			if (unitPriceInput) {
				unitPriceInput.value = price.toFixed(2);
			}
			
			// Show stock info
			if (stockInfo) {
				stockInfo.textContent = `Available stock: ${stock} units`;
				stockInfo.style.display = 'block';
			}
			
			// Reset quantity and hide error
			if (quantityInput) {
				quantityInput.value = '';
				quantityInput.max = stock;
			}
			if (quantityError) {
				quantityError.style.display = 'none';
			}
			
			// Clear total price
			if (totalPriceInput) {
				totalPriceInput.value = '';
			}
		});
	}

	if (quantityInput) {
		quantityInput.addEventListener('input', function() {
			const quantity = parseInt(this.value) || 0;
			const unitPrice = parseFloat(unitPriceInput?.value) || 0;
			const selectedOption = productSelect?.options[productSelect.selectedIndex];
			const maxStock = parseInt(selectedOption?.getAttribute('data-stock')) || 0;
			
			// Validate quantity against stock
			if (quantity > maxStock) {
				if (quantityError) {
					quantityError.textContent = `Only ${maxStock} units available in stock`;
					quantityError.style.display = 'block';
				}
				this.value = maxStock;
			} else {
				if (quantityError) {
					quantityError.style.display = 'none';
				}
			}
			
			// Calculate total price
			const total = (parseInt(this.value) || 0) * unitPrice;
			if (totalPriceInput) {
				totalPriceInput.value = total.toFixed(2);
			}
		});
	}

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

			tbody.innerHTML = data.data.map(row => {
				// Fixed: use transaction_id instead of transaction_ID
				const txnId = escapeHtml(row.transaction_id || 'N/A');
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

	renderSales(currentPage);

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

	const applyFilter = () => {
		const selected = [...checkboxes].filter(cb => cb.checked).map(cb => cb.value.toLowerCase());

		tbody.querySelectorAll('tr').forEach(row => {
			const paymentCell = row.querySelector('td:last-child span');
			if (!paymentCell) return;

			const pm = paymentCell.textContent.trim().toLowerCase();
			row.style.display = (selected.length === 0 || selected.includes(pm)) ? '' : 'none';
		});

		localStorage.setItem('paymentFilter', JSON.stringify(selected));
		
		// Close modal after applying filter
		if (paymentFilterModal) {
			paymentFilterModal.style.display = 'none';
		}
	};

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
		
		// Close modal after clearing filter
		if (paymentFilterModal) {
			paymentFilterModal.style.display = 'none';
		}
	});

	const savedFilter = JSON.parse(localStorage.getItem('paymentFilter') || '[]');
	if (savedFilter.length) {
		checkboxes.forEach(cb => {
			cb.checked = savedFilter.includes(cb.value.toLowerCase());
		});
	}

	function openModal(){ 
		if(modal){ 
			modal.style.display = 'flex'; 
			document.body.style.overflow = 'hidden'; 
			if(modalError){ 
				modalError.style.display='none'; 
				modalError.textContent=''; 
			}
			// Reset form fields
			if (stockInfo) stockInfo.style.display = 'none';
			if (quantityError) quantityError.style.display = 'none';
			if (unitPriceInput) unitPriceInput.value = '';
			if (totalPriceInput) totalPriceInput.value = '';
			if (productSearchInput) productSearchInput.value = '';
			if (productSearchResults) productSearchResults.style.display = 'none';
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
			if (stockInfo) stockInfo.style.display = 'none';
			if (quantityError) quantityError.style.display = 'none';
			if (productSearchInput) productSearchInput.value = '';
			if (productSearchResults) productSearchResults.style.display = 'none';
		} 
	}

	if (addBtn) addBtn.addEventListener('click', openModal);
	if (discardBtn) discardBtn.addEventListener('click', closeModal);
	if (modal) modal.addEventListener('click', function(e){
		if(e.target === modal) closeModal();
	});

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
					data = { success: false, error: 'Invalid server response' }; 
				}

				if (!response.ok || !data.success) {
					const msg = data.error || 'Failed to record sale';
					if(modalError){ 
						modalError.textContent = msg; 
						modalError.style.display = 'block'; 
					}
					return;
				}

				if (data.success) {
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