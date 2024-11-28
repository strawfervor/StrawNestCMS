<?php
//setting varibles for config file, title, headings
    session_start();
    $config = fopen("config.php", "r");
    $title = "";
    $heading1 = "";
    $heading2 = "";
    $footer = "";
    $timeout = 600;//session timeout time
    $_SESSION['last_activity'] = time(); //set 'last_activity' time to now


//checking for activity timeout to destroy session()
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // new array + destroy session
        $_SESSION = array();
        session_destroy();
    }

//loop for reading varibles from config file
    while (!feof($config)) {
        $config_line = fgets($config);
        if (match_config_line($config_line, "title:")) {
            global $title; 
            $title = substr($config_line, 6);
        } elseif (match_config_line($config_line, "heading1:")) {
            global $heading1; 
            $heading1 = substr($config_line, 9);
        } elseif (match_config_line($config_line, "heading2:")) {
            global $heading2; 
            $heading2 = substr($config_line, 9);
        } elseif (match_config_line($config_line, "footer:")) {
            global $footer; 
            $footer = substr($config_line, 7);
        }
    }

//function for searching for parameter in config file
    function match_config_line($config_line, $config_parameter) {
        if (strpos($config_line, $config_parameter) !== false || strpos($config_line, $config_parameter) === 0) {
            return true;
        }
        return false;
    }

//function is generating single entry preview for main page; need to add limit of displaying number of numbers
    function entry_preview($filename) {
        $page_file = fopen($filename, "r");
        $page_body = "";
        while(!feof($page_file)) {
            $page_line = fgets($page_file);
            if (match_config_line($page_line, "=title:")) {
                $page_body .= "<h3>" . substr($page_line, 7) . "</h3><p>";
            } elseif ($page_line === '' || empty($page_line) || trim($page_line) === '') {
                $page_body .= "</p><p>";
            } else {
                $page_body .= $page_line;
            }
        }
        $page_body .= "</p><p><i>Last edited: " . date("d-m-Y H:i:s", filemtime($filename)) ."</i></p>";
        echo $page_body;
    }

    function entry_path() {
        $entry_from_get = $_GET['entry'];
        $entry_path = "pages/" . $entry_from_get . ".txt";
        return $entry_path;
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <?php
    //setting title
        echo "<title>{$title}</title>";
    ?>
</head>
<body>

    <h1 style="margin-top: 1.5em;"><?php echo "<a href='index.php'>" . $heading1 . "</a>"; ?></h1>

    <h2 style="margin-top: -0.5em;"><?php echo $heading2; ?></h2>

    <main style="margin-top: 6em;">
        <?php entry_preview(entry_path()); ?>
    </main>

    <nav style="margin-top: 1.5em;">

    </nav>

    <footer style="margin-top: 10em;">
        <?php
            echo $footer;
        ?>
    </footer>

</body>
</html>