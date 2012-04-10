<?php
if(isset($_GET['q']) && $_GET['q']!=='')       $q = $_GET['q'];
elseif(isset($_POST['q']) && $_POST['q']!=='') $q = $_POST['q'];
else exit;

$q = realpath($q) or die(); 

define('MODX_API_MODE', true);
include_once('index.php');

if(strtolower(substr($q,-4))=='.php') include_once($q);
