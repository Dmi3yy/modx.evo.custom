<?php
//markItUp! for MODx evo Plugin v1.1.6.1

function get_markitup_init($params, $mode='')
{
	$markitup_init = file_get_contents($params['markitup_path'] . '/view_markitup_init.tpl');
	
	$textarea = array();
	if ($mode=='id')
	{
		foreach($params['elements'] as $value)
		{
			$textarea[] = '#' . $value;
		}
		$ph['textarea'] = join(',', $textarea);
	}
	else
	{
		$ph['textarea'] = 'textarea';
	}
	$ph['markitup_url'] = $params['markitup_url'];
	$ph['modx_browser_url'] = MODX_BASE_URL .'manager/media/browser/mcpuk/';
	
	foreach($ph as $name => $value)
	{
		$name = '[+' . $name . '+]';
		$markitup_init = str_replace($name, $value, $markitup_init);
	}
	return $markitup_init;
}
