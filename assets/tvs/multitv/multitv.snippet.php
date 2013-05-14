<?php
/**
 * multiTV
 * 
 * @category 	snippet
 * @version 	1.5.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author		Jako (thomas.jakobi@partout.info)
 *
 * @internal    description: <strong>1.5.1</strong> Transform template variables into a sortable multi item list.
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

// load template variable settings
$tvName = isset($tvName) ? $tvName : '';
$res = $modx->db->select('*', $modx->getFullTableName('site_tmplvars'), 'name="' . $tvName . '"');
$tvSettings = $modx->db->getRow($res);
if (!$tvSettings) {
	return 'Template variable ' . $tvName . ' does not exists';
}

// pre-init template configuration
$tvSettings['tpl_config'] = (isset($tplConfig)) ? $tplConfig : '';

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
$offset = isset($offset) ? intval($offset) : 0;
$rows = (isset($rows) && ($rows != 'all')) ? explode(',', $rows) : 'all';
$toPlaceholder = (isset($toPlaceholder) && $toPlaceholder != '') ? $toPlaceholder : FALSE;
$randomize = (isset($randomize) && $randomize) ? TRUE : FALSE;
$orderBy = isset($orderBy) ? $orderBy : '';
list($sortBy, $sortDir) = explode(" ", $orderBy);
$published = (isset($published)) ? $published : '1';
$outputSeparator = (isset($outputSeparator)) ? $outputSeparator : '';

// replace masked placeholder tags (for templates that are set directly set in snippet call by @CODE)
$maskedTags = array('((' => '[+', '))' => '+]');
$outerTpl = str_replace(array_keys($maskedTags), array_values($maskedTags), $outerTpl);
$rowTpl = str_replace(array_keys($maskedTags), array_values($maskedTags), $rowTpl);

// get template variable
switch (strtolower($published)) {
	case '0':
	case 'false':
		$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '0');
		break;
	case '1':
	case '2':
	case 'true':
		$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '1');
		if ($tvOutput == FALSE && $published == '2') {
			$tvOutput = $modx->getTemplateVarOutput(array($tvName), $docid, '0');
		}
		break;
}
$tvOutput = $tvOutput[$tvName];
$tvOutput = json_decode($tvOutput, TRUE);
if ($tvOutput['fieldValue']) {
	$tvOutput = $tvOutput['fieldValue'];
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
		$parser = new evoChunkie($outerTpl);
		$parser->AddVar('wrapper', '');
		$output = $parser->Render();
		if ($toPlaceholder) {
			$modx->setPlaceholder($tvName, $output);
		}
		return $output;
	}
}

// random or sort output
if ($randomize) {
	shuffle($tvOutput);
} elseif (!empty($sortBy)) {
	$multiTV->sort($tvOutput, trim($sortBy), trim($sortDir));
}

// check for display all regarding selected rows count and offset
$countOutput = ($rows === 'all') ? $countOutput : count($rows);
$display = ($display !== 'all') ? intval($display) : $countOutput;
$display = (($display + $offset) < $countOutput) ? $display : $countOutput - $offset;

// output
$wrapper = array();
$i = $iteration = 1;
$class = 'first';
// rowTpl output 
foreach ($tvOutput as $value) {
	if ($display == 0) {
		break;
	}
	if ($rows !== 'all' && !in_array($i, $rows)) {
		// output only selected rows 
		$i++;
		continue;
	}
	if ($offset) {
		// don't show the offset rows
		$offset--;
		$i++;
		continue;
	}
	$class = ($display != 1) ? $class : trim($class . ' last');
	$parser = new evoChunkie($rowTpl);
	foreach ($value as $key => $fieldvalue) {
		$fieldname = (is_int($key)) ? $columns[$key] : $key;
		$parser->AddVar($fieldname, $fieldvalue);
	}
	$parser->AddVar('iteration', $iteration);
	$parser->AddVar('row', array('number' => $i, 'class' => $class, 'total' => $countOutput));
	$parser->AddVar('docid', $docid);
	$placeholder = $parser->Render();
	if ($toPlaceholder) {
		$modx->setPlaceholder($toPlaceholder . '.' . $i, $placeholder);
	}
	$wrapper[] = $placeholder;
	$i++;
	$iteration++;
	$display--;
	$class = '';
}
if ($emptyOutput && !count($wrapper)) {
	// output nothing
	$output = '';
} else {
	// wrap rowTpl output in outerTpl
	$parser = new evoChunkie($outerTpl);
	$parser->AddVar('wrapper', implode($outputSeparator, $wrapper));
	$parser->AddVar('rows', array('offset' => $offset, 'total' => $countOutput));
	$parser->AddVar('docid', $docid);
	$output = $parser->Render();
}

if ($toPlaceholder) {
	$modx->setPlaceholder($toPlaceholder, $output);
	return '';
} else {
	return $output;
}
?>
