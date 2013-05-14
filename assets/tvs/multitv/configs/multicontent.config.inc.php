<?php
$settings['display'] = 'datatable';
$settings['fields'] = array(
	'title1_1' => array(
		'caption' => 'Title',
		'type' => 'text'
	),
	'content1_1' => array(
		'caption' => 'Content',
		'type' => 'richtext'
	),
	'title2_1' => array(
		'caption' => 'Title (First Column)',
		'type' => 'text'
	),
	'content2_1' => array(
		'caption' => 'Content (First Column)',
		'type' => 'richtext'
	),
	'title2_2' => array(
		'caption' => 'Title (Second Column)',
		'type' => 'text'
	),
	'content2_2' => array(
		'caption' => 'Content (Second Column)',
		'type' => 'richtext'
	)
);
$settings['columns'] = array(
	array(
		'caption' => 'Content',
		'fieldname' => 'title1_1',
		'render' => '[+fieldTab:switch=`onecol:<table><tr><td>[+title1_1:outer=`<b>|</b> – `+][+content1_1:striphtml:character_limit=`75`:outer=`| …`+]</td></tr></table>|twocol:<table><tr><td style="width: 50%">[+title2_1:outer=`<b>|</b> – `+][+content2_1:striphtml:character_limit=`75`:outer=`| …`+]</td><td style="width: 50%">[+title2_2:outer=`<b>|</b> – `+][+content2_2:striphtml:character_limit=`75`:outer=`| …`+]</td></tr></table>|default:`+]'
	)
);
$settings['form'] = array(
	array(
		'caption' => 'One Column',
		'value' => 'onecol',
		'content' => array(
			'title1_1' => array(
			),
			'content1_1' => array(
			)
		)
	),
	array(
		'caption' => 'Two Columns',
		'value' => 'twocol',
		'content' => array(
			'title2_1' => array(
			),
			'content2_1' => array(
			),
			'title2_2' => array(
			),
			'content2_2' => array(
			)
		)
	)
);

$settings['templates'] = array(
	'outerTpl' => '

[+wrapper+]
',
	'rowTpl' => '
[+img_bg+]
[+row.number+]
[+iteration+]
[+title+]
[+row.class+]
<br/>'
);
$settings['configuration'] = array(
	'enablePaste' => FALSE,
	'csvseparator' => ',',
	'radioTabs' => TRUE,
	'hideHeader' => TRUE
);
$settings['templates'] = array(
	'outerTpl' => '<table>[+wrapper+]</table>',
	'rowTpl' => '[+fieldTab:switch=`onecol:<tr><td><h1>[+title1_1+]</h1>[+content1_1+]</td></tr>|twocol:<tr><td><h1>[+title2_1+]</h1>[+content2_1+]</td><td><h1>[+title2_2+]</h1>[+content2_2+]</td></tr>|default:`+]'
		)
?>