document.addEventListener('DOMContentLoaded', function() {

  let editingIndex = null;

  // Storage helpers
  function getStoredProducts() {
      return JSON.parse(localStorage.getItem('products') || '[]');
  }
  function setStoredProducts(arr) {
      localStorage.setItem('products', JSON.stringify(arr));
  }

  let currentPage = 1, productsPerPage = 10, filterStr = '';

  // Safe Selectors
  const searchbar = document.querySelector('.searchbar');
  const addBtn = document.querySelector('.add-product');
  const filterBtn = document.querySelector('.filter-btn');
  const downloadBtn = document.querySelector('.download-btn');
  const prevBtn = document.querySelector('.prev-btn');
  const nextBtn = document.querySelector('.next-btn');
  const viewAll = document.querySelector('.view-all');
  const pageIndicator = document.querySelector('.page-indicator');
  const addProductModal = document.getElementById('addProductModal');
  const addProductForm = document.getElementById('addProductForm');
  const imageInput = document.getElementById('productImage');
  const imagePreview = document.getElementById('imagePreview');
  const closeModalBtns = document.querySelectorAll('.close-modal');
  const filterModal = document.getElementById('filterModal');
  const filterModalOptions = document.getElementById('categoryOptions');
  const productContainer = document.getElementById('productOverview'); 
  const products = getStoredProducts();
  const lastSearch = localStorage.getItem('lastSearch') || '';
  const backBtn = document.getElementById('backBtn');

  // Back Button
  if(backBtn){
    backBtn.addEventListener('click', function(){
      // Clear search storage
      localStorage.removeItem('lastSearch');
      if(searchbar) searchbar.value = '';

      // Sections
      const inventorySection = document.querySelector('.overall-inventory-container');
      const productsSection = document.querySelector('.products');

      // Show default inventory view
      if(inventorySection) inventorySection.style.display = '';
      if(productsSection) productsSection.style.display = '';

      // Hide product overview
      productContainer.style.display = 'none';

      // Hide back button in default inventory view
      backBtn.style.display = 'none';
    });
  }

  // Restore search on page load
  if(lastSearch){
    searchbar.value = lastSearch;

    const inventorySection = document.querySelector('.overall-inventory-container');
    const productsSection = document.querySelector('.products');

    // Hide default inventory view
    if(inventorySection) inventorySection.style.display = 'none';
    if(productsSection) productsSection.style.display = 'none';

    // Show product overview
    productContainer.style.display = 'flex';

    if(backBtn) backBtn.style.display = 'inline-block';

    // Filter products
    const filteredProducts = products.filter(prod =>
      (prod.name && prod.name.toLowerCase().includes(lastSearch)) ||
      (prod.id && prod.id.toLowerCase().includes(lastSearch)) ||
      (prod.category && prod.category.toLowerCase().includes(lastSearch))
    );
    renderProductOverview(filteredProducts);
  }

  // Search Products
  if (searchbar) {
    searchbar.addEventListener('keydown', function(e) {

      if (e.key === 'Enter') {
        const query = this.value.toLowerCase().trim();
        localStorage.setItem('lastSearch', query); // Store last search

        const backBtn = document.getElementById('backBtn');
        const inventorySection = document.querySelector('.overall-inventory-container');
        const productsSection = document.querySelector('.products');

        if(query){
          // Hide default inventory view
          if(backBtn) backBtn.style.display = 'inline-block';
          if(inventorySection) inventorySection.style.display = 'none';
          if(productsSection) productsSection.style.display = 'none';

          // Show product overview
          productContainer.style.display = 'flex';

          // Filter products
          const filteredProducts = products.filter(prod =>
            (prod.name && prod.name.toLowerCase().includes(query)) ||
            (prod.id && prod.id.toLowerCase().includes(query)) ||
            (prod.category && prod.category.toLowerCase().includes(query))
          );
          renderProductOverview(filteredProducts);
        } else{
          // Show default inventory view
          if(backBtn) backBtn.style.display = 'none';
          if(inventorySection) inventorySection.style.display = '';
          if(productsSection) productsSection.style.display = '';
          productContainer.style.display = 'none';
        }
      }
    });
  }

  // Render function for overview cards
  function renderProductOverview(productsList){
    if(!productContainer) return;
    productContainer.innerHTML = ''; // Clear existing content

    if(productsList.length === 0){
      productContainer.innerHTML = '<p style="color:#888;">No products found.</p>';
      return;
    }

    productsList.forEach((prod, idx) => {
      const card = document.createElement('div');
      card.className = 'product-card';

      card.innerHTML = `
        <div class="product-card">
          <div class="overview-header">
            <h3>${prod.name}</h3>
            <div class="product-actions">
              <button class="edit-btn"><i class='bx bx-pencil' ></i>Edit</button>
              <button class="download-btn">Download</button>
            </div>
          </div>

          <div class="overview-tabs">
            <span>Overview</span>
          </div>

          <div class="overview-content">

          <div class="product-info">
            <h4>Product Details</h4>

            <div class="details-row">
              <label>Product Name</label> 
              <span>${prod.name}</span>
            </div>

            <div class="details-row">
              <label>Product ID</label> 
              <span>${formatProductId(prod.id)}</span>
            </div>

            <div class="details-row">
              <label>Product category</label> 
              <span>${prod.category}</span>
            </div>

            <div class="details-row">
              <label>Expiry Date</label> 
              <span>${prod.expiry}</span>
            </div>
          </div>

            <div class="product-img-box">
              <img src="${prod.image}" alt="${prod.name}" class="product-img"/>
            </div>
          </div>
        </div>
        `;
        productContainer.appendChild(card);
    })
  }

  function openProductForm() {
    addProductModal.classList.add('show');
  }

  // Add Product Button & Modal
  if (addBtn) {
    addBtn.addEventListener('click', function() {
        editingIndex = null; // ensure ADD mode
        document.getElementById("submitBtn").textContent = "Add Product";
        addProductForm.reset(); // clear fields
        setImagePreview(null); // reset preview
        openProductForm();
    });
  }

  function closeModal() {
    addProductModal.classList.remove('show');
    if (filterModal) filterModal.classList.remove('show');
  }
  if (closeModalBtns) {
    closeModalBtns.forEach(btn => btn.addEventListener('click', closeModal));
  }

  // Clear form on close
  if (addProductModal) {
    addProductModal.addEventListener('transitionend', function() {
      if (!this.classList.contains('show')) {
        addProductForm.reset();
        setImagePreview(null);
      }
    });
  }

  // Image Preview
  if(imageInput && imagePreview) {
    imageInput.addEventListener('change', function() {
      const file = this.files[0];
      if(file){
          const reader = new FileReader();
          reader.onload = function(e) {
              showImage(e.target.result);
          };
          reader.readAsDataURL(file);
      }
    });
  }

  function showImage(src){
    const img = document.getElementById("previewImg");
    const placeholder = imagePreview.querySelector(".upload-placeholder");

    img.src = src;
    img.style.display = "block";
    placeholder.style.display = "none";
  }

  function resetImagePreview(){
    const img = document.getElementById("previewImg");
    const placeholder = imagePreview.querySelector(".upload-placeholder");

    img.style.display = "none";
    img.src = "";
    placeholder.style.display = "block";
  }

  // Instead of replacing the entire innerHTML, keep the wrapper and apply the image inside.
  function setImagePreview(src){
    if(!src){
      resetImagePreview();
    } else{
      showImage(src);
    }
  }

  // Add Product Form
  if(addProductForm) {
    addProductForm.addEventListener('submit', function(e){
      e.preventDefault();
      const fd = new FormData(this);
      const product = {
        name: fd.get('productName') || "",
        id: fd.get('productID') || "",
        quantity: fd.get('quantity') || "",
        price: fd.get('buyingPrice') || "",
        expiry: fd.get('expiryDate') || "",
        category: fd.get('category') || "",
        unit: fd.get('unit') || "",
        image: ""
      };
      const imageFile = imageInput && imageInput.files[0];
      if(imageFile){
        const reader = new FileReader();
        reader.onload = function(e) {
          product.image = e.target.result;
          saveAndRender();
        };
        reader.readAsDataURL(imageFile);
      } else {
        saveAndRender();
      }
      function saveAndRender(){
        const prods = getStoredProducts();
        if(editingIndex !== null){
          prods[editingIndex] = product; // update
          editingIndex = null;
          document.getElementById("submitBtn").textContent = "Add Product";
        } else{
          prods.unshift(product); // add new
        }
        setStoredProducts(prods);
        renderProducts(currentPage, filterStr);
        closeModal();
        addProductForm.reset();
        resetImagePreview();
      }
    });
  }

  // Download All (CSV)
  if (downloadBtn) {
    downloadBtn.addEventListener('click', function() {
      const prods = getStoredProducts();
      if (prods.length === 0) {
          alert('No products to download!');
          return;
      }
      let csv = 'Product Name,Product ID,Quantity,Unit,Price,Expiry Date,Category\n' +
        prods.map(p => `${p.name},${p.id},${p.quantity},${p.unit},${p.price},${p.expiry},${p.category}`).join('\n');
      const blob = new Blob([csv], {type: 'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'products.csv';
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 200);
    });
  }

  // Pagination
  if (prevBtn) {
    prevBtn.addEventListener('click', function() {
      if(currentPage > 1) {
          currentPage -= 1;
          renderProducts(currentPage, filterStr);
      }
    });
  }
  if (nextBtn) {
    nextBtn.addEventListener('click', function() {
      const all = getStoredProducts();
      let filtered = all;
      if(filterStr.trim()){
          const str = filterStr.toLowerCase();
          filtered = all.filter(p => 
            (p.name && p.name.toLowerCase().includes(str)) ||
            (p.id && p.id.toLowerCase().includes(str)) ||
            (p.category && p.category.toLowerCase().includes(str))
          );
      }
      const totalPages = Math.max(1, Math.ceil(filtered.length / productsPerPage));
      if(currentPage < totalPages){
          currentPage += 1;
          renderProducts(currentPage, filterStr);
      }
    });
  }
  if (viewAll) {
    viewAll.addEventListener('click', function(e) {
      e.preventDefault();
      currentPage = 1;
      productsPerPage = getStoredProducts().length || 1;
      renderProducts(currentPage, filterStr);
    });
  }

  // Filter Modal
  if (filterBtn && filterModal && filterModalOptions) {
    filterBtn.addEventListener('click', function () {
      // Populate options
      const categories = [...new Set(getStoredProducts().map(p => p.category).filter(Boolean))];
      filterModalOptions.innerHTML = categories.length
        ? categories.map(cat =>
          `<button type="button" class="category-option">${cat}</button>`
        ).join('')
        : '<em style="color:#aaa;">No categories found</em>';
      filterModal.classList.add('show');

      filterModalOptions.querySelectorAll('.category-option').forEach(btn => {
        btn.onclick = function() {
          filterStr = btn.textContent;
          currentPage = 1;
          renderProducts(currentPage, filterStr);
          filterModal.classList.remove('show');
          if (searchbar) searchbar.value = filterStr;
        }
      });
    });
  }

  // Allow modal close with .close-modal anywhere in filter modal:
  if (filterModal) {
    filterModal.querySelectorAll('.close-modal').forEach(btn => btn.onclick = closeModal);
  }

  // Immediately set ProductID, Quantity as Packets, and Price as Peso
  function formatProductId(id){
    return `PRD-${String(id).padStart(4, '0')}`;
  }

  function formatQuantity(qty, unit){
    return `${qty} ${unit}`;
  }

  function formatPrice(price){
    return `₱${parseFloat(price).toFixed(2)}`;
  }

  // Main Render Function
  function renderProducts(page = 1, searchTerm = '') {
    const all = getStoredProducts();
    let filtered = all;
    if(searchTerm && searchTerm.trim()) {
        const s = searchTerm.toLowerCase();
        filtered = all.filter(p => 
          (p.name && p.name.toLowerCase().includes(s)) ||
          (p.id && p.id.toLowerCase().includes(s)) ||
          (p.category && p.category.toLowerCase().includes(s))
        );
    }
    const total = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / productsPerPage));
    if (page > totalPages) page = totalPages;
    const start = (page - 1) * productsPerPage, end = start + productsPerPage;
    const sub = filtered.slice(start, end);
    const tbody = document.getElementById('productTableBody');
    if (tbody) {
      tbody.innerHTML =
            sub.length === 0
                ? '<tr class="no-products"><td colspan="8">No products yet.</td></tr>'
                : sub.map(p => `
                <tr>
                    <td>${p.name}</td>
                    <td>${formatProductId(p.id)}</td>
                    <td>${formatQuantity(p.quantity, p.unit)}</td>
                    <td>${formatPrice(p.price)}</td>
                    <td>${p.expiry}</td>
                    <td>
                    <span class="category">
                        ${p.category}
                        <svg class="down-arrow" width="14" height="14" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" fill="#e8a02e"/></svg>
                    </span>
                    </td>
                    <td>
                    <button class="action-btn edit" title="Edit">
                        <i class='bx bxs-edit'></i>
                    </button>
                    <button class="action-btn delete" title="Delete">
                        <i class='bx bx-trash-alt' ></i>
                    </button>
                    </td>
                </tr>
                `).join('')

      // Edit/Delete Buttons
      tbody.querySelectorAll('.action-btn.delete').forEach((btn, idx) => {
          btn.onclick = function() {
              const all = getStoredProducts();
              all.splice((currentPage-1)*productsPerPage + idx, 1);
              setStoredProducts(all);
              renderProducts(currentPage, filterStr);
          };
      });
      tbody.querySelectorAll('.action-btn.edit').forEach((btn, idx) => {
        btn.onclick = function() {
            const all = getStoredProducts();
            const prodIndex = (currentPage - 1) * productsPerPage + idx;
            const prod = all[prodIndex];

            // Change button text to Update
            document.getElementById("submitBtn").textContent = "Update Product";

            // Store the index so the form knows it's editing
            editingIndex = prodIndex;

            // Open the form Popup
            openProductForm();

            if(prod.image){
              setImagePreview(prod.image);
            } else{
              setImagePreview(null);
            }

            // Pre-fill each field
            document.getElementById('productName').value = prod.name;
            document.getElementById('productID').value = prod.id;
            document.getElementById('quantity').value = prod.quantity;
            document.getElementById('buyingPrice').value = prod.price;
            document.getElementById('expiryDate').value = prod.expiry;
            document.getElementById('category').value = prod.category;
            document.getElementById('unit').value = prod.unit;
        };
      });
    }
    setPagination(total, page);
  }

  function setPagination(total, page) {
      const totalPages = Math.max(1, Math.ceil(total / productsPerPage));
      if(pageIndicator)
        pageIndicator.textContent = `Page ${page} of ${totalPages}`;
      if(prevBtn) prevBtn.disabled = page === 1;
      if(nextBtn) nextBtn.disabled = page === totalPages;
  }

  // Startup render
  renderProducts(currentPage, filterStr);
});