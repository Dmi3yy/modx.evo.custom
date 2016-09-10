<?php

$host = $_POST['host'];
$uid = $_POST['uid'];
$pwd = $_POST['pwd'];
$database_collation = htmlentities($_POST['database_collation']);

$output = '<select id="database_collation" name="database_collation">
<option value="'.$database_collation.'" selected >'.$database_collation.'</option></select>';

if ($conn = mysqli_connect($host, $uid, $pwd)) {
    // get collation
    $getCol = mysqli_query($conn, "SHOW COLLATION");
    if (mysqli_num_rows($getCol) > 0) {
        $output = '<select id="database_collation" name="database_collation">';
        while ($row = mysqli_fetch_row($getCol)) {
            $collation = htmlentities($row[0]);
            $selected = ( $collation==$database_collation ? ' selected' : '' );
            $output .= '<option value="'.$collation.'"'.$selected.'>'.$collation.'</option>';
        }
        $output .= '</select>';
    }
}
echo $output;
?>