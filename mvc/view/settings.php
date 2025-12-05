<?php 
    require_once __DIR__ . "/../controller/settingscontroller.php";
class SettingsView {
    private $user;
    private $list_of_msg = [
        1 => 'Update Successful',
        2 => 'New Password and Confirm Password did not match',
        3 => 'Incorrect Password'
    ];
    public function __construct() {
        // STATIC user data for now
        $this->user = $_SESSION["account"];

        if(isset($_POST["edit_info"])){
            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            
            $settings_controller = new SettingsController();

            $settings_controller->handleUpdateInfo($this->user["account_ID"],$first_name, $last_name);
        }

        if(isset($_POST["update_password"])){
            $current_password = $_POST["current_password"];
            $new_password = $_POST["new_password"];
            $confirm_password = $_POST["confirm_password"];

            $settings_controller = new SettingsController();

            $settings_controller->handlePassword($this->user["account_ID"], $current_password, $new_password, $confirm_password);
        }
    }

    public function showUpdateModal(){
        ?>
            <div id="addAccountModal" class="account-modal">
                <div class="modal-content">
                    <h3 class="modal-title"><?php echo $this->list_of_msg[$_GET["msg"]] ?></h3>
                    <button onclick="closeModal()">Close</button>
                </div>
            </div>
        <?php
    }

    public function render() {
        ?>
    
        <div class="layout">
            <!-- Main -->
            <div class="main">
                <?php
                    if(isset($_GET["msg"])){
                        $this->showUpdateModal();
                    }
                ?>

                <div class="header-gradient">
                    <div class="pfp-wrapper">
                        <img id="pfp">
                        <label for="pfpInput" class="edit-pfp"></label>
                        <input id="pfpInput" type="file" accept="image/*">
                    </div>

                    <h1><?php echo $this->user['first_name'] . ' ' . $this->user['last_name'] ?></h1>
                </div>

                <!-- STATIC FORM — no backend -->
                 
                <form method="POST" class="settings-form">
                    <h3>Edit Info</h3>
                    <div class="field-row">
                        <div class="field">
                            <label>First name</label>
                            <input name="first_name" value="<?php echo $this->user['first_name']; ?>">
                        </div>

                        <div class="field">
                            <label>Last name</label>
                            <input name="last_name" value="<?php echo $this->user['last_name']; ?>">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label>Email</label>
                            <input value="<?php echo $this->user['email']; ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Role</label>
                            <input value="<?php echo $this->user['role']; ?>" readonly>
                            <!-- <a class="manage" href="#">Manage User Access</a> -->
                        </div>
                    </div>

                    <div class="theme-field">
                        <label>Theme</label>
                        <label class="switch">
                            <input type="checkbox" id="themeToggle">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <input type="hidden" name="edit_info">
                    <button class="save">Save Changes</button>
                </form>

                <form method="POST" class="settings-form">
                    <h3>Update Password</h3>

                    <div class="field-row">
                        <div class="field">
                            <label>Current Password</label>
                            <input type="password" name="current_password">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label>New Password</label>
                            <input type="password" name="new_password">
                        </div>

                        <div class="field">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password">
                        </div>
                    </div>

                    <input type="hidden" name="update_password"> 
                    <button class="save">Update Password</button>
                </form>

            </div>
        </div>

        <link rel="stylesheet" href="./public/src/css/settings.css">
        <script src="./public/src/js/settings.js"></script>

    <?php }
}
?>
