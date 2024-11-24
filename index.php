<?php
//setting varibles for config file, title, headings
    $config = fopen("config.php", "r");
    $title = "";
    $heading1 = "";
    $heading2 = "";
    $preview_len = 300;
    $items = 5;

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

    function match_config_line($config_line, $config_parameter) {
        if (strpos($config_line, $config_parameter) !== false || strpos($config_line, $config_parameter) === 0) {
            return true;
        }
        return false;
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
</body>
</html>