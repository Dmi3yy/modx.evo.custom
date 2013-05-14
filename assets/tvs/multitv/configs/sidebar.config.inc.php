<?php
$settings['display'] = 'vertical';
$settings['fields'] = array(
	'element' => array(
		'caption' => 'Element',
		'type' => 'dropdown',
		'elements' => '@EVAL return $modx->runSnippet("Quill", array("parent"=>"0", "title"=>"pagetitle", "mode"=>"list", "default"=>" ", "depth"=>"5", "showPublishedOnly"=>"1"));'
	)
);
$settings['templates'] = array(
	'outerTpl' => '[+wrapper:trim=`,`+]',
	'rowTpl' => '[+element+],'
);
$settings['configuration'] = array(
	'enablePaste' => FALSE,
	'enableClear' => TRUE
);
?>
