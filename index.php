<?php
//setting varibles for config file, title, headings
    $config = fopen("config.php", "r");
    $title = "";
    $heading1 = "";
    $heading2 = "";
//loop for reading varibles from config file
    while (!feof($config)) {
        $config_line = fgets($config);
        if (strpos($config_line, "title:") !== false || strpos($config_line, "title") === 0) {
            global $title; 
            $title = substr($config_line, 6);
        } elseif (strpos($config_line, "heading1:") !== false || strpos($config_line, "heading1") === 0) {
            global $heading1; 
            $heading1 = substr($config_line, 9);
        } elseif (strpos($config_line, "heading2:") !== false || strpos($config_line, "heading2") === 0) {
            global $heading2; 
            $heading2 = substr($config_line, 9);
        }
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