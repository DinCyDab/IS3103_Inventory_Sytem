document.addEventListener("DOMContentLoaded", () => {
  // Search Bar
  // Storage Helpers
  function getStoredProducts() {
      return JSON.parse(localStorage.getItem('products') || '[]');
  }

  // Safe Selectors
  const searchbar = document.querySelector('.searchbar');
  const productContainer = document.getElementById('productOverview'); 
  const lastSearch = localStorage.getItem('lastSearch') || '';
  const backBtn = document.getElementById('backBtn');
  const inventorySection = document.querySelector('.overall-inventory-container');
  const productsSection = document.querySelector('.products');
  const products = getStoredProducts();
  

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
});