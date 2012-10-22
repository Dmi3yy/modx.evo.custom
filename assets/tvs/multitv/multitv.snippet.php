<?php
/**
 * multiTV
 * 
 * @category 	snippet
 * @version 	1.4.7
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author		Jako (thomas.jakobi@partout.info)
 *
 * @internal    description: <strong>1.4.7</strong> Transform template variables into a sortable multi item list.
 * @internal    snippet code: return include(MODX_BASE_PATH.'assets/tvs/multitv/multitv.snippet.php');
 */
if (MODX_BASE_PATH == '') {
	die('<h1>ERROR:</h1><p>Please use do not access this file directly.</p>');
}

global $modx;

// set customtv (base) path
define(MTV_PATH, 'assets/tvs/multitv/');
define(MTV_BASE_PATH, MODX_BASE_PATH . MTV_PATH);

// include classfile
if (!class_exists('multiTV')) {
	include MTV_BASE_PATH . 'multitv.class.php';
}
// include chunke class
if (!class_exists('multitvChunkie')) {
	include (MTV_BASE_PATH . '/includes/chunkie.class.inc.php');
}

// load template variable settings
$tvName = isset($tvName) ? $tvName : '';
$res = $modx->db->select('*', $modx->getFullTableName('site_tmplvars'), 'name="' . $tvName . '"');
$tvSettings = $modx->db->getRow($res);
if (!$tvSettings) {
	return 'Template variable ' . $tvName . ' does not exists';
}

// init multiTV class
$multiTV = new multiTV($tvSettings);
$columns = $multiTV->fieldnames;
$templates = $multiTV->templates;

// get snippet parameter
$docid = isset($docid) ? $docid : $modx->documentObject['id'];
$outerTpl = isset($outerTpl) ? $outerTpl : (isset($templates['outerTpl']) ? '@CODE:' . $templates['outerTpl'] : '@CODE:<select name="' . $tvName . '">[+wrapper+]</select>');
$emptyOutput = (isset($emptyOutput) && !$emptyOutput) ? FALSE : TRUE;
$rowTpl = isset($rowTpl) ? $rowTpl : (isset($templates['rowTpl']) ? '@CODE:' . $templates['rowTpl'] : '@CODE:<option value="[+value+]">[+key+]</option>');
$display = isset($display) ? $display : 5;
$rows = (isset($rows) && ($rows != 'all')) ? explode(',', $rows) : 'all';
$toPlaceholder = (isset($toPlaceholder) && $toPlaceholder) ? TRUE : FALSE;
$randomize = (isset($randomize) && $randomize) ? TRUE : FALSE;
$published = (isset($published)) ? $published : '1';

// replace masked placeholder tags (for templates that are set directly set in snippet call by @CODE)
$maskedTags = array('((' => '[+', '))' => '+]');
$outerTpl = str_replace(array_keys($maskedTags), array_values($maskedTags), $outerTpl);
$rowTpl = str_replace(array_keys($maskedTags), array_values($maskedTags), $rowTpl);

// get template variable
switch (strtolower($published)) {
	case '0' :
	case 'false' :
		$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '0');
		break;
	case '1' :
	case '2':
	case 'true':
		$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '1');
		if ($tvOutput == false && $published == '2') {
			$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '0');
		}
		break;
}
$tvOutput = $tvOutput[$tvName];
$tvOutput = json_decode($tvOutput);
if (is_object($tvOutput)) {
	$tvOutput = $tvOutput->fieldValue;
}
$countOutput = count($tvOutput);

$firstEmpty = TRUE;
if ($countOutput) {
	// check for first item empty
	foreach ($tvOutput[0] as $value) {
		if ($value != '') {
			$firstEmpty = FALSE;
		}
	}
}
// stop if there is no output
if (!$countOutput || $firstEmpty) {
	if ($emptyOutput) {
		// output nothing
		return '';
	} else {
		// output empty outer template
		$parser = new multitvChunkie($outerTpl);
		$parser->AddVar('wrapper', '');
		$output = $parser->Render();
		if ($toPlaceholder) {
			$modx->setPlaceholder($tvName, $output);
		}
		return $output;
	}
}

// random output
if ($randomize) {
	shuffle($tvOutput);
}

// check for display all
$display = ($display != 'all') ? intval($display) : $countOutput;

// output
$columnCount = count($columns);
$wrapper = '';
$i = 1;
$placeholder = array();
// rowTpl output 
foreach ($tvOutput as $value) {
	if ($display == 0) {
		break;
	}
	if ($rows != 'all') {
		// output only selected rows 
		if (!in_array($i, $rows)) {
			continue;
		}
	}
	$parser = new multitvChunkie($rowTpl);
	for ($j = 0; $j < $columnCount; $j++) {
		$parser->AddVar($columns[$j], $value[$j]);
	}
	$parser->AddVar('iteration', $i);
	$parser->AddVar('docid', $docid);
	$placeholder[$i] = $parser->Render();
	if ($toPlaceholder) {
		$modx->setPlaceholder($tvName . '.' . $i, $placeholder[$i]);
	}
	$wrapper .= $placeholder[$i];
	$i++;
	$display--;
}
// wrap rowTpl output in outerTpl
$parser = new multitvChunkie($outerTpl);
$parser->AddVar('wrapper', $wrapper);
$parser->AddVar('docid', $docid);
$output = $parser->Render();

if ($toPlaceholder) {
	$modx->setPlaceholder($tvName, $output);
	return '';
} else {
	return $output;
}
?>
