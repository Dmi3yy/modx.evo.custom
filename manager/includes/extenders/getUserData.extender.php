<?php

	if(IN_MANAGER_MODE!=true && IN_PARSER_MODE!="true") die("<b>INCLUDE ACCESS ERROR</b><br /><br />Direct access to this file prohibited.");

		require_once MODX_BASE_PATH . 'manager/includes/sniff/phpSniff.class.php';
		if(!isset($_GET['UA'])) $_GET['UA'] = '';
		if(!isset($_GET['cc'])) $_GET['cc'] = '';
		if(!isset($_GET['dl'])) $_GET['dl'] = '';
		if(!isset($_GET['am'])) $_GET['am'] = '';

		$sniffer_settings = array(	'check_cookies'=>$_GET['cc'],
									'default_language'=>$_GET['dl'],
									'allow_masquerading'=>$_GET['am']);

		$client = new phpSniff($_GET['UA'],$sniffer_settings);
		$client->get_property('UA');
		$tmpArray = array();
		$tmpArray['ip'] = $client->property('ip');
		$tmpArray['ua'] = $client->property('ua');
		$tmpArray['browser'] = $client->property('browser');
		$tmpArray['long_name'] = $client->property('long_name');
		$tmpArray['version'] = $client->property('version');
		$tmpArray['maj_ver'] = $client->property('maj_ver');
		$tmpArray['min_ver'] = $client->property('min_ver');
		$tmpArray['letter_ver'] = $client->property('letter_ver');
		$tmpArray['javascript'] = $client->property('javascript');
		$tmpArray['platform'] = $client->property('platform');
		$tmpArray['os'] = $client->property('os');
		$tmpArray['language'] = $client->property('language');
		$tmpArray['gecko'] = $client->property('gecko');
		$tmpArray['gecko_ver'] = $client->property('gecko_ver');
		$tmpArray['html'] = $client->has_feature('html')==1 ? "true" : "false" ;
		$tmpArray['images'] = $client->has_feature('images')==1 ? "true" : "false" ;
		$tmpArray['frames'] = $client->has_feature('frames')==1 ? "true" : "false" ;
		$tmpArray['tables'] = $client->has_feature('tables')==1 ? "true" : "false" ;
		$tmpArray['java'] = $client->has_feature('java')==1 ? "true" : "false" ;
		$tmpArray['plugins'] = $client->has_feature('plugins')==1 ? "true" : "false" ;
		$tmpArray['css2'] = $client->has_feature('css2')==1 ? "true" : "false" ;
		$tmpArray['css1'] = $client->has_feature('css1')==1 ? "true" : "false" ;
		$tmpArray['iframes'] = $client->has_feature('iframes')==1 ? "true" : "false" ;
		$tmpArray['xml'] = $client->has_feature('xml')==1 ? "true" : "false" ;
		$tmpArray['dom'] = $client->has_feature('dom')==1 ? "true" : "false" ;
		$tmpArray['hdml'] = $client->has_feature('hdml')==1 ? "true" : "false" ;
		$tmpArray['wml'] = $client->has_feature('wml')==1 ? "true" : "false" ;
		$tmpArray['must_cache_forms'] = $client->has_quirk('must_cache_forms')==1 ? "true" : "false" ;
		$tmpArray['avoid_popup_windows'] = $client->has_quirk('avoid_popup_windows')==1 ? "true" : "false" ;
		$tmpArray['cache_ssl_downloads'] = $client->has_quirk('cache_ssl_downloads')==1 ? "true" : "false" ;
		$tmpArray['break_disposition_header'] = $client->has_quirk('break_disposition_header')==1 ? "true" : "false" ;
		$tmpArray['empty_file_input_value'] = $client->has_quirk('empty_file_input_value')==1 ? "true" : "false" ;
		$tmpArray['scrollbar_in_way'] = $client->has_quirk('scrollbar_in_way')==1 ? "true" : "false" ;
