<?php 
    class Inventory{
        private $products = [];

        public function __contruct($products = []){
            // Add data
        }

        public function render(){
            ?>
            <header class="topbar">
                <h2>Inventory</h2>
                <input type="search" placeholder="Search product or order" class="searchbar"/>
            </header>

            <section class="overview">
                <!-- Overview boxes, stats or data -->
            </section>

            <section class="products">
                <div class="products-header">
                    <h3>Products</h3>
                    <div class="button-group">
                        <button class="add-product">Add Product</button>
                        <button class="filter-btn">Filters</button>
                        <button class="download-btn">Download all</button>
                    </div>
                </div>
                <table>
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
                        <?php foreach ($this->products as $index => $product): ?>
                            <tr data-id="<?php echo $product["id"]; ?>">
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['expiry_date']); ?></td>
                                <td><span class="category"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                <td>
                                    <button class="action-btn edit" onclick="editProduct('<?php echo $product['id']; ?>')">Edit</button>
                                    <button class="action-btn delete" onclick="deleteProduct('<?php echo $product['id']; ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div id="addProductModal" class="modal">
                    <div class="modal-content">
                        <h2>New Product</h2>
                        <form action="addProductForm" enctype="miltipart/form-data" method="POST" action="add_product.php">
                            <div class="image-upload">
                                <label for="productImage" class="image-label">
                                    <div id="imagePreview" class="image-preview">Drag image here or <span class="browse-link">Browse image</span></div>
                                </label>
                                <input type="file" id="productImage" name="productImage" accept="image/*" style="display:none;">
                            </div>
                            <input type="text" placeholder="Enter product name" required>
                            <input type="text" placeholder="Enter product ID" required>
                            <input type="text" placeholder="Enter product quantity" required>
                            <input type="text" placeholder="Enter buying price" required>
                            <input type="text" placeholder="Select product category" required>
                            <input type="text" placeholder="Enter product unit" required>
                            <input type="text" placeholder="Enter expiry date" required>
                            <div class="modal-actions">
                                <button type="submit" class="add-btn">Add Product</button>
                                <button type="button" class="discard-btn" onclick="closeModal()">Discard</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-nav">
                    <button onclick="previousPage()">Previous</button>
                    <span>Page 1 of 10 <a href="#" onclick="seeAll()">See All</a></span>
                    <button onclick="nextPage()">Next</button>
                </div>
            </section>
            <?php
        }
    }
?>