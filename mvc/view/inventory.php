<?php
    class InventoryView {
        private $products = [];
        private $overviewStats = [
            ["label" => "Categories", "value" => 0, "footer" => "Last 7 days", "highlight" => "blue"],
            ["label" => "Total Products", "value" => 0, "footer" => "Last 7 days", "extra" => "₱0", "highlight" => "orange"],
            ["label" => "Top Selling", "value" => 0, "footer" => "Last 7 days", "extra" => "₱0", "highlight" => "purple"],
            ["label" => "Low Stocks", "value" => 0, "footer" => "Ordered", "extra" => "0 Not in stock", "highlight" => "red"],
        ];

    public function render() { ?>
    <div class="header"></div>

    <div class="topbar">
        <div class="search-wrapper">
            <i class='bx bx-search' ></i>
            <input type="search" class="searchbar" placeholder="Search product or order" />
        </div>
    </div>

    <!-- Overall Inventory Section -->
    <div class="overall-inventory-container">
        <div class="inventory-title">
            <h3>Overall Inventory</h3>
        </div>

        <div class="inventory-stats-row">
            <?php foreach ($this->overviewStats as $index => $stat): ?>
                <div class="inventory-stat-col">
                    <div class="inventory-main-label <?= $stat['highlight'] ?>">
                        <?= htmlspecialchars($stat['label']) ?>
                    </div>

                    <?php if ($stat['label'] === 'Low Stocks'): ?>
                        <div class="inventory-lowstocks-flex">
                            <div>
                                <div class="inventory-main-value"><?= htmlspecialchars($stat['value']) ?></div>
                                <div class="inventory-main-footer"><?= htmlspecialchars($stat['footer']) ?></div>
                            </div>
                            <div>
                                <div class="inventory-main-value">0</div>
                                <div class="inventory-main-footer">Not in stock</div>
                            </div>
                        </div>
                    <?php elseif ($stat['label'] === 'Total Products' || $stat['label'] === 'Top Selling'): ?>
                        <div class="inventory-paired-flex">
                            <div>
                                <div class="inventory-main-value"><?= htmlspecialchars($stat['value']) ?></div>
                                <div class="inventory-main-footer"><?= htmlspecialchars($stat['footer']) ?></div>
                            </div>
                            <div>
                                <div class="inventory-main-value"><?= htmlspecialchars($stat['extra']) ?></div>
                                <div class="inventory-main-footer">
                                    <?= $stat['label'] === 'Total Products' ? 'Revenue' : 'Cost'; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="inventory-main-value"><?= htmlspecialchars($stat['value']) ?></div>
                        <div class="inventory-main-footer"><?= htmlspecialchars($stat['footer']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($index < count($this->overviewStats) - 1): ?>
                    <div class="inventory-stat-divider"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Section -->
    <div class="products">
        <div class="products-header">
            <h3>Products</h3>
            <div class="button-group">
                <button class="add-product" id="showAddProductModal">Add Product</button>
                <button class="filter-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(149, 145, 145, 1);transform: ;msFilter:;"><path d="M7 11h10v2H7zM4 7h16v2H4zm6 8h4v2h-4z"></path></svg>Filters</button>
                <button class="download-btn">Download all</button>
            </div>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th>Products</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Expiry Date</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                <tr class="no-products"><td colspan="8">No products yet.</td></tr>
            </tbody>
        </table>

        <div class="table-nav">
            <button class="prev-btn">Previous</button>

            <div class="pages">
                <span class="page-indicator">Page 1</span>
                <a href="#" class="view-all">See All</a>
            </div>

            <button class="next-btn">Next</button>
        </div>
    </div>

    <!-- Product Overview -->
    <button id="backBtn" class="back-btn" style="display:none;"><i class='bx bx-arrow-back' ></i></button>
    <div id="productOverview" class="product-overview-container" style="display:none;"></div>
    <button id="backToTopBtn" class="bTopButton">
        <i class='bx bx-up-arrow-alt'></i>
    </button>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">New Product</h3>
            <!-- SVG close button -->
            <form id="addProductForm" method="POST" action="index.php?view=createProduct">
                <div class="image-upload-section">
                    <label for="productImage" class="image-upload-area">
                        <div id="imagePreview" class="image-preview">
                            <img id="previewImg" style="display:none;">
                            <div class="upload-placeholder">
                                <div class="upload-icon"></div>
                                <div class="upload-text">
                                    Drag image here<br>or<br>
                                    <span class="browse-link">Browse image</span>
                                </div>
                            </div>
                        </div>
                    </label>
                    <input type="file" id="productImage" name="productImage" accept="image/*" style="display:none;">
                </div>

                <div class="form-row">
                    <label class="field-label">Product Name</label>
                    <input type="text" id="productName" name="productName" placeholder="Enter product name" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Product ID</label>
                    <input type="text" id="productID" name="productID" placeholder="Enter product ID" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Quantity</label>
                    <input type="text" id="quantity" name="quantity" placeholder="Enter product quantity" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Buying Price</label>
                    <input type="text" id="price" name="price" placeholder="Enter buying price" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Category</label>
                    <select name="category" id="category" class="styled-category">
                        <option value="" disabled selected>Select product category</option>
                        <optgroup label="Food & Beverages">
                            <option value="Beverage">Softdrinks/Juice/Water</option>
                            <option value="Snacks">Chips/Cookies/Candies</option>
                            <option value="Groceries">Instant Noodles/Rice/Canned Goods</option>
                            <option value="Spices">Spices/Condiments</option>
                        </optgroup>
                        <optgroup label="Household & Personal Care">
                            <option value="Personal Care">Soap/Shampoo/Toothpaste</option>
                            <option value="Cleaning Supplies">Detergent/Cleaning Supplies</option>
                            <option value="Tooilet Sanitaries">Toilet Paper/Sanitary Pad</option>
                        </optgroup>
                        <optgroup label="Miscellaneous/Others">
                            <option value="Cigar/Alcohol">Cigarettes/Alcohol</option>
                            <option value="Stationaries">Stationary/Batteries/Small Toys</option>
                            <option value="Frozen Items">Frozen Items or Perishables</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-row">
                    <label class="field-label">Unit</label>
                    <input type="text" id="unit" name="unit" placeholder="Enter product unit" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Expiry Date</label>
                    <input type="text" id="expiryDate" name="expiryDate" placeholder="Enter expiry date" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="discard-btn close-modal">Discard</button>
                    <button type="submit" id="submitBtn" class="add-product-btn">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <div id="filterModal" class="modal">
        <div class="modal-content" style="position:relative;">
            <button type="button" class="close-modal modal-x" aria-label="Close" style="position: absolute; top: 22px; right: 22px; background: none; border: none;">
            <svg width="25" height="25" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18" stroke="#999" stroke-width="2" stroke-linecap="round"/>
                <path d="M6 6L18 18" stroke="#999" stroke-width="2" stroke-linecap="round"/>
            </svg>
            </button>
            <h3 style="margin-top: 8px;">Select Category</h3>
            <div id="categoryOptions" style="display: flex; flex-wrap: wrap; gap:10px; margin: 15px 0;"></div>
        </div>
    </div>

    <link rel="stylesheet" href="./public/src/css/inventory.css">
    <script src="./public/src/js/inventoryscript.js"></script>
<?php } }
?>