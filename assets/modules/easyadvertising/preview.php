<?php

foreach ($_POST as $k=>$v) 
	$row[$k] = htmlspecialchars_decode($v);
$row['id'] = 'zzz';
	
$modUrl = 'http://'.$_SERVER['HTTP_HOST'].'/assets/modules/easyadvertising/';
$out = '';

$lnk = '<a target="_blank" href="'.
	($row['jump_counted'] == 1 ? 'http://'.$_SERVER['HTTP_HOST'].'/click.php?id='.$row['id'] : $row['link']).'">';
	
if (!is_file('../../../'.$row['content'])) {
	
	if (strpos($row['content'], '[+link_start+]'))
		$banner = str_replace(array('[+link_start+]','[+link_finish+]') , array($lnk,'</a>'), $row['content']);	
	else 
		$banner = $lnk.$row['content'].'</a>';	

} else {
	$ext = pathinfo($row['content'], PATHINFO_EXTENSION);
	if ($ext == 'swf') {
		
		$alt = '<a href="http://www.adobe.com/go/getflashplayer">
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" 
				alt="Get Adobe Flash player" /></a>';
		
		if ($row['jump_counted'] == 1 || !empty($row['link'])) { 
			$banner = '<div id="eadvt_wrapper'.$row['id'].'">'.$lnk.'</a><div id="eadvt'.$row['id'].'">'.$alt.'</div></div>';
		} else 
			$banner = '<div id="eadvt'.$row['id'].'">'.$alt.'</div>';
		
		$fl = getimagesize('../../../'.$row['content']);
		$js .= "swfobject.embedSWF('/".$row['content']."', 'eadvt".$row['id']."', '".$fl[0]."', '".$fl[1]."', '6.0.65', '".$modUrl."flash/expressInstall.swf', {}, {wmode:'opaque',quality:'high'});\n";
		$css .= "#eadvt_wrapper".$row['id']." {width:".$fl[0]."px; height:".$fl[1]."px} \n";
		$out = 1;
	} else 
		$banner = $lnk.'<img src="/'.$row['content'].'" alt="banner '.$row['id'].'" /></a>';	
}

if ($out == 1) {
	$out  = $banner;
	$out .= '<link type="text/css" rel="stylesheet" href="'.$modUrl.'css/eadvt.css" />';
	$out .= '<style type="text/css">'.$css.'</style>';
	$out .= '<script type="text/javascript" src="'.$modUrl.'flash/swfobject.js"></script>';
	$out .= '<script type="text/javascript">'.$js.'</script>';	
} else 
	$out = $banner;
	
echo $out;