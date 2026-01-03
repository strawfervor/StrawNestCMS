<?php
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

$secrets = require 'secrets.php';
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate current password
    if (!password_verify($current_password, $secrets['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Generate new hash
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Update secrets.php
        $secrets_content = "<?php\nreturn [\n    'password_hash' => '$new_hash'\n];\n";

        if (file_put_contents(__DIR__ . '/secrets.php', $secrets_content)) {
            // Destroy session and redirect to login
            session_unset();
            session_destroy();
            header("Location: index.php?msg=password_changed");
            exit;
        } else {
            $error = "Failed to save new password. Check file permissions.";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Change Password - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h1>Change Password</h1>

    <?php if ($error): ?>
        <p style="color: #d9534f;"><strong>
                <?php echo htmlspecialchars($error); ?>
            </strong></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color: #5cb85c;"><strong>
                <?php echo htmlspecialchars($success); ?>
            </strong></p>
    <?php endif; ?>

    <form method="post" style="max-width: 400px;">
        <div style="margin-bottom: 1em;">
            <label style="display: block; margin-bottom: 0.5em;">Current Password:</label>
            <input type="password" name="current_password" required
                style="width: 100%; padding: 0.5em; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 1em;">
            <label style="display: block; margin-bottom: 0.5em;">New Password:</label>
            <input type="password" name="new_password" required minlength="8"
                style="width: 100%; padding: 0.5em; box-sizing: border-box;">
            <small>Minimum 8 characters</small>
        </div>

        <div style="margin-bottom: 1em;">
            <label style="display: block; margin-bottom: 0.5em;">Confirm New Password:</label>
            <input type="password" name="confirm_password" required
                style="width: 100%; padding: 0.5em; box-sizing: border-box;">
        </div>

        <div style="margin-top: 1em; display: flex; gap: 0.5em;">
            <button type="submit">Change Password</button>
            <a href="index.php"><button type="button">Back</button></a>
        </div>
    </form>
</body>

</html>