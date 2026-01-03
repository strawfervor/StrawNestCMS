<?php
session_start();
$secrets = require 'secrets.php';

$error = '';
$success = '';

// Rate limiting settings
$max_attempts = 5;
$lockout_time = 30 * 60; // 30 minutes in seconds
$lockout_dir = __DIR__ . '/login_attempts';

// Create lockout directory if not exists
if (!is_dir($lockout_dir)) {
    mkdir($lockout_dir, 0700, true);
}

// Get client IP (with proxy support)
function get_client_ip()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    return preg_replace('/[^a-zA-Z0-9\.\:]/', '_', trim($ip));
}

// Get login attempts data for IP
function get_attempts_data($dir, $ip)
{
    $file = $dir . '/' . $ip . '.json';
    if (!file_exists($file)) {
        return ['attempts' => 0, 'locked_until' => 0];
    }
    $data = json_decode(file_get_contents($file), true);
    return $data ?: ['attempts' => 0, 'locked_until' => 0];
}

// Save login attempts data for IP
function save_attempts_data($dir, $ip, $data)
{
    $file = $dir . '/' . $ip . '.json';
    file_put_contents($file, json_encode($data));
}

// Reset attempts for IP
function reset_attempts($dir, $ip)
{
    $file = $dir . '/' . $ip . '.json';
    if (file_exists($file)) {
        unlink($file);
    }
}

$client_ip = get_client_ip();
$attempts_data = get_attempts_data($lockout_dir, $client_ip);
$is_locked = $attempts_data['locked_until'] > time();

// Handle password changed message
if (isset($_GET['msg']) && $_GET['msg'] === 'password_changed') {
    $success = "Password changed successfully. Please login with your new password.";
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($is_locked) {
        $error = "Too many failed attempts. Please try again later.";
    } elseif (password_verify($_POST['password'], $secrets['password_hash'])) {
        // Reset attempts on successful login
        reset_attempts($lockout_dir, $client_ip);
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        // Increment failed attempts
        $attempts_data['attempts']++;

        if ($attempts_data['attempts'] >= $max_attempts) {
            $attempts_data['locked_until'] = time() + $lockout_time;
            $error = "Too many failed attempts. Please try again later.";
            $is_locked = true;
        } else {
            $error = "Invalid password.";
        }

        save_attempts_data($lockout_dir, $client_ip, $attempts_data);
    }
}

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Admin Login - StrawNestCMS</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <div class="login-container"
            style="max-width: 400px; margin: 5em auto; padding: 2em; border: 1px solid #ccc; border-radius: 8px;">
            <h2>Admin Login</h2>
            <?php if ($success): ?>
                <p style="color: #5cb85c;"><strong><?php echo htmlspecialchars($success); ?></strong></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p style="color: #d9534f;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
            <?php endif; ?>
            <?php if ($is_locked): ?>
                <p>Please try again later.</p>
            <?php else: ?>
                <form method="post">
                    <div style="margin-bottom: 1em;">
                        <input type="password" name="password" placeholder="Password" required
                            style="width: 100%; padding: 0.5em; box-sizing: border-box;">
                    </div>
                    <button type="submit">Login</button>
                </form>
            <?php endif; ?>
            <p><a href="../index.php">&larr; Back to Site</a></p>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// Helper to get pages
function get_pages()
{
    $files = glob("../pages/*.txt");
    usort($files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    return $files;
}

// Handle Page Deletion (Simple implementation here, or move to separate file)
if (isset($_POST['delete_page'])) {
    $file_to_delete = '../pages/' . basename($_POST['delete_page']);
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        header("Location: index.php?msg=deleted");
        exit;
    }
}

$pages = get_pages();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard - StrawNestCMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h1>Dashboard</h1>

    <section style="margin-bottom: 2em;">
        <h3>Actions</h3>
        <nav style="display: flex; gap: 0.5em; flex-wrap: wrap;">
            <a href="editor.php"><button type="button">Add New Page</button></a>
            <a href="config_editor.php"><button type="button">Edit Configuration</button></a>
            <a href="change_password.php"><button type="button">Change Password</button></a>
            <a href="logout.php"><button type="button">Logout</button></a>
            <a href="../index.php" target="_blank"><button type="button">View Site</button></a>
        </nav>
    </section>

    <section>
        <h3>Pages</h3>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: left;">Title</th>
                    <th style="text-align: left;">File</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <?php
                    $filename = basename($page);
                    $name = pathinfo($filename, PATHINFO_FILENAME);
                    $handle = fopen($page, 'r');
                    $title = $name;
                    if ($handle) {
                        $line = fgets($handle);
                        if (strpos($line, '=title:') === 0) {
                            $title = substr(trim($line), 7);
                        }
                        fclose($handle);
                    }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($title); ?></strong></td>
                        <td><small><?php echo htmlspecialchars($filename); ?></small></td>
                        <td style="text-align: right;">
                            <a href="editor.php?file=<?php echo urlencode($filename); ?>"><button
                                    type="button">Edit</button></a>
                            <form method="POST" style="display: inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this page?');">
                                <input type="hidden" name="delete_page" value="<?php echo htmlspecialchars($filename); ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</body>

</html>