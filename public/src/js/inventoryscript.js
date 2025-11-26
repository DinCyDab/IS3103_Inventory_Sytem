document.addEventListener('DOMContentLoaded', function() {

  let editingIndex = null;

  // Storage helpers
  function getStoredProducts() {
      return JSON.parse(localStorage.getItem('products') || '[]');
  }
  function setStoredProducts(arr) {
      localStorage.setItem('products', JSON.stringify(arr));
  }

  let currentPage = 1, productsPerPage = 5, filterStr = '';

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

      // Clear see all
      localStorage.removeItem('seeAll');
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
      (prod.productName && prod.productName.toLowerCase().includes(lastSearch)) ||
      (prod.productID && prod.productID.toLowerCase().includes(lastSearch)) ||
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
            (prod.productName && prod.productName.toLowerCase().includes(query)) ||
            (prod.productID && prod.productID.toLowerCase().includes(query)) ||
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
        location.reload();
      }
    });
  }

  // Render function to see all products
  function renderAllProductsAsCards(){
    const allProducts = getStoredProducts();
    if(!productContainer) return;

    // Hide default inventory sections
    const inventorySection = document.querySelector('.overall-inventory-container');
    const productsSection = document.querySelector('.products');
    if(inventorySection) inventorySection.style.display = 'none';
    if(productsSection) productsSection.style.display = 'none';

    // Show overview container and back button
    productContainer.style.display = 'flex';
    if(backBtn) backBtn.style.display = 'inline-block';

    // Clear and render all cards
    productContainer.innerHTML = '';
    if(allProducts.length === 0){
      productContainer.innerHTML = '<p style="color:#888;">No products found.</p>';
      return;
    }

    allProducts.forEach((prod, idx) => {
      const card = document.createElement('div');
      card.className = 'product-card';

      card.innerHTML = `
        <div class="product-card">
          <div class="overview-header">
            <h3>${prod.productName}</h3>
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
                <span>${prod.productName}</span>
              </div>
              <div class="details-row">
                <label>Product ID</label> 
                <span>${formatProductId(prod.productID)}</span>
              </div>
              <div class="details-row">
                <label>Product category</label> 
                <span>${prod.category}</span>
              </div>
              <div class="details-row">
                <label>Expiry Date</label> 
                <span>${prod.expiryDate}</span>
              </div>
            </div>
            <div class="product-img-box">
              <img src="${prod.image}" alt="${prod.productName}" class="product-img"/>
            </div>
          </div>
        </div>
      `;
      productContainer.appendChild(card);
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
            <h3>${prod.productName}</h3>
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
                <span>${prod.productName}</span>
              </div>
              <div class="details-row">
                <label>Product ID</label> 
                <span>${formatProductId(prod.productID)}</span>
              </div>
              <div class="details-row">
                <label>Product category</label> 
                <span>${prod.category}</span>
              </div>
              <div class="details-row">
                <label>Expiry Date</label> 
                <span>${prod.expiryDate}</span>
              </div>
            </div>
            <div class="product-img-box">
              <img src="public/images/uploads/${prod.image}" alt="${prod.productName}" class="product-img"/>
            </div>
          </div>
        </div>
      `;
      productContainer.appendChild(card);
    });
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
  let uploadedFile = null;

  if(imageInput && imagePreview) {
    imageInput.addEventListener('change', function() {
      uploadedFile = this.files[0] || null;
      if(uploadedFile){
          const reader = new FileReader();
          reader.onload = function(e) {

            showImage(e.target.result);
          };
          reader.readAsDataURL(uploadedFile);
      } else{
          setImagePreview(null);
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
  addProductForm.addEventListener('submit', function(e){
    e.preventDefault();
    const product = {
        productID: addProductForm.productID.value.trim(),
        productName: addProductForm.productName.value.trim(),
        quantity: addProductForm.quantity.value.trim(),
        unit: addProductForm.unit.value.trim(),
        price: addProductForm.price.value.trim(),
        expiryDate: addProductForm.expiryDate.value.trim(),
        category: addProductForm.category.value.trim(),
        image: uploadedFile ? uploadedFile.name : document.getElementById("previewImg").src
    };

    const url = editingIndex !== null ? 'index.php?view=updateProduct' : 'index.php?view=createProduct';
    const fd = new FormData();
    for(const k in product) fd.append(k, product[k]);
    if(uploadedFile) fd.append('image', uploadedFile);

    fetch(url, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(json => {
        if(json.success){
            const prods = getStoredProducts();
            if(editingIndex !== null) prods[editingIndex] = product;
            else prods.unshift(product);
            setStoredProducts(prods);
            renderProducts(currentPage, filterStr);
            closeModal();
        } else alert(json.message);
    });
});

  // Download All CSV
  if (downloadBtn) {
    downloadBtn.addEventListener('click', function() {
      const prods = getStoredProducts();
      if (prods.length === 0) {
          alert('No products to download!');
          return;
      }
      let csv = 'Product Name,Product ID,Quantity,Unit,Price,Expiry Date,Category\n' +
        prods.map(p => `${p.productName},${p.productID},${p.quantity},${p.unit},${p.price},${p.expiryDate},${p.category}`).join('\n');
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

  // PAGINATION
  // Previous button
  if(prevBtn){
    prevBtn.addEventListener('click', function(){
      if(currentPage > 1){
        currentPage -= 1;
        renderProducts(currentPage, filterStr);
      }
    });
  }

  // Next Button
  if (nextBtn) {
    nextBtn.addEventListener('click', function() {
      const all = getStoredProducts();
      let filtered = all;

      if(filterStr.trim()){
          const str = filterStr.toLowerCase();
          filtered = all.filter(p => 
            (p.productName && p.productName.toLowerCase().includes(str)) ||
            (p.productID && p.productID.toLowerCase().includes(str)) ||
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

  // View All Products Only
  if (viewAll) {
    viewAll.addEventListener('click', function(e) {
      e.preventDefault();

      localStorage.setItem('seeAll', 'true');

      renderAllProductsAsCards();
    });
  }

  // On page load, check See All
  const seeAllFlag = localStorage.getItem('seeAll');

  if(seeAllFlag === 'true'){
      renderAllProductsAsCards();
      productContainer.style.display = 'flex';
      if(backBtn) backBtn.style.display = 'inline-block';
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
    if(!id) return 'PRD-0000'; // Fallback for empty/undefined

    // Remove any existing 'PRD-' prefix
    let numericPart = id.toString().replace(/^PRD-/, '');

    // Keep only digits
    numericPart = numericPart.replace(/\D/g, '');

    // Pad to 4 digits
    numericPart = numericPart.padStart(4, '0');

    return `PRD-${numericPart}`;
  }

  function formatQuantity(qty, unit){
    return `${qty} ${unit}`;
  }

  function formatPrice(price){
    return `₱${parseFloat(price).toFixed(2)}`;
  }

  // Helper for updatePaginationButtons
  function updatePaginationButtons(totalPages){
    if(prevBtn) prevBtn.disabled = currentPage <= 1;
    if(nextBtn) nextBtn.disabled = currentPage >= totalPages;
  }


  // Main Render Function
  function renderProducts(page = 1, searchTerm = '') {
    const all = getStoredProducts();
    let filtered = all;
    if(searchTerm && searchTerm.trim()) {
        const s = searchTerm.toLowerCase();
        filtered = all.filter(p => 
          (p.productName && p.productName.toLowerCase().includes(s)) ||
          (p.productID && p.productID.toLowerCase().includes(s)) ||
          (p.category && p.category.toLowerCase().includes(s))
        );
    }
    const total = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / productsPerPage));

    if (page > totalPages) page = totalPages;
    // Update page indicator
    if(pageIndicator){
      pageIndicator.textContent = `Page ${page} of ${totalPages}`;
    }

    // Enable/disable Prev/Next buttons
    updatePaginationButtons(totalPages);

    const start = (page - 1) * productsPerPage, end = start + productsPerPage;
    const sub = filtered.slice(start, end);
    const tbody = document.getElementById('productTableBody');
    if (tbody) {
      tbody.innerHTML =
            sub.length === 0
                ? '<tr class="no-products"><td colspan="8">No products yet.</td></tr>'
                : sub.map(p => `
                <tr>
                    <td>${p.productName}</td>
                    <td>${formatProductId(p.productID)}</td>
                    <td>${formatQuantity(p.quantity, p.unit)}</td>
                    <td>${formatPrice(p.price)}</td>
                    <td>${p.expiryDate}</td>
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
          btn.onclick = function(){
          const prodIndex = (currentPage-1)*productsPerPage + idx;
          const prod = getStoredProducts()[prodIndex];

          fetch('index.php?view=deleteProduct', {
              method: 'POST',
              body: new URLSearchParams({ productID: prod.productID }),
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
          })
          .then(res => res.json())
          .then(json => {
              if(json.success){
                  const all = getStoredProducts();
                  all.splice(prodIndex, 1);
                  setStoredProducts(all);
                  renderProducts(currentPage, filterStr);
              } else alert(json.message);
          });
        }
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
              setImagePreview(`public/images/uploads/${prod.image}`);
            } else{
              setImagePreview(null);
            }

            // Pre-fill each field
            document.getElementById('productName').value = prod.productName;
            document.getElementById('productID').value = prod.productID;
            document.getElementById('quantity').value = prod.quantity;
            document.getElementById('price').value = prod.price;
            document.getElementById('expiryDate').value = prod.expiryDate;
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