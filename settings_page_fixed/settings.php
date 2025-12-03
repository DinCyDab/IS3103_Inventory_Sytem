<?php
session_start();

if(!isset($_SESSION['user_id'])){
    $_SESSION['user_id'] = 1;
}

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

require 'update_settings.php';
$user = get_user($_SESSION['user_id']);

if (!$user) {
    die("Error: User ID " . $_SESSION['user_id'] . " not found in the database 'account' table.");
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <title>Settings</title>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="profile-short">
                <img src="uploads/<?php echo htmlspecialchars($user['avatar']?:'default.jpg'); ?>" class="sidebar-pfp">
                <span class="name"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></span>
            </div>
            <nav>
                <a href="#">Dashboard</a>
                <a href="#">Inventory</a>
                <a href="#">Sales</a>
                <a href="#">Reports</a>
                <a class="active" href="#">Settings</a>
                <a class="logout" href="logout.php">Log Out</a>
            </nav>
        </aside>
        <main class="main">
            <div class="header-gradient">
                <div class="pfp-wrapper">
                    <img id="pfp" src="uploads/<?php echo htmlspecialchars($user['avatar']?:'default.jpg'); ?>">
                    <label for="pfpInput" class="edit-pfp"></label>
                    <form id="pfpForm" method="post" enctype="multipart/form-data" action="settings.php">
                        <input id="pfpInput" name="avatar" type="file" accept="image/*">
                        <input type="hidden" name="action" value="upload_avatar">
                    </form>
                </div>
                <h1>Settings</h1>
            </div>
            <form class="settings-form" method="post" action="settings.php" enctype="multipart/form-data">
                <div class="field-row">
                    <div class="field">
                        <label>First name</label>
                        <input name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <div class="field">
                        <label>Last name</label>
                        <input name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label>Email</label>
                        <div class="input-icon">
                            <span>@</span>
                            <input name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label>Role</label>
                        <input name="role" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
                        <a class="manage" href="#">Manage User Access</a>
                    </div>
                </div>
                <div class="theme-field">
                    <label>Theme</label>
                    <label class="switch">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider"></span>
                    </label>
                </div>
                <input type="hidden" name="action" value="update_profile">
                <button class="save">Save Changes</button>
            </form>
        </main>
    </div>
    <script src="script.js"></script>
</body>
</html>