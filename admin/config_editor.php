<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

$config_file = "../config.php";
$content = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    file_put_contents($config_file, $content);
    $message = "Configuration saved.";
} else {
    if (file_exists($config_file)) {
        $content = file_get_contents($config_file);
    } else {
        $content = "Config file not found!";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Configuration - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h1>Edit Configuration</h1>

    <?php if ($message): ?>
        <div class="alert" style="background-color: #dff0d8; color: #3c763d;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div style="margin-bottom: 1em;">
            <textarea name="content" rows="15"
                style="font-family: monospace; width: 100%; box-sizing: border-box; padding: 0.5em;"><?php echo htmlspecialchars($content); ?></textarea>
        </div>

        <div style="margin-top: 1em; display: flex; gap: 0.5em;">
            <button type="submit">Save Config</button>
            <a href="index.php"><button type="button">Back to Dashboard</button></a>
        </div>
    </form>
</body>

</html>