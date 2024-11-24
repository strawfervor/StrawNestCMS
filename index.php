<?php
//setting varibles for config file, title, headings
    session_start();
    $config = fopen("config.php", "r");
    $title = "";
    $heading1 = "";
    $heading2 = "";
    $preview_len = 300; //entry item preview length
    $items = 5; //number of items to be displayed on main page
    //$_SESSION['page']; current page on main page
    //$_SESSION['num_of_pages']; total number of pages with items

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
        } elseif (match_config_line($config_line, "preview_len:")) {
            global $preview_len;
            $temp = intval(substr($config_line, 12));
            if ($temp >= 0 && $temp !== $preview_len) {
                $preview_len = $temp;
            }
        } elseif (match_config_line($config_line, "items:")) {
            global $items;
            $temp = intval(substr($config_line, 6));
            if ($temp >= 1 && $temp !== $items && $temp <= 1000) {
                $items = $temp;
            }
        }
    }

//setting $_SESSION varibles:
    if (empty($_SESSION['page'])) {
        $_SESSION['page'] = 1;
    }
    if (empty($_SESSION['num_of_pages'])) {
        $_SESSION['num_of_pages'] = ceil(number_of_files() / $items);
    }

//function for searching for parameter in config file
    function match_config_line($config_line, $config_parameter) {
        if (strpos($config_line, $config_parameter) !== false || strpos($config_line, $config_parameter) === 0) {
            return true;
        }
        return false;
    }

//function counting number of files in pages directory, only .txt files!
    function number_of_files() {
        $count = 0;
        $dir = "pages";
        $files = glob($dir . "/*.txt");
        foreach ($files as $file) {
            $count++;
        }
        return $count;
    }

//function is creating array with pages filenames and ordering them from newset to oldest by creation time
    function create_pages_array() {
        $_SESSION['pages_array'] = glob("pages/*.txt");
        usort($_SESSION['pages_array'], function($a, $b) {
            if (filectime($b) < filemtime($b)) {
                return filectime($b) - filectime($a);
            }
            return filemtime($b) - filemtime($a);
        }
    ); 
    }

    function main_contents_preview($preview_len, $items) {
        $first_index = ($_SESSION['page'] - 1) * $items;
        echo "
            <p>Page number {$_SESSION['page']} / {$_SESSION['num_of_pages']}</p>
            <p>First index: {$first_index}</p>
        ";
        if ($first_index <= count($_SESSION['pages_array'])) {
            for ($i = $first_index; $i < $first_index + 5; $i++) {
                if (!empty($_SESSION['pages_array'][$i])) {
                    entry_preview($preview_len, $i);
                }
            }
        }

    }

    //function is generating single entry preview for main page
    function entry_preview($preview_len, $entry_index) {
        $page_file = fopen($_SESSION['pages_array'][$entry_index], "r");
        $preview_string = "";
        $preview_body = "";
        $letters_count = 0;
        while(!feof($page_file)) {
            $page_line = fgets($page_file);
            if (match_config_line($page_line, "=title:")) {
                $preview_string .= "<h3>" . substr($page_line, 7) . "</h3>";
            } elseif ($letters_count <= $preview_len) {
                $preview_body .= $page_line;
                $letters_count += strlen($page_line) ;
            }
        }
        $preview_string .= substr($preview_body, 0, $preview_len);
        echo $preview_string;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    //setting title
        echo "<title>{$title}</title>";
    ?>
</head>
<body>
    <h1><?php echo $heading1; ?></h1>

    <h2><?php echo $heading2; ?></h2>

    <debug>
        <p>Number of files: <?php echo number_of_files(); ?></p>
        <p>Number of pages: <?php echo $_SESSION['num_of_pages']; ?></p>
        <p>Current page: <?php echo $_SESSION['page']; ?></p>
        <p>Newest page: <?php create_pages_array();
        echo $_SESSION['pages_array'][0]; ?></p>
    </debug>

    <main>
        <?php
            main_contents_preview($preview_len, $items);
        ?>
    </main>
</body>
</html>