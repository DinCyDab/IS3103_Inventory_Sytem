<?php 
    require_once __DIR__ . "/../controller/accountcontroller.php";
    
    class AccountsView{
        private $account_controller;
        private $accounts = [];
        private $upper_limit;
        private $lower_limit;
        private $next_disabled;
        private $current_user_role;

        public function __construct(){
            $this->account_controller = new AccountController();
            $this->current_user_role = $_SESSION["account"]["role"] ?? 'staff';

            $this->handleAccountCreation();
            $this->handleDeleteAccount();
            $this->handleUpdateAccount();

            // Start here for filter
            $conditions = [];

            if (!empty($_GET["role"])) {
                $roles = array_map('trim', $_GET["role"]);
                $rolePlaceholders = implode("','", array_map('addslashes', $roles));
                $conditions[] = "role IN ('$rolePlaceholders')";
            }

            if (!empty($_GET["status"])) {
                $statuses = array_map('trim', $_GET["status"]);
                $statusPlaceholders = implode("','", array_map('addslashes', $statuses));
                $conditions[] = "status IN ('$statusPlaceholders')";
            }

            // Search functionality
            if (!empty($_GET["search"])) {
                $search = addslashes(trim($_GET["search"]));
                $conditions[] = "(account_ID LIKE '%$search%' 
                                OR first_name LIKE '%$search%' 
                                OR last_name LIKE '%$search%' 
                                OR email LIKE '%$search%' 
                                OR contact_number LIKE '%$search%')";
            }

            $this->lower_limit = $_GET["lower_limit"] ?? 0;
            $this->upper_limit = $_GET["upper_limit"] ?? 7;

            $filter = "";

            if (!empty($conditions)) {
                $filter = "WHERE " . implode(" AND ", $conditions);
            }

            $filter .= " LIMIT $this->lower_limit, $this->upper_limit";

            $this->accounts = $this->account_controller->loadAccount($filter);

            // Determine if Next button should be disabled
            $this->next_disabled = count($this->accounts) < 7;

            // End here for filtering
            $this->account_controller->closeConnection();
        }

        // Check if current user can modify target account
        private function canModify($target_role){
            if($this->current_user_role === 'super_admin'){
                return $target_role !== 'super_admin';
            }

            if($this->current_user_role === 'admin'){
                return $target_role === 'staff';
            }

            return false;
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
                    $this->filterModal();
                ?>

                <link href="./public/src/css/account.css" rel="stylesheet">
                <script src="./public/src/js/accounts.js"></script>
                <script>
                    // Pass current user role to Javascript
                    const currentUserRole = '<?php echo $this->current_user_role; ?>';
                </script>
            <?php
        }

        public function searchTab(){
            ?>
                <form method="GET" class="search-wrapper">
                    <input type="hidden" name="view" value="accounts">

                    <!-- Preserve filters when searching -->
                    <?php 
                        if (!empty($_GET["role"])) {
                            foreach ($_GET["role"] as $r) {
                                echo '<input type="hidden" name="role[]" value="'.htmlspecialchars($r).'">';
                            }
                        }
                        if (!empty($_GET["status"])) {
                            foreach ($_GET["status"] as $s) {
                                echo '<input type="hidden" name="status[]" value="'.htmlspecialchars($s).'">';
                            }
                        }
                    ?>

                    <div class="topbar">
                        <div class="search-wrapper">
                            <i class="bx bx-search"></i>
                            <input 
                                type="search"
                                name="search"
                                class="searchbaraccounts"
                                placeholder="Search by ID, name, email or number"
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                            >
                        </div>
                    </div>
                </form>
            <?php
        }

        public function accountsTab(){
            ?>
                <div class="accounts-container">
                    <div class="accounts-header">
                        <div>
                            <h3>Accounts</h3>
                        </div>
                        <div class="button-accounts">
                            <button class="accounts-btn" onclick="showAccountModal()">Add Account</button>
                            <button class="filter-btn" onclick="openFilterModal()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(149, 145, 145, 1);transform:msFilter;"><path d="M7 11h10v2H7zM4 7h16v2H4zm6 8h4v2h-4z"></path></svg>Filter</button>
                        </div>
                    </div>

                    <table class="accounts-table">
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
                        <tbody id="accountsTableBody">
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
                                            <td><?php echo htmlspecialchars($account_ID); ?></td>
                                            <td><?php echo htmlspecialchars($first_name); ?></td>
                                            <td><?php echo htmlspecialchars($last_name); ?></td>
                                            <td><?php echo htmlspecialchars($email); ?></td>
                                            <td><?php echo htmlspecialchars($contact_number); ?></td>
                                            <td><?php echo htmlspecialchars($role); ?></td>
                                            <td><?php echo htmlspecialchars($status); ?></td>
                                            
                                            <td>
                                                <?php if ($this->canModify($role)) : ?>
                                                    <!-- EDIT BUTTON - Fixed to pass status parameter -->
                                                    <button class="action-btn edit" title="Edit"
                                                        onclick="editAccount('<?php echo addslashes($account_ID); ?>', '<?php echo addslashes($first_name); ?>', '<?php echo addslashes($last_name); ?>', '<?php echo addslashes($email); ?>', '<?php echo addslashes($contact_number); ?>', '<?php echo addslashes($role); ?>', '<?php echo addslashes($status); ?>')">
                                                        <i class="bx bxs-edit"></i>
                                                    </button>

                                                    <!-- DELETE BUTTON -->
                                                    <button class="action-btn delete" title="Delete"
                                                        onclick="deleteAccount('<?php echo addslashes($account_ID); ?>', '<?php echo addslashes($role); ?>')">
                                                        <i class="bx bx-trash-alt"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color:#888; font-size:12px;">No Access</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    $next_lower = $this->lower_limit + 7;
                    $next_upper = $this->upper_limit + 7;

                    $prev_lower = max($this->lower_limit - 7, 0);
                    $prev_upper = max($this->upper_limit - 7, 7);

                    // Build query array for Next
                    $query = [
                        "view=accounts",
                        "lower_limit=$next_lower",
                        "upper_limit=$next_upper"
                    ];

                    // Preserve role filters
                    if (!empty($_GET["role"])) {
                        foreach ($_GET["role"] as $r) {
                            $query[] = "role[]=" . urlencode($r);
                        }
                    }

                    // Preserve status filters
                    if (!empty($_GET["status"])) {
                        foreach ($_GET["status"] as $s) {
                            $query[] = "status[]=" . urlencode($s);
                        }
                    }

                    // Preserve search
                    if (!empty($_GET["search"])) {
                        $query[] = "search=" . urlencode($_GET["search"]);
                    }

                    $next_url = "?" . implode("&", $query);

                    // Previous URL
                    $query[1] = "lower_limit=$prev_lower";
                    $query[2] = "upper_limit=$prev_upper";
                    $prev_url = "?" . implode("&", $query);
                    ?>
                    <div class="table-nav">
                        <a href="<?=$prev_url?>">
                            <button <?= $this->lower_limit == 0 ? 'disabled' : '' ?>>Previous</button>
                        </a>

                        <a href="<?=$next_url?>">
                            <button <?= $this->next_disabled ? 'disabled' : '' ?>>Next</button>
                        </a>
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
                        <form method="POST" id="addProductsForm">
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
                                    
                                    <?php 
                                        // Only super_admin can create admin accounts
                                        if($this->current_user_role == 'super_admin'){
                                            ?>
                                                <option value="admin">Admin</option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>
                            <small style="color: red; font-size: 10px;">Note: The default password is the user's email address.</small>
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
                        <form method="POST" id="editProductsForm">
                            <input type="hidden" id="edit_account_ID" name="account_ID">
                            <div class="form-row">
                                <label class="field-label">First Name</label>
                                <input id="edit_first_name" type="text" name="first_name" placeholder="Enter first name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Last Name</label>
                                <input id="edit_last_name" type="text" name="last_name" placeholder="Enter last name" required="">
                            </div>
                            <div class="form-row">
                                <label class="field-label">Email</label>
                                <input id="edit_email" type="email" name="email" placeholder="Enter email" required="">
                            </div>
                            <small style="color: red; font-size: 10px;">Note: Leave the password field empty if you do not want to change the current password.</small>
                            <div class="form-row">
                                <label class="field-label">New Password</label>
                                <input id="edit_password" type="password" name="password" placeholder="*********">
                            </div>
                            <div class="form-row">
                                <label for="phone">Phone Number</label>
                                <input id="edit_phone" type="tel" name="contact_number"
                                    maxlength="13"
                                    placeholder="09xx xxx xxxx"
                                    pattern="[0-9]{4}\s[0-9]{3}\s[0-9]{4}"
                                    required>
                            </div>
                            <div class="form-row">
                                <label class="field-label">Role</label>
                                <select id="edit_role" name="role" required="">
                                    <option value="staff">Staff</option>
                                    <?php 
                                        // Only super_admin can set admin role
                                        if($this->current_user_role == 'super_admin'){
                                            ?>
                                                <option value="admin">Admin</option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <label class="field-label">Status</label>
                                <select id="edit_status" name="status" required="">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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

        public function filterModal(){
            ?>
                <div id="filterModal" class="modal">
                    <div class="modal-content" style="position:relative;">
                        <button type="button" class="close-modal modal-x" onclick="closeFilterModal()" aria-label="Close" style="position: absolute; top: 22px; right: 22px; background: none; border: none;">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none">
                            <path d="M18 6L6 18" stroke="#999" stroke-width="2" stroke-linecap="round"></path>
                            <path d="M6 6L18 18" stroke="#999" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                        </button>
                        <h3 style="margin-top: 8px;">Select Filter</h3>
                        <div class="filter-design">
                            <form method="GET">
                                <input type="hidden" name="view" value="accounts">

                                <h3>Role</h3>

                                <!-- Check All (Role) -->
                                <label style="padding-left: 10px; display: flex; gap: 10px;">
                                    <input type="checkbox" id="checkAllRole">
                                    <p>Select All</p>
                                </label>

                                <hr>

                                <label>
                                    <input type="checkbox" name="role[]" value="super_admin" class="role-checkbox">
                                    Super Admin
                                </label>

                                <label>
                                    <input type="checkbox" name="role[]" value="admin" class="role-checkbox">
                                    Admin
                                </label>

                                <label>
                                    <input type="checkbox" name="role[]" value="staff" class="role-checkbox">
                                    Staff
                                </label>

                                <h3>Status</h3>

                                <!-- Check All (Status) -->
                                <label style="padding-left: 10px; display: flex; gap: 10px;">
                                    <input type="checkbox" id="checkAllStatus">
                                    <p>Select All</p>
                                </label>

                                <hr>

                                <label>
                                    <input type="checkbox" name="status[]" value="active" class="status-checkbox">
                                    Active
                                </label>

                                <label>
                                    <input type="checkbox" name="status[]" value="inactive" class="status-checkbox">
                                    Inactive
                                </label>

                                <button type="submit">Apply Filter</button>
                                <a href="index.php?view=accounts"><button type="button" style="background: orange;">Clear Filter</button></a>
                            </form>
                        </div>
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
                
                // Verify permissions in backend
                $current_role = $_SESSION["account"]["role"];
                
                // Super admin cannot create other super admins
                if($role === 'super_admin'){
                    $_SESSION['error'] = "Cannot create super admin accounts";
                    header("Location: index.php?view=accounts");
                    exit();
                }
                
                // Admin can only create staff
                if($current_role === 'admin' && $role !== 'staff'){
                    $_SESSION['error'] = "Admins can only create staff accounts";
                    header("Location: index.php?view=accounts");
                    exit();
                }
                
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
                $status = $_POST["status"];

                // Verify permissions in backend
                $current_role = $_SESSION["account"]["role"];
                
                // Admin cannot change role to admin or super_admin
                if($current_role === 'admin' && in_array($role, ['admin', 'super_admin'])){
                    $_SESSION['error'] = "Admins cannot set admin or super_admin roles";
                    header("Location: index.php?view=accounts");
                    exit();
                }

                $this->account_controller->updateAccount($account_ID,
                                                        $first_name,
                                                        $last_name,
                                                        $password, 
                                                        $email, 
                                                        $contact_number, 
                                                        $role,
                                                        $status);

                header("Location: index.php?view=accounts");
                exit();
            }
        }
    }
?>