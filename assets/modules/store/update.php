<?php
$file = fopen ("http://modx-store.com/get.php?get=file&cid=1", "rb");
$newf = fopen ('update.zip', "wb");
while(!feof($file)) fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
$zip = new ZipArchive;
$res = $zip->open(dirname(__FILE__).'/update.zip');
$zip->extractTo( dirname(__FILE__) );
$zip->close();
echo dirname(__FILE__).'/update.zip';
unlink('update.zip');
?>