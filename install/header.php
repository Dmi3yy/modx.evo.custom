<?php
$ph = get_ph_header();
$src = get_src_header();
echo parse($src,$ph);

function get_ph_header()
{
	global $_lang,$moduleName,$moduleVersion,$modx_textdir,$modx_release_date;
	
	$ph['language_code'] = $_lang['language_code'];
	$ph['encoding']      = $_lang['encoding'];
	$ph['pagetitle']     = $_lang['modx_install'];
	$ph['textdir']       = $modx_textdir ? ' id="rtl"':'';
	$ph['help_link']     = $_lang["help_link"];
	$ph['help_title']    = $_lang["help_title"];
	$ph['help']          = $_lang["help"];
	$ph['version']       = $moduleName.' '.$moduleVersion;
	$ph['release_date']  = ($modx_textdir ? '&rlm;':'') . $modx_release_date;
	return $ph;
}

function get_src_header()
{
	$src = <<< EOT
<!DOCTYPE html>
<html lang="[+language_code+]">
<head>
<meta charset="[+encoding+]">
<title>[+pagetitle+]</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="style.css" type="text/css" media="screen">
</head>
<body [+textdir+]>
<!-- start install screen-->
<div id="header">
	<div class="container_10">
		<span class="help"><a href="[+help_link+]" target="_blank" title="[+help_title+]">[+help+]</a></span>
		<span class="version">[+version+] ([+release_date+])</span>
		<div id="mainheader">
			<h1 id="logo"><span>MODX CMS</span></h1>
		</div>
	</div>
</div>
<!-- end header -->

<div id="contentarea">
    <div class="container_10">        
        <!-- start content -->
        <div id="content">
EOT;
	return $src;
}
