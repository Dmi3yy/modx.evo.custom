<?php
$settings['display'] = 'vertical';
$settings['fields'] = array(
	'image' => array(
		'caption' => 'Image',
		'type' => 'image'
	),
	'thumb' => array(
		'caption' => 'Thumbnail',
		'type' => 'thumb',
		'thumbof' => 'image'
	),
	'title' => array(
		'caption' => 'Title',
		'type' => 'text'
	),
	'legend' => array(
		'caption' => 'Legend',
		'type' => 'text'
	),
	'author' => array(
		'caption' => 'Author',
		'type' => 'text'
	)
);
$settings['templates'] = array(
	'outerTpl' => '<div class="images">[+wrapper+]</div>',
	'rowTpl' => '<div class="image"><div class="copyrightwrapper"><img src="[+image+]" alt="[+legend+]" title="[+title+]" />[+author:ne=``:then=`<p class="copyright">[+author+]</p>`+]</div>[+legend:ne=``:then=`<p class="legend">[+legend:nl2br+]</p>`+]</div>'
		)
?>
