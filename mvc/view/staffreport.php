<?php 
    require_once __DIR__ . "/../controller/staffreportcontroller.php";
    class StaffReport{
        public function render(){
            $this->table();

            ?><link rel="stylesheet" href="./public/src/css/inventory.css"><?php
        }

        public function table(){
            ?>
                <div class="products">
                    <div class="products-header">
                        <h3>Record Transaction</h3>
                        <div class="button-group">
                            <button class="add-product" id="showAddProductModal">Add Product</button>
                        </div>
                    </div>

                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <tr>
                                <td>
                                    <button class="action-btn edit" title="Edit" data-pid="4">
                                        <i class="bx bxs-edit"></i>
                                    </button>
                                    <button class="action-btn delete" title="Delete" data-pid="4">
                                        <i class="bx bx-trash-alt"></i>
                                    </button>
                                </td>
                                <td>PRD-0004</td>
                                <td>Ariel</td>
                                <td>1</td>
                                <td>₱4095.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- <div class="table-nav">
                        <button class="prev-btn" disabled="">Previous</button>

                        <div class="pages">
                            <span class="page-indicator">Page 1 of 1</span>
                            <a href="#" class="view-all">See All</a>
                        </div>

                        <button class="next-btn" disabled="">Next</button>
                    </div> -->
                </div>
            <?php
        }
    }    
?>