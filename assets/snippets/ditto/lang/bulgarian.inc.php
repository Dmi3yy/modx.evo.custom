<?php
    $filename = dirname(__FILE__) . '/bulgarian-utf8.inc.php';
    $contents = file_get_contents($filename);
    $contents = mb_convert_encoding($contents, 'windows-1251', 'UTF-8');
    eval('?>' . $contents);
?>