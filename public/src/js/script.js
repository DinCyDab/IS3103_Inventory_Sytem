document.addEventListener("DOMContentLoaded", () => {
  // --- Helpers ---
  function getStoredProducts() {
    return JSON.parse(localStorage.getItem('products') || '[]');
  }

  function formatProductId(id){
    if(!id) return 'PRD-0000';
    let numericPart = id.toString().replace(/^PRD-/, '').replace(/\D/g, '');
    return `PRD-${numericPart.padStart(4, '0')}`;
  }

  function renderProductOverview(productsList, container){
    if(!container) return;
    container.innerHTML = '';

    if(productsList.length === 0){
      container.innerHTML = '<p style="color:#888;">No products found.</p>';
      return;
    }

    productsList.forEach(prod => {
      const card = document.createElement('div');
      card.className = 'product-card';

      card.innerHTML = `
        <div class="overview-header">
          <h3>${prod.productName}</h3>
        </div>
        <div class="overview-tabs"><span>Overview</span></div>
        <div class="overview-content">
          <div class="product-info">
            <h4>Product Details</h4>
            <div class="details-row"><label>Product Name</label><span>${prod.productName}</span></div>
            <div class="details-row"><label>Product ID</label><span>${formatProductId(prod.productID)}</span></div>
            <div class="details-row"><label>Category</label><span>${prod.category}</span></div>
            <div class="details-row"><label>Expiry Date</label><span>${prod.expiryDate || 'N/A'}</span></div>
          </div>
          <div class="product-img-box">
            <img src="public/images/uploads/${prod.image || 'default.png'}" alt="${prod.productName}" class="product-img"/>
          </div>
        </div>
      `;
      container.appendChild(card);
    });
  }

  // --- INVENTORY PAGE SEARCH ---
  const searchbar = document.querySelector('.searchbar');
  const productContainer = document.getElementById('productOverview'); 
  const lastSearch = localStorage.getItem('lastSearch') || '';
  const backBtn = document.getElementById('backBtn');
  const inventorySection = document.querySelector('.overall-inventory-container');
  const productsSection = document.querySelector('.products');
  const products = getStoredProducts();

  if(searchbar && productContainer){
    // Restore previous search
    if(lastSearch){
      searchbar.value = lastSearch;
      inventorySection.style.display = 'none';
      productsSection.style.display = 'none';
      productContainer.style.display = 'flex';
      if(backBtn) backBtn.style.display = 'inline-block';

      const filtered = products.filter(p =>
        (p.productName && p.productName.toLowerCase().includes(lastSearch)) ||
        (p.productID && p.productID.toLowerCase().includes(lastSearch)) ||
        (p.category && p.category.toLowerCase().includes(lastSearch))
      );
      renderProductOverview(filtered, productContainer);
    }

    // Enter search
    searchbar.addEventListener('keydown', async (e) => {
      if(e.key !== 'Enter') return;

      const query = searchbar.value.trim().toLowerCase();
      localStorage.setItem('lastSearch', query);

      inventorySection.style.display = 'none';
      productsSection.style.display = 'none';
      productContainer.style.display = 'flex';
      if(backBtn) backBtn.style.display = 'inline-block';

      try {
        const res = await fetch('index.php?view=allProducts', { headers: {'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        const allProducts = data.products || [];

        const filtered = query
          ? allProducts.filter(p =>
              (p.productName && p.productName.toLowerCase().includes(query)) ||
              (p.productID && p.productID.toLowerCase().includes(query)) ||
              (p.category && p.category.toLowerCase().includes(query))
            )
          : allProducts;

        renderProductOverview(filtered, productContainer);
      } catch(err){
        console.error("Inventory search error:", err);
        productContainer.innerHTML = '<p style="color:#888;">Failed to fetch products.</p>';
      }
    });

    // Back button
    if(backBtn){
      backBtn.addEventListener('click', () => {
        localStorage.removeItem('seeAll');
        localStorage.removeItem('lastSearch');
        searchbar.value = '';
        inventorySection.style.display = '';
        productsSection.style.display = '';
        productContainer.style.display = 'none';
        backBtn.style.display = 'none';
      });
    }
  }
});
