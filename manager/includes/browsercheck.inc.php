<?php 
require_once(dirname(__FILE__).'/protect.inc.php');
require_once(MODX_BASE_PATH.'manager/includes/sniff/phpSniff.class.php');

if(!isset($_GET['UA'])) $_GET['UA'] = ''; 
if(!isset($_GET['cc'])) $_GET['cc'] = ''; 
if(!isset($_GET['dl'])) $_GET['dl'] = ''; 
if(!isset($_GET['am'])) $_GET['am'] = '';

$sniffer_settings = array(	'check_cookies'=>$_GET['cc'],
							'default_language'=>$_GET['dl'],
							'allow_masquerading'=>$_GET['am']); 

$client = new phpSniff($_GET['UA'],$sniffer_settings);

$client->get_property('UA');

if(isset($_GET['showbrowser']) && $_GET['showbrowser']==1) {
?>
Browser: <?php print $client->property('browser'); ?> <br />
longname: <?php print $client->property('long_name');?> <br />
version: <?php print $client->property('version');?> <br />
maj_ver: <?php print $client->property('maj_ver');?> <br />
min_ver: <?php print $client->property('min_ver');?> <br />
letter_ver: <?php print $client->property('letter_ver');?> <br />
javascript: <?php print $client->property('javascript');?> <br />
platform: <?php print $client->property('platform');?> <br />
os: <?php print $client->property('os');?> <br />
<?php
}

$browserok = false;
if($client->property('platform')=='win' && $client->property('browser')=='ie' && $client->property('version')>='5.5') {
	$browserok = true;
}
if($client->property('platform')=='win' && $client->property('browser')=='fb' && $client->property('version')>='0.6.1') {
	$browserok = true;
}
if($client->property('platform')=='win' && $client->property('browser')=='mz' && $client->property('version')>='1.4') {
	$browserok = true;
}

if((isset($_GET['browserok']) && $_GET['browserok']==1) || (isset($_SESSION['browserok']) && $_SESSION['browserok']==1)) {
	$browserok = 1;
	$_SESSION['browserok']=1;
}
