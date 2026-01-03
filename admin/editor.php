<?php
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

$file = isset($_GET['file']) ? $_GET['file'] : '';
$content = '';
$error = '';
$success = '';
$is_new = empty($file);

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'];
    $content = $_POST['content'];

    // Simple validation
    if (empty($filename)) {
        $error = "Filename is required.";
    } else {
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $filepath = "../pages/" . $filename . ".txt";

        // If new, checks if exists
        if ($is_new && file_exists($filepath)) {
            $error = "File already exists. Choose another name.";
        } else {
            // Write file
            file_put_contents($filepath, $content);
            $success = "Page saved successfully.";
            // If it was new, redirect to edit mode to avoid creating another one on refresh validation issues
            if ($is_new) {
                header("Location: editor.php?file=" . $filename . "&msg=saved");
                exit;
            }
        }
    }
}

// Load content if editing
if (!$is_new && empty($content)) {
    // Sanitize input file param
    $safe_file = basename($file);

    // Check if extension is already present
    if (pathinfo($safe_file, PATHINFO_EXTENSION) === 'txt') {
        $filepath = "../pages/" . $safe_file;
        $filename = pathinfo($safe_file, PATHINFO_FILENAME);
    } else {
        $filepath = "../pages/" . $safe_file . ".txt";
        $filename = $safe_file;
    }

    if (file_exists($filepath)) {
        $content = file_get_contents($filepath);
    } else {
        $error = "File not found.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'saved') {
    $success = "Page saved successfully.";
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>
        <?php echo $is_new ? 'New Page' : 'Edit Page'; ?> - Admin
    </title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h1>
        <?php echo $is_new ? 'Create New Page' : 'Edit Page'; ?>
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert" style="background-color: #dff0d8; color: #3c763d;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div style="margin-bottom: 1em;">
            <label style="display: block; margin-bottom: 0.5em;">Filename (without .txt):</label>
            <input type="text" name="filename"
                value="<?php echo htmlspecialchars(isset($filename) ? $filename : ''); ?>" <?php echo $is_new ? '' : 'readonly'; ?> required style="width: 100%; padding: 0.5em; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 1em;">
            <label style="display: block; margin-bottom: 0.5em;">Content:</label>
            <textarea name="content" rows="20"
                style="font-family: monospace; width: 100%; padding: 0.5em; box-sizing: border-box;"><?php echo htmlspecialchars($content); ?></textarea>
        </div>

        <div style="margin-top: 1em; display: flex; gap: 0.5em;">
            <button type="submit">Save Page</button>
            <a href="index.php"><button type="button">Cancel</button></a>
        </div>
    </form>

    <details style="margin-top: 2em;">
        <summary>Cheatsheet</summary>
        <p><code>=title: Your Title</code> - Sets the page title.</p>
        <p><code>=image:filename.jpg</code> - Full width image.</p>
        <p><code>=imageSmall:filename.jpg</code> - Small image.</p>
        <p>Images path: <code>images/</code></p>
    </details>
</body>

</html>