document.addEventListener('DOMContentLoaded', function() {
  let editingIndex = null;

  let savedPage = parseInt(localStorage.getItem('inventoryCurrentPage')) || 1;

  let currentPage = savedPage;
  const productsPerPage = 5;
  let filterArr = [];

  // Safe Selectors
  const searchbar = document.querySelector('.searchbar');
  const productContainer = document.getElementById('productOverview'); 
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
  const backBtn = document.getElementById('backBtn');
  const inventorySection = document.querySelector('.overall-inventory-container');
  const productsSection = document.querySelector('.products');

  // Back To Top Button
  const backToTopBtn = document.getElementById('backToTopBtn');

  // Image Preview
  let uploadedFile = null;

  // Back Button
  if(backBtn){
    backBtn.addEventListener('click', function(){

      // Clear see all
      localStorage.removeItem('seeAll');
      // Clear search storage
      localStorage.removeItem('lastSearch');
      if(searchbar) searchbar.value = '';

      // Show default inventory view
      if(inventorySection) inventorySection.style.display = '';
      if(productsSection) productsSection.style.display = '';

      // Hide product overview
      productContainer.style.display = 'none';

      // Hide back button in default inventory view
      backBtn.style.display = 'none';
    });
  }

  // Normalize image path so that it will not duplicate
  function normalizeImagePath(img){
    if(!img) return 'public/images/default.png';

    // If path already contains uploads/, do NOT add folder
    if(img.includes('uploads/')){
        return img;
    }

    return `public/images/uploads/${img}`;
  }

  // Render function to see all products
  async function renderAllProductsAsCards(){
    if(!productContainer) return;
    productContainer.innerHTML = '';

    // Hide default inventory sections
    if(inventorySection) inventorySection.style.display = 'none';
    if(productsSection) productsSection.style.display = 'none';

    // Show overview container and back button
    productContainer.style.display = 'flex';
    if(backBtn) backBtn.style.display = 'inline-block';

    try{
        const res = await fetch('index.php?view=allProducts', {
            headers: { 'X-Requested-With':'XMLHttpRequest' }
        });
        const data = await res.json();
        const allProducts = data.products;

    // Clear and render all cards
    if(allProducts.length === 0){
      productContainer.innerHTML = '<p style="color:#888;">No products found.</p>';
      return;
    }

    allProducts.forEach((prod, idx) => {
      const card = document.createElement('div');
      card.className = 'product-card';

      const imageSrc = normalizeImagePath(prod.image);

      card.innerHTML = `
        <div class="product-card">
          <div class="overview-header">
            <h3>${prod.productName}</h3>
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
    } catch(err){
        console.error("Error fetching all products:", err);
    }
  }

  window.addEventListener('scroll', function (){
    if(!backToTopBtn) return;

    // Show button only in SEE ALL or SEARCH mode
    const overviewVisible = productContainer && productContainer.style.display !== 'none';

    if(overviewVisible && this.window.scrollY > 300){
        backToTopBtn.style.display = 'flex';
    } else{
        backToTopBtn.style.display = 'none';
    }
  });

  // Scroll to top on click
  if(backToTopBtn){
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
  }

  // Modal Handling
  function openProductForm() {
    addProductModal.classList.add('show');
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
        document.getElementById('existingImage').value = '';
      }
    });
  }

  // Image Preview
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

  addProductForm.addEventListener('submit', async function(e){
    e.preventDefault();

    let expiryDate = document.getElementById('expiryDate').value.trim();

    // Normalization
    if(/^\d{4}$/.test(expiryDate)){              // YYYY → YYYY-01-01
        expiryDate += '-01-01';
    } else if(/^\d{4}-\d{2}$/.test(expiryDate)){ // YYYY-MM → YYYY-MM-01
        expiryDate += '-01';
    }

    // Validate full YYYY-MM-DD
    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
    if(!datePattern.test(expiryDate)){
        alert('Expiry Date must be in YYYY-MM-DD format!');
        return;
    }

    const formData = new FormData(addProductForm);
    formData.set("expiryDate", expiryDate);
    console.log("EXPIRY SENT TO SERVER:", expiryDate);
    
    // Always include hidden input for old image
    const oldImage = document.getElementById('existingImage').value;
    if(!uploadedFile && oldImage){
        formData.append('existingImage', oldImage);
    } else if(uploadedFile){
        formData.append('image', uploadedFile);
    }

    if(editingIndex !== null) formData.append('productID', editingIndex);

    const url = editingIndex !== null ? 'index.php?view=updateProduct' : 'index.php?view=createProduct';

    try{
        const res = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With':'XMLHttpRequest' }
        });
        const json = await res.json();
        if(json.success){
            currentPage = 1; // reset to first page
            localStorage.setItem('inventoryCurrentPage', currentPage);
            renderProducts(currentPage, filterArr);
            updateOverallStats();
            closeModal();
        } else{
            alert(json.message);
        }
    } catch(err){
        console.error(err);
    }
});

  // Download All CSV
  if (downloadBtn) {
    downloadBtn.addEventListener('click', async function() {
    try{
        // Fetch backend products
        const res = await fetch('index.php?view=allProducts', { headers: {'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        const prods = data.products;

      if (prods.length === 0) {
          alert('No products to download!');
          return;
      }

      const csv = 'Product Name,Product ID,Quantity,Unit,Price,Expiry Date,Category\n' +
      prods.map(p => `${p.productName},${p.productID},${p.quantity},${p.unit},${p.price},${p.expiryDate},${p.category}`).join('\n');

      const blob = new Blob([csv], {type: 'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'products.csv';
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 200);
    } catch(err){ console.error(err); }
    });
  }

  // View All Products Only
  if (viewAll) {
    viewAll.addEventListener('click', function(e) {
      e.preventDefault();

      localStorage.setItem('seeAll', 'true');

      // Reset pagination because all products are shown
      localStorage.removeItem('inventoryCurrentPage');

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

  // Load last filter from localStorage on page load
  let savedFilter = JSON.parse(localStorage.getItem('productFilter') || '[]');
  if(savedFilter.length){
    filterArr = savedFilter;
    renderProducts(currentPage, filterArr);
  }

  // Filter Modal
  if (filterBtn && filterModal && filterModalOptions) {
  filterBtn.addEventListener('click', async function () {
    // Fetch all products first
    const res = await fetch('index.php?view=allProducts', { headers: {'X-Requested-With':'XMLHttpRequest'} });
    const data = await res.json();

    // Get unique categories
    const categories = [...new Set(data.products.map(p => p.category).filter(Boolean))];

    // Clear modal content first
    filterModalOptions.innerHTML = '';

    // Populate category checkboxes
    if(categories.length){
        categories.forEach(cat => {
            const label = document.createElement('label');
            label.innerHTML = `
                <input type="checkbox" value="${cat}" ${filterArr.includes(cat) ? 'checked' : ''}>
                ${cat}
            `;
            filterModalOptions.appendChild(label);
        });
    } else{
        filterModalOptions.innerHTML = '<em style="color:#aaa;">No categories found</em>';
    }

    // Clear old action button first
    const oldActions = filterModalOptions.querySelector('.filter-actions');
    if(oldActions) oldActions.remove();

    // Add Clear/Apply Filter button dynamically
    const actions = document.createElement('div');
    actions.className = 'filter-actions';
    actions.innerHTML = `
        <button type="button" id="clear-filter" class="clear-filter-btn">Clear Filter</button>
        <button type="button" id="apply-filter" class="apply-filter-btn">Apply Filter</button>
    `;
    filterModalOptions.appendChild(actions);

    filterModal.classList.add('show');

    // Attach Handlers
    const clearBtn = actions.querySelector('#clear-filter');
    const applyBtn = actions.querySelector('#apply-filter');

    applyBtn.onclick = () => {
        const checkedBoxes = [...filterModalOptions.querySelectorAll('input[type="checkbox"]:checked')];
        filterArr = checkedBoxes.map(cb => cb.value);
        localStorage.setItem('productFilter', JSON.stringify(filterArr));
        currentPage = 1;
        localStorage.setItem('inventoryCurrentPage', currentPage);
        renderProducts(currentPage, filterArr);
        filterModal.classList.remove('show');
        if(searchbar) searchbar.value = '';
    };

    clearBtn.onclick = () => {
        filterArr = [];
        localStorage.removeItem('productFilter');
        currentPage = 1;
        localStorage.removeItem('inventoryCurrentPage');

        renderProducts(currentPage, filterArr);
        filterModal.classList.remove('show');
        if(searchbar) searchbar.value = '';
    };
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
    return `${qty} ${unit || ''}`;
  }

  function formatPrice(price){
    return `₱${parseFloat(price).toFixed(2)}`;
  }

  // Helper for updatePaginationButtons
  function updatePaginationButtons(totalPages){
    if(prevBtn) prevBtn.disabled = currentPage <= 1;
    if(nextBtn) nextBtn.disabled = currentPage >= totalPages;
  }

  // Main Render Function using backend pagination
  async function renderProducts(page = 1, filter = []) {
    try{
        // Build API URL with pagination and optional filters
        let url = `index.php?view=paginated&page=${page}&limit=${productsPerPage}`;
        if(filterArr.length > 0){
            url += `&categories=${encodeURIComponent(JSON.stringify(filterArr))}`;
        }

        console.log("Fetching page:", page, "limit:", productsPerPage);

        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        const tbody = document.getElementById('productTableBody');
        if(!tbody) return;

        tbody.innerHTML = ''; // clear old rows
    
    // Render rows
    if (data.products.length === 0) {
        tbody.innerHTML = '<tr class="no-products"><td colspan="8">No products yet.</td></tr>'
    } else{
            tbody.innerHTML = data.products.map((p) => `
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
                    <button class="action-btn edit" title="Edit" data-pid="${p.productID}">
                        <i class='bx bxs-edit'></i>
                    </button>
                    <button class="action-btn delete" title="Delete" data-pid="${p.productID}">
                        <i class='bx bx-trash-alt' ></i>
                    </button>
                    </td>
                </tr>
            `).join('');
        }

                // Update pagination indicators
                const totalPages = data.totalPages || 1;
                if(pageIndicator) pageIndicator.textContent = `Page ${page} of ${totalPages}`;
                updatePaginationButtons(totalPages);

                // Attach Edit/Delete events
                attachEditDeleteButtons(data.products);

                // Update stats if backend returned them
                if(data.stats) updateStats(data.stats);

                if(data.stats) refreshStats(data.stats);
        } catch(err){
            console.error("Error fetching paginated products:", err);
        }
    }

    // Attach edit/delete events after rendering rows
    function attachEditDeleteButtons(products = []){
        const tbody = document.getElementById('productTableBody');
        if(!tbody) return;

      // Edit/Delete Buttons
      tbody.querySelectorAll('.action-btn.delete').forEach(btn => {
          btn.onclick = async function(){
          const productID = this.dataset.pid;
                  
        // Delete via backend
        try{
            const res = await fetch('index.php?view=deleteProduct', {
                method: 'POST',
                body: new URLSearchParams({ productID }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if(json.success){
                currentPage = 1; // reset to first page
                localStorage.setItem('inventoryCurrentPage', currentPage);
                renderProducts(currentPage, filterArr); // refresh page
                updateOverallStats();
            } else{
                alert(json.message);
            }
        } catch(err){
            console.error(err);
        }
        };
    });

      tbody.querySelectorAll('.action-btn.edit').forEach(btn => {
        btn.onclick = function() {
            const productID = this.dataset.pid;
            const prod = products.find(p => p && p.productID === productID);
            if(!prod) return;

            editingIndex = productID;

            // Change button text to Update
            document.getElementById("submitBtn").textContent = "Update Product";

            // Open the form Popup
            openProductForm();

            // Pre-fill each field
            document.getElementById('productName').value = prod.productName;
            document.getElementById('productID').value = prod.productID;
            document.getElementById('quantity').value = prod.quantity;
            document.getElementById('price').value = prod.price;
            document.getElementById('expiryDate').value = prod.expiryDate;
            document.getElementById('category').value = prod.category;
            document.getElementById('unit').value = prod.unit;
            if(prod.image){
                setImagePreview(`public/images/uploads/${prod.image}`);
                document.getElementById('existingImage').value = prod.image; // store old image
            } else{
                setImagePreview(null);
                document.getElementById('existingImage').value = '';
            } 
        };
      });
    }

  // Refresh stats when their are changes
  async function updateOverallStats(){
    try{
        const res = await fetch('index.php?view=fetchStats', {
            headers: { 'X-Requested_With': 'XMLHttpRequest' }
        });
        const stats = await res.json();
        if(!Array.isArray(stats)) return;

            // Loop through stats array from backend
            stats.forEach(stat => {
                switch(stat.label){
                    case "Total Products":
                        const totalEl = document.querySelector("#total-products");
                        const revenueEl = document.querySelector("#total-products-revenue");
                        if(totalEl) totalEl.innerText = stat.value;
                        if(revenueEl){
                            revenueEl.innerText = stat.extra;
                            revenueEl.setAttribute('data-full', stat.extra); // Update tooltip
                        }
                        break;

                    case "Low Stocks":
                        const lowEl = document.querySelector("#low-stock");
                        if(lowEl) lowEl.innerText = stat.value;
                        break;

                    case "Top Selling":
                        const topQtyEl = document.querySelector("#top-selling-qty");
                        const topCostEl = document.querySelector("#top-selling-cost");
                        if(topQtyEl) topQtyEl.innerText = stat.value;
                        if(topCostEl){
                            topCostEl.innerText = stat.extra;
                            topCostEl.setAttribute('data-full', stat.extra); // Update tooltip
                        }
                        break;

                    case "Categories":
                        const catEl = document.querySelector("#total-categories");
                        if(catEl) catEl.innerText = stat.value;
                        break;

                    default:
                        break;
                }
            });
        } catch(err){
            console.error("Error fetching overall stats:", err);
        }
    }

  // PAGINATION
  // Previous button
  if(prevBtn) prevBtn.onclick = () => {
    if(currentPage > 1){
        currentPage--;
        localStorage.setItem('inventoryCurrentPage', currentPage);
        renderProducts(currentPage, filterArr);
    }
  };

  // Next Button
  if (nextBtn){
    nextBtn.onclick = () => {
        currentPage++;
        localStorage.setItem('inventoryCurrentPage', currentPage);
        renderProducts(currentPage, filterArr);
    };
  }

  // Startup render
  renderProducts(currentPage, filterArr);
  updateOverallStats();

  // Auto-refresh overall stats every 5 seconds
  setInterval(updateOverallStats, 5000);
});