<?php
$settings['display'] = 'horizontal';
$settings['fields'] = array(
	'event' => array(
		'caption' => 'Event',
		'type' => 'date',
		'width' => '150'
	),
	'location' => array(
		'caption' => 'Location',
		'type' => 'text',
		'width' => '180'
	),
	'price' => array(
		'caption' => 'Price',
		'type' => 'text',
		'width' => '90'
	)
);
$settings['templates'] = array(
	'outerTpl' => '<div class="events">[+wrapper+]</div>',
	'rowTpl' => '<div class="event">[+event+], [+location+] ([+price+])</div>'
);
?>
