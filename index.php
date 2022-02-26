<!DOCTYPE html>
<html>
<head></head>
<body>
<?php
require_once('./BlogReader.php');

$blog = BlogReader::getInstance();
$blog->printContent();

?>
</body>
</html>
