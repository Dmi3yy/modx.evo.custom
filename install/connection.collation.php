<?php

$host = $_POST['host'];
$uid = $_POST['uid'];
$pwd = $_POST['pwd'];
$database_collation = htmlentities($_POST['database_collation']);

$output = '<input type="hidden" id="database_collation" name="database_collation" value="' . $database_collation . '" />';
echo $output;
?>