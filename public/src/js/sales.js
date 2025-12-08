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

	// Download All
	if(downloadBtn) {
		downloadBtn.addEventListener('click', () => {
			if(!table) return;

			// Prepare CSV content
			let csvContent = '';
			const headers = Array.from(table.querySelectorAll('thead th'))
				.map(th => `"${th.textContent.trim()}"`)
				.join(',');
			csvContent += headers + '\n';

			const rows = Array.from(tbody.querySelectorAll('tr'));
			rows.forEach(row => {
				const cols = Array.from(row.querySelectorAll('td')).map(td => {
					let text = td.textContent.trim();

					// If td contains span or select, extract proper value
					const span = td.querySelector('span');
					const select = td.querySelector('select');
					if(span) text = span.textContent.trim();
					if(select) text = select.value.trim();

					// Escape double quotes
					return `"${text.replace(/"/g, '""')}"`;
				});
				csvContent += cols.join(',') + '\n';
			});

			// Download file
			const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
			const link = document.createElement('a');
			link.href = URL.createObjectURL(blob);
			link.setAttribute('download', 'transactions.csv');
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		});
	}

	// Show/Hide filter popup
	const openPaymentFilter = () => {
		paymentFilterModal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	};
	const closePaymentFilter = () => {
		paymentFilterModal.style.display = 'none';
		document.body.style.overflow = '';
	};

	filterBtn?.addEventListener('click', openPaymentFilter);

	// Close X button
	document.querySelector(".payment-close-x")?.addEventListener("click", closePaymentFilter);

	// Close when clicking outside modal-content
	window.addEventListener("click", (e) => {
		if (e.target === paymentFilterModal) closePaymentFilter();
	});

	// Apply filter
	const applyFilter = () => {
		const selected = [...checkboxes].filter(cb => cb.checked).map(cb => cb.value.toLowerCase());

		tbody.querySelectorAll('tr').forEach(row => {
			const paymentCell = row.querySelector('td:last-child span');
			if (!paymentCell) return;

			const pm = paymentCell.textContent.trim().toLowerCase();
			row.style.display = (selected.length === 0 || selected.includes(pm)) ? '' : 'none';
		});

		// Save selection to localStorage so filter persists
		localStorage.setItem('paymentFilter', JSON.stringify(selected));
		closePaymentFilter();
	};

	applyPaymentFilter?.addEventListener('click', applyFilter);

	// Clear filter
	clearPaymentFilter?.addEventListener('click', () => {
		checkboxes.forEach(cb => cb.checked = false);
		tbody.querySelectorAll('tr').forEach(row => row.style.display = '');
		localStorage.removeItem('paymentFilter');
		closePaymentFilter();
	});

	// Restore filter on page load WITHOUT opening the popup
	const savedFilter = JSON.parse(localStorage.getItem('paymentFilter') || '[]');
	if (savedFilter.length) {
		checkboxes.forEach(cb => {
			cb.checked = savedFilter.includes(cb.value.toLowerCase());
		});
		// Apply the filter to the rows
		tbody.querySelectorAll('tr').forEach(row => {
			const paymentCell = row.querySelector('td:last-child span');
			if (!paymentCell) return;
			const pm = paymentCell.textContent.trim().toLowerCase();
			row.style.display = savedFilter.includes(pm) ? '' : 'none';
		});
	}

	// To record modal
	function openModal(){ 
		if(modal){ 
			modal.style.display = 'flex'; 
			document.body.style.overflow = 'hidden'; 
			if(modalError){ modalError.style.display='none'; 
				modalError.textContent=''; 
			} 
		} 
	}
	function closeModal(){ 
		if(modal){ modal.style.display = 'none'; document.body.style.overflow = ''; if(form){ form.reset(); } if(modalError){ modalError.style.display='none'; modalError.textContent=''; } } }

	if (addBtn) addBtn.addEventListener('click', openModal);
	if (discardBtn) discardBtn.addEventListener('click', closeModal);
	if (modal) modal.addEventListener('click', function(e){
		if(e.target === modal) closeModal();
	});

	if (form) {
		form.addEventListener('submit', function(e){
			e.preventDefault();
			if(modalError){ modalError.style.display='none'; modalError.textContent=''; }
			const formData = new FormData(form);
			const customer = formData.get('customer_name');
			const product = formData.get('product');
			const qty = formData.get('quantity');
			const price = parseFloat(formData.get('total_price')||0).toFixed(2);
			const payment = formData.get('payment_method');

			fetch('./mvc/controller/salescontroller.php', {
				method: 'POST',
				body: formData
			})
			.then(async response => {
				let data;
				try { data = await response.json(); } catch(err){ data = {}; }
				if (!response.ok) {
					const msg = data.error || 'Failed to record sale';
					if(modalError){ modalError.textContent = msg; modalError.style.display = 'block'; }
					return;
				}
				if (data.success) {
					// controller returns report_id (transaction id or numeric id)
					const txnId = (data.report_id || '').toString();
					const displayTxn = txnId.startsWith('TXN-') ? txnId : ('TXN-' + txnId);
					const dt = new Date().toISOString().replace('T',' ').slice(0,16);

					const tr = document.createElement('tr');
					tr.style.borderTop = '1px solid #f3f4f6';
					tr.style.color = '#374151';

					// create payment select to match table UI
					const createPaymentSelect = (selected) => {
						const options = ['GCash','Cash','Card'];
						let html = '<select>';
						options.forEach(opt => {
							html += `<option value="${escapeHtml(opt)}"${opt===selected ? ' selected' : ''}>${escapeHtml(opt)}</option>`;
						});
						html += '</select>';
						return html;
					};

					tr.innerHTML = `
						<td>${escapeHtml(displayTxn)}</td>
						<td>${escapeHtml(dt)}</td>
						<td>${escapeHtml(product)}</td>
						<td>₱${escapeHtml(price)}</td>
						<td>${escapeHtml(qty)} Packets</td>
						<td>${escapeHtml(customer)}</td>
						<td>${createPaymentSelect(payment)}</td>
					`;
					if (tbody && tbody.firstChild) tbody.insertBefore(tr, tbody.firstChild);
					else if (tbody) tbody.appendChild(tr);
					closeModal();
				} else {
					if(modalError){ modalError.textContent = data.error || 'Failed to record sale'; modalError.style.display = 'block'; }
				}
			})
			.catch(err => {
				console.error('Error:', err);
				if(modalError){ modalError.textContent = 'Network or server error'; modalError.style.display = 'block'; }
			});
		});
	}

	// helper
	function escapeHtml(s){
		if(!s) return '';
		return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
	}
});
