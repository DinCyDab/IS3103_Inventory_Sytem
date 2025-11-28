<?php 
    require_once __DIR__ . "/../controller/accountcontroller.php";
    class AccountsView{
        private $account_controller;
        private $accounts = [];
        public function __construct(){
            $this->account_controller = new AccountController();

            $this->handleAccountCreation();
            $this->handleDeleteAccount();
            $this->handleUpdateAccount();

            $this->accounts = $this->account_controller->loadAccount();

            $this->account_controller->closeConnection();
        }
        public function render(){
            ?>
                <div class="header"></div>
                <div class="main-wrapper">
                    <?php 
                        $this->searchTab();
                        $this->accountsTab();
                    ?>
                </div>

                <?php
                    $this->modal();
                    $this->logicHolder();
                    $this->editModal();
                ?>

                <link href="./public/src/css/account.css" rel="stylesheet">
                <script src="./public/src/js/accounts.js"></script>
            <?php
        }

        public function searchTab(){
            ?>
                <div class="topbar">
                    <div class="search-wrapper">
                        <i class="bx bx-search"></i>
                        <input type="search" class="searchbar" placeholder="Search product or order">
                    </div>
                </div>
            <?php
        }

        public function accountsTab(){
            ?>
                <div class="account-container">
                    <div class="account-header">
                        <div>
                            <h3>Accounts</h3>
                        </div>
                        <div>
                            <button class="account-btn" onclick="showAccountModal()">Add Account</button>
                            <button class="filter-btn">Filter</button>
                        </div>
                    </div>

                    <div class="account-body">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Account ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Contact Number</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <?php 
                                    $list_of_accounts = $this->accounts;

                                    foreach($list_of_accounts as $account){
                                        $account_ID = $account["account_ID"];
                                        $role = $account["role"];
                                        $first_name = $account["first_name"];
                                        $last_name = $account["last_name"];
                                        $email = $account["email"];
                                        $contact_number = $account["contact_number"];
                                        $status = $account["status"];
                                        ?>
                                            <tr>
                                                <td><?php echo $account_ID?></td>
                                                <td><?php echo $first_name?></td>
                                                <td><?php echo $last_name?></td>
                                                <td><?php echo $email?></td>
                                                <td><?php echo $contact_number?></td>
                                                <td><?php echo $role?></td>
                                                <td><?php echo $status?></td>
                                                <td>
                                                    <button class="action-btn edit" title="Edit" onclick="editAccount(<?php printf('\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\'', $account_ID, $first_name, $last_name, $email, $contact_number, $role) ?>)">
                                                        <i class="bx bxs-edit"></i>
                                                    </button>
                                                    <button class="action-btn delete" title="Delete" 
                                                        onclick="deleteAccount(<?php echo $account['account_ID'] . ', \'' . $role . '\''; ?>)">
                                                        <i class="bx bx-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <!-- <tr class="no-products"><td colspan="7">No accounts yet.</td></tr> -->
                            </tbody>
                        </table>
                        <div class="table-nav">
                            <button>Previous</button>
                            <span>Page 1 of 1 <a href="#" class="view-all">See All</a></span>
                            <button>Next</button>
                        </div>
                    </div>
                </div>
            <?php
        }
        public function logicHolder(){
            ?>
                <form method="POST" id="delete_form">
                    <input id="selected_ID" type="hidden" name="delete_ID">
                </form>
            <?php
        }
        public function modal(){
            ?>
                <div id="addAccountModal" class="account-modal">
                    <div class="modal-content">
                        <h3 class="modal-title">New Account</h3>
                        <form method="POST" id="addProductForm">
                            <div class="form-row">
                                <label class="field-label">Account ID</label>
                                <input type="text" name="account_ID" placeholder="Enter account ID" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">First Name</label>
                                <input type="text" name="first_name" placeholder="Enter first name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Last Name</label>
                                <input type="text" name="last_name" placeholder="Enter last name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Email</label>
                                <input type="email" name="email" placeholder="Enter email" required="">
                            </div>
                            <div class="form-row">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="contact_number"
                                    maxlength="13"
                                    placeholder="09xx xxx xxxx"
                                    pattern="[0-9]{4}\s[0-9]{3}\s[0-9]{4}"
                                    required>
                            </div>
                            <div class="form-row">
                                <label class="field-label">Role</label>
                                <select name="role" required="">
                                    <option value="" disabled="" selected="">Select role</option>
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <small style="color: red; font-size: 10px;">Note: The default password is the user’s email address.</small>
                            <div class="modal-actions">
                                <button type="button" class="discard-btn" onclick="closeAccountModal()">Discard</button>
                                <input type="submit" class="add-product-btn" name="create_account" value="Create Account">
                            </div>
                        </form>
                    </div>
                </div>
            <?php
        }

        public function editModal(){
            ?>
                <div id="editAccountModal" class="account-modal">
                    <div class="modal-content">
                        <h3 class="modal-title">Edit Account</h3>
                        <form method="POST" id="addProductForm">
                            <input type="hidden" id="account_ID" name="account_ID">
                            <div class="form-row">
                                <label class="field-label">First Name</label>
                                <input id="first_name" type="text" name="first_name" placeholder="Enter first name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Last Name</label>
                                <input id="last_name" type="text" name="last_name" placeholder="Enter last name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Email</label>
                                <input id="email" type="email" name="email" placeholder="Enter email" required="">
                            </div>
                            <small style="color: red; font-size: 10px;">Note: Leave the password field empty if you do not want to change the current password.</small>
                            <div class="form-row">
                                <label class="field-label">New Password</label>
                                <input id="password" type="password" name="password" placeholder="*********">
                            </div>
                            <div class="form-row">
                                <label for="phone">Phone Number</label>
                                <input id="phone" type="tel" id="phone" name="contact_number"
                                    maxlength="13"
                                    placeholder="09xx xxx xxxx"
                                    pattern="[0-9]{4}\s[0-9]{3}\s[0-9]{4}"
                                    required>
                            </div>
                            <div class="form-row">
                                <label class="field-label">Role</label>
                                <select name="role" required="">
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="discard-btn" onclick="closeEditModal()">Discard</button>
                                <input type="submit" class="add-product-btn" name="update_account" value="Update Account">
                            </div>
                        </form>
                    </div>
                </div>
            <?php
        }
        public function handleAccountCreation(){
            if(isset($_POST["create_account"])){
                $account_ID = $_POST["account_ID"];
                $first_name = $_POST["first_name"];
                $last_name = $_POST["last_name"];
                $email = $_POST["email"];
                $contact_number = $_POST["contact_number"];
                $role = $_POST["role"];
                
                $this->account_controller->createAccount($account_ID,
                                                        $first_name, 
                                                        $last_name,
                                                        $email,
                                                        $email,
                                                        $contact_number,
                                                        $role);

                header("Location: index.php?view=accounts");
                exit();
            }
        }
        public function handleDeleteAccount(){
            if(isset($_POST["delete_ID"])){
                $account_ID = $_POST["delete_ID"];
                
                $this->account_controller->deleteAccount($account_ID);

                header("Location: index.php?view=accounts");
                exit();
            }
        }
        public function handleUpdateAccount(){
            if(isset($_POST["update_account"])){
                $account_ID = $_POST["account_ID"];
                $first_name = $_POST["first_name"];
                $last_name = $_POST["last_name"];
                $email = $_POST["email"];
                $contact_number = $_POST["contact_number"];
                $role = $_POST["role"];
                $password = $_POST["password"];

                $this->account_controller->updateAccount($account_ID,
                                                        $first_name,
                                                        $last_name,
                                                        $password, 
                                                        $email, 
                                                        $contact_number, 
                                                        $role);

                header("Location: index.php?view=accounts");
                exit();
            }
        }
    }
?>