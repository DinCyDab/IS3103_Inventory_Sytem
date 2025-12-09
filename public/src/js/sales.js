document.addEventListener('DOMContentLoaded', function () {
	const addBtn = document.getElementById('addRecordBtn');
	const modal = document.getElementById('addModal');
	const discardBtn = document.getElementById('discardBtn');
	const form = document.getElementById('addRecordForm');
	const tbody = document.getElementById('transactionsTbody');
	const modalError = document.getElementById('modalError');

	function openModal(){ if(modal){ modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; if(modalError){ modalError.style.display='none'; modalError.textContent=''; } } }
	function closeModal(){ if(modal){ modal.style.display = 'none'; document.body.style.overflow = ''; if(form){ form.reset(); } if(modalError){ modalError.style.display='none'; modalError.textContent=''; } } }

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
						let html = '<select style="padding:6px 8px; border-radius:8px; border:1px solid #e6e9ef;">';
						options.forEach(opt => {
							html += `<option value="${escapeHtml(opt)}"${opt===selected ? ' selected' : ''}>${escapeHtml(opt)}</option>`;
						});
						html += '</select>';
						return html;
					};

					tr.innerHTML = `
						<td style="padding:12px 8px; font-weight:600;">${escapeHtml(displayTxn)}</td>
						<td style="padding:12px 8px;">${escapeHtml(dt)}</td>
						<td style="padding:12px 8px;">${escapeHtml(product)}</td>
						<td style="padding:12px 8px;">₱${escapeHtml(price)}</td>
						<td style="padding:12px 8px;">${escapeHtml(qty)} Packets</td>
						<td style="padding:12px 8px;">${escapeHtml(customer)}</td>
						<td style="padding:12px 8px;">${createPaymentSelect(payment)}</td>
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
