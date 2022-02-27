<!DOCTYPE html>
<html>
    <head>
	<link rel="stylesheet" href="./style.css">
    </head>
<body>
<?php
require_once('./BlogReader.php');

$blog = BlogReader::getInstance();
$blog->printContent();

?>
</body>
</html>
