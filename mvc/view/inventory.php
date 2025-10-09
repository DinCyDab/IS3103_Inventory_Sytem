<?php
    class Inventory {
        private $products = [];
        private $overviewStats = [
            ["label" => "Categories", "value" => 0, "footer" => "Last 7 days", "highlight" => "blue"],
            ["label" => "Total Products", "value" => 0, "footer" => "Last 7 days", "extra" => "₱0", "highlight" => "orange"],
            ["label" => "Top Selling", "value" => 0, "footer" => "Last 7 days", "extra" => "₱0", "highlight" => "purple"],
            ["label" => "Low Stocks", "value" => 0, "footer" => "Ordered", "extra" => "0 Not in stock", "highlight" => "red"],
        ];

    public function render() { ?>
    <div class="topbar">
        <input type="search" class="searchbar" placeholder="Search product or order" />
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
                <button class="filter-btn">Filters</button>
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
                <tr class="no-products"><td colspan="7">No products yet.</td></tr>
            </tbody>
        </table>

        <div class="table-nav">
            <button>Previous</button>
            <span>Page 1 of 10 <a href="#" class="view-all">See All</a></span>
            <button>Next</button>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">New Product</h3>
            <form id="addProductForm">
                <div class="image-upload-section">
                    <label for="productImage" class="image-upload-area">
                        <div id="imagePreview" class="image-preview">
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
                    <input type="text" name="productName" placeholder="Enter product name" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Product ID</label>
                    <input type="text" name="productID" placeholder="Enter product ID" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Quantity</label>
                    <input type="text" name="quantity" placeholder="Enter product quantity" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Buying Price</label>
                    <input type="text" name="buyingPrice" placeholder="Enter buying price" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Category</label>
                    <select name="category" required>
                        <option value="" disabled selected>Select product category</option>
                        <option value="Groceries">Groceries</option>
                    </select>
                </div>
                <div class="form-row">
                    <label class="field-label">Unit</label>
                    <input type="text" name="unit" placeholder="Enter product unit" required>
                </div>
                <div class="form-row">
                    <label class="field-label">Expiry Date</label>
                    <input type="text" name="expiryDate" placeholder="Enter expiry date" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="discard-btn" onclick="closeModal()">Discard</button>
                    <button type="submit" class="add-product-btn">Add Product</button>
                </div>
            </form>
        </div>
    </div>
<?php } }
?>