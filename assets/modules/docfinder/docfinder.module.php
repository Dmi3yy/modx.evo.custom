<?php
// ---------------------------------------------------------------
// :: Doc Finder
// ----------------------------------------------------------------
//
// 	Short Description:
//         Ajax powered search and replace for the manager.
//
//   Version:
//         1.7
//
//   Created by:
// 	    Bogdan Günther (http://www.medianotions.de - bg@medianotions.de)
//
//
// ----------------------------------------------------------------
// :: Copyright & Licencing
// ----------------------------------------------------------------
//
//   GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)
//
//

if (IN_MANAGER_MODE != 'true') {
    die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}

global $modx;

// set customtv (base) path
define('DF_PATH', str_replace(MODX_BASE_PATH, '', str_replace('\\', '/', realpath(dirname(__FILE__)))) . '/');
define('DF_BASE_PATH', MODX_BASE_PATH . DF_PATH);

if (!class_exists('newChunkie')) {
    include(DF_BASE_PATH . 'includes/newchunkie.class.php');
}

$_lang = array();
include(DF_BASE_PATH . 'lang/english.php');
if (file_exists(DF_BASE_PATH . 'lang/' . $modx->config['manager_language'] . '.php')) {
    include(DF_BASE_PATH . 'lang/' . $modx->config['manager_language'] . '.php');
}

//error_reporting(E_ALL);

// set PHP version
$phpversion = intval(substr(phpversion(), 0, 1));

// set timezoneZeitzone festlegen
if ($phpversion > 4) date_default_timezone_set("Europe/Berlin");

$options = array();
$options['moduleversion'] = '1.7';
$options['theme'] = $modx->config['manager_theme'];
$options['modx_charset'] = $modx->config['modx_charset'];
$options['dir'] = ($modx->config['manager_direction'] == 'rtl' ? 'rtl' : 'ltr');
$options['module_id'] = (int)$_GET['id'];
$options['module_url'] = DF_PATH;


// set Theme
$theme = '/MODxCarbon';

// load text direction as seen in Doc manager
if (isset($modx->config['manager_direction'])) $dir = ($modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '');
else $dir = "";

// load lang as seen in Doc Manager
$lang = $modx->config['manager_language'];

// define search places
$searchPlacesArray = array(
    'DocAndTVV' => array(
        'name' => '[+lang.documents+]',
        'id' => 'DocAndTVV',
        'edit' => 27,
        'info' => 3
    ),
    'Templates' => array(
        'name' => '[+lang.templates+]',
        'id' => 'Templates',
        'edit' => 16
    ),
    'TVs' => array(
        'name' => '[+lang.tvs+]',
        'id' => 'TVs',
        'edit' => 301
    ),
    'Chunks' => array(
        'name' => '[+lang.chunks+]',
        'id' => 'Chunks',
        'edit' => 78
    ),
    'Snippets' => array(
        'name' => '[+lang.snippets+]',
        'id' => 'Snippets',
        'edit' => 22
    ),
    'Plugins' => array(
        'name' => '[+lang.plugins+]',
        'id' => 'Plugins',
        'edit' => 102
    ),
    'Modules' => array(
        'name' => '[+lang.modules+]',
        'id' => 'Modules',
        'edit' => 108
    )
);

// load PHP functions
include(DF_BASE_PATH . 'includes/functions.php');

// get and set post and session vars: form controls
if (isset($_POST['checkform'])) $checkform = $_POST['checkform']; else $checkform = "load";
if (isset($_POST['update_session'])) $update_session = $_POST['update_session']; else $update_session = 0;

// get and set post and session vars: search places
if (isset($_POST['search_place_selector']) or !empty($_SESSION['docfinder_search_place_selector']) and !$update_session) $searchOptions['search_place_selector'] = 'checked="checked"'; else $searchOptions['search_place_selector'] = "";
foreach ($searchPlacesArray as $searchPlace) {
    if (isset($_POST['search_place_' . $searchPlace['id']]) or !empty($_SESSION['docfinder_search_place_' . $searchPlace['id']]) and !$update_session) {
        $searchOptions['search_place_' . $searchPlace['id']] = 'checked="checked"';
    } else {
        $searchOptions['search_place_' . $searchPlace['id']] = "";
    }
}
// get and set post and session vars: search options
if (isset($_POST['case_sensitive']) or !empty($_SESSION['docfinder_case_sensitive']) and !$update_session) $searchOptions['case_sensitive'] = 'checked="checked"'; else $searchOptions['case_sensitive'] = "";
if (isset($_POST['regular_expression']) or !empty($_SESSION['docfinder_regular_expression']) and !$update_session) $searchOptions['regular_expression'] = 'checked="checked"'; else $searchOptions['regular_expression'] = "";
if ($searchOptions['regular_expression']) $searchOptions['regular_expression_status'] = "true"; else $searchOptions['regular_expression_status'] = "false";
if (isset($_POST['sortable_tables']) or !empty($_SESSION['docfinder_sortable_tables']) and !$update_session) $searchOptions['sortable_tables'] = 'checked="checked"'; else $searchOptions['sortable_tables'] = "";

// get and set post and session vars: search fields documents
if (isset($_POST['documents_search_in_selector']) or !empty($_SESSION['docfinder_documents_search_in_selector']) and !$update_session) $searchOptions['documents_search_in_selector'] = 'checked="checked"'; else $searchOptions['documents_search_in_selector'] = "";
if (isset($_POST['df_id']) or !empty($_SESSION['docfinder_id']) and !$update_session) $searchOptions['id'] = 'checked="checked"'; else $searchOptions['id'] = "";
if (isset($_POST['pagetitle']) or !empty($_SESSION['docfinder_pagetitle']) and !$update_session) $searchOptions['pagetitle'] = 'checked="checked"'; else $searchOptions['pagetitle'] = "";
if (isset($_POST['longtitle']) or !empty($_SESSION['docfinder_longtitle']) and !$update_session) $searchOptions['longtitle'] = 'checked="checked"'; else $searchOptions['longtitle'] = "";
if (isset($_POST['description']) or !empty($_SESSION['docfinder_description']) and !$update_session) $searchOptions['description'] = 'checked="checked"'; else $searchOptions['description'] = "";
if (isset($_POST['alias']) or !empty($_SESSION['docfinder_alias']) and !$update_session) $searchOptions['alias'] = 'checked="checked"'; else $searchOptions['alias'] = "";
if (isset($_POST['introtext']) or !empty($_SESSION['docfinder_introtext']) and !$update_session) $searchOptions['introtext'] = 'checked="checked"'; else $searchOptions['introtext'] = "";
if (isset($_POST['menutitle']) or !empty($_SESSION['docfinder_menutitle']) and !$update_session) $searchOptions['menutitle'] = 'checked="checked"'; else $searchOptions['menutitle'] = "";
if (isset($_POST['content']) or !empty($_SESSION['docfinder_content']) and !$update_session) $searchOptions['content'] = 'checked="checked"'; else $searchOptions['content'] = "";
if (isset($_POST['tvs']) or !empty($_SESSION['docfinder_tvs']) and !$update_session) $searchOptions['tvs'] = 'checked="checked"'; else $searchOptions['tvs'] = "";

// get and set post and session vars: search fields resources
if (isset($_POST['resources_search_in_selector']) or !empty($_SESSION['docfinder_resources_search_in_selector']) and !$update_session) $searchOptions['resources_search_in_selector'] = 'checked="checked"'; else $searchOptions['resources_search_in_selector'] = "";
if (isset($_POST['resources_id']) or !empty($_SESSION['docfinder_resources_id']) and !$update_session) $searchOptions['resources_id'] = 'checked="checked"'; else $searchOptions['resources_id'] = "";
if (isset($_POST['resources_name']) or !empty($_SESSION['docfinder_resources_name']) and !$update_session) $searchOptions['resources_name'] = 'checked="checked"'; else $searchOptions['resources_name'] = "";
if (isset($_POST['resources_description']) or !empty($_SESSION['docfinder_resources_description']) and !$update_session) $searchOptions['resources_description'] = 'checked="checked"'; else $searchOptions['resources_description'] = "";
if (isset($_POST['resources_other']) or !empty($_SESSION['docfinder_resources_other']) and !$update_session) $searchOptions['resources_other'] = 'checked="checked"'; else $searchOptions['resources_other'] = "";

// get and set post and session vars: date ranges
if (isset($_POST['createdon_start'])) $searchOptions['createdon_start'] = $_POST['createdon_start']; else if (!empty($_SESSION['docfinder_createdon_start']) and !$update_session) $searchOptions['createdon_start'] = $_SESSION['docfinder_createdon_start']; else $searchOptions['createdon_start'] = "";
if (isset($_POST['createdon_end'])) $searchOptions['createdon_end'] = $_POST['createdon_end']; else if (!empty($_SESSION['docfinder_createdon_end']) and !$update_session) $searchOptions['createdon_end'] = $_SESSION['docfinder_createdon_end']; else $searchOptions['createdon_end'] = "";
if (isset($_POST['editedon_start'])) $searchOptions['editedon_start'] = $_POST['editedon_start']; else if (!empty($_SESSION['docfinder_editedon_start']) and !$update_session) $searchOptions['editedon_start'] = $_SESSION['docfinder_editedon_start']; else $searchOptions['editedon_start'] = "";
if (isset($_POST['editedon_end'])) $searchOptions['editedon_end'] = $_POST['editedon_end']; else if (!empty($_SESSION['docfinder_editedon_end']) and !$update_session) $searchOptions['editedon_end'] = $_SESSION['docfinder_editedon_end']; else $searchOptions['editedon_end'] = "";

// set UNIX time for date ranges
if ($searchOptions['createdon_start']) $searchOptions['createdon_start_time'] = strtotime($searchOptions['createdon_start']);
else $searchOptions['createdon_start_time'] = strtotime("01.01.1970");
if ($searchOptions['createdon_end']) $searchOptions['createdon_end_time'] = strtotime($searchOptions['createdon_end']);
else $searchOptions['createdon_end_time'] = strtotime("now");
if ($searchOptions['editedon_start']) $searchOptions['editedon_start_time'] = strtotime($searchOptions['editedon_start']);
else $searchOptions['editedon_start_time'] = strtotime("01.01.1970");
if ($searchOptions['editedon_end']) $searchOptions['editedon_end_time'] = strtotime($searchOptions['editedon_end']);
else $searchOptions['editedon_end_time'] = strtotime("now");

// get and set post and session vars: entries to show
if (isset($_POST['entries']) and $_POST['entries'] == "50" or !empty($_SESSION['docfinder_entries_50']) and !$update_session) $searchOptions['entries_50'] = 'checked="checked"'; else $searchOptions['entries_50'] = "";
if (isset($_POST['entries']) and $_POST['entries'] == "100" or !empty($_SESSION['docfinder_entries_100']) and !$update_session) $searchOptions['entries_100'] = 'checked="checked"'; else $searchOptions['entries_100'] = "";
if (isset($_POST['entries']) and $_POST['entries'] == "All" or !empty($_SESSION['docfinder_entries_All']) and !$update_session) $searchOptions['entries_All'] = 'checked="checked"'; else $searchOptions['entries_All'] = "";

// get and set post and session vars: search string
if (isset($_POST['searchstring'])) $search['string'] = $_POST['searchstring'];
else if (!empty($_SESSION['docfinder_string']) and !$update_session) $search['string'] = $_SESSION['docfinder_string'];
else $search['string'] = "";

// get and set post and session vars: replace string and replace mode
if (isset($_POST['replacestring'])) $searchOptions['replace'] = $_POST['replacestring'];
else $searchOptions['replace'] = "";
if (isset($_POST['replace_mode']) and $_POST['replace_mode'] == "1") $searchOptions['replace_mode'] = true;
else $searchOptions['replace_mode'] = false;

// get and set post and session vars: parents
if (isset($_POST['parents'])) $search['parents'] = $_POST['parents']; else if (!empty($_SESSION['docfinder_parents']) and !$update_session) $search['parents'] = $_SESSION['docfinder_parents']; else $search['parents'] = "";

// set parents array
$parentsArray = explode(",", $search['parents']);
foreach ($parentsArray as $parent) {
    $parent = trim($parent);
    if ($parent == "") continue;
    $search['parentsArray'][$parent] = $parent;
}

// set default values
if (!isset($_SESSION['docfinder_string'])) {
    // Search places
    $searchOptions['search_place_selector'] = 'checked="checked"';
    foreach ($searchPlacesArray as $searchPlace) {
        $searchOptions['search_place_' . $searchPlace['id']] = 'checked="checked"';
    }

    // Seach options
    $searchOptions['sortable_tables'] = 'checked="checked"';

    // Search in documents
    $searchOptions['documents_search_in_selector'] = 'checked="checked"';
    $searchOptions['id'] = 'checked="checked"';
    $searchOptions['pagetitle'] = 'checked="checked"';
    $searchOptions['longtitle'] = 'checked="checked"';
    $searchOptions['description'] = 'checked="checked"';
    $searchOptions['alias'] = 'checked="checked"';
    $searchOptions['introtext'] = 'checked="checked"';
    $searchOptions['menutitle'] = 'checked="checked"';
    $searchOptions['content'] = 'checked="checked"';
    $searchOptions['tvs'] = 'checked="checked"';

    // Search in resources
    $searchOptions['resources_search_in_selector'] = 'checked="checked"';
    $searchOptions['resources_id'] = 'checked="checked"';
    $searchOptions['resources_name'] = 'checked="checked"';
    $searchOptions['resources_description'] = 'checked="checked"';
    $searchOptions['resources_other'] = 'checked="checked"';

    // search resutls display
    $searchOptions['entries_100'] = 'checked="checked"';
}

// set session vars
$_SESSION['docfinder_checkform'] = $checkform;

$_SESSION['docfinder_documents_search_in_selector'] = $searchOptions['id'];
$_SESSION['docfinder_id'] = $searchOptions['id'];
$_SESSION['docfinder_pagetitle'] = $searchOptions['pagetitle'];
$_SESSION['docfinder_longtitle'] = $searchOptions['longtitle'];
$_SESSION['docfinder_description'] = $searchOptions['description'];
$_SESSION['docfinder_alias'] = $searchOptions['alias'];
$_SESSION['docfinder_introtext'] = $searchOptions['introtext'];
$_SESSION['docfinder_menutitle'] = $searchOptions['menutitle'];
$_SESSION['docfinder_content'] = $searchOptions['content'];
$_SESSION['docfinder_tvs'] = $searchOptions['tvs'];

$_SESSION['docfinder_resources_search_in_selector'] = $searchOptions['resources_search_in_selector'];
$_SESSION['docfinder_resources_id'] = $searchOptions['resources_id'];
$_SESSION['docfinder_resources_name'] = $searchOptions['resources_name'];
$_SESSION['docfinder_resources_description'] = $searchOptions['resources_description'];
$_SESSION['docfinder_resources_other'] = $searchOptions['resources_other'];

$_SESSION['docfinder_case_sensitive'] = $searchOptions['case_sensitive'];
$_SESSION['docfinder_regular_expression'] = $searchOptions['regular_expression'];
$_SESSION['docfinder_regular_expression_status'] = $searchOptions['regular_expression_status'];
$_SESSION['docfinder_sortable_tables'] = $searchOptions['sortable_tables'];

$_SESSION['docfinder_createdon_start'] = $searchOptions['createdon_start'];
$_SESSION['docfinder_createdon_end'] = $searchOptions['createdon_end'];
$_SESSION['docfinder_editedon_start'] = $searchOptions['editedon_start'];
$_SESSION['docfinder_editedon_end'] = $searchOptions['editedon_end'];

$_SESSION['docfinder_search_place_selector'] = $searchOptions['search_place_selector'];
foreach ($searchPlacesArray as $searchPlace) {
    $_SESSION['docfinder_search_place_' . $searchPlace['id']] = $searchOptions['search_place_' . $searchPlace['id']];
}

$_SESSION['docfinder_entries_50'] = $searchOptions['entries_50'];
$_SESSION['docfinder_entries_100'] = $searchOptions['entries_100'];
$_SESSION['docfinder_entries_All'] = $searchOptions['entries_All'];

$_SESSION['docfinder_string'] = $search['string'];
$_SESSION['docfinder_parents'] = $search['parents'];

// session vars: history
if (isset($_SESSION['search_history'])) $_SESSION['search_history'] = ";" . $search['string'] . $_SESSION['search_history'];
else if ($search['string']) $_SESSION['search_history'] = ";" . $search['string'];
if (isset($_SESSION['replace_history']) and $searchOptions['replace']) $_SESSION['replace_history'] = ";" . $searchOptions['replace'] . $_SESSION['replace_history'];
else if ($searchOptions['replace']) $_SESSION['replace_history'] = ";" . $searchOptions['replace'];

$history = array(
    'search' => printHistory("search"),
    'replace' => printHistory("replace"),
);
$searchPlaces = array();
foreach ($searchPlacesArray as $searchPlace) {
    $searchPlaces[] = '<label><input type="checkbox" class="checkbox" name="search_place_' . $searchPlace['id'] . '" id="search_place_' . $searchPlace['id'] . '" ' . $searchOptions['search_place_' . $searchPlace['id']] . ' />' . $searchPlace['name'] . '</label>';
}

// separate responses for AJAX and normal page requests
switch ($checkform) {
    case 'ajax_get_results':
        $chunkie = new newChunkie($modx, array('basepath' => DF_PATH));
        $chunkie->setTpl(printResultTabs($search, $searchOptions, $theme, $searchPlacesArray));
        $chunkie->setPlaceholder('lang', $_lang, 'module');
        $chunkie->setPlaceholder('options', $options, 'module');
        $chunkie->prepareTemplate('', array(), 'module');
        $output = $chunkie->process('module');
        break;
    case 'ajax_get_searchHistory':
        $chunkie = new newChunkie($modx, array('basepath' => DF_PATH));
        $chunkie->setTpl($history['search']);
        $chunkie->setPlaceholder('lang', $_lang, 'module');
        $chunkie->setPlaceholder('options', $options, 'module');
        $chunkie->prepareTemplate('', array(), 'module');
        $output = $chunkie->process('module');
        break;
    case 'ajax_get_replaceHistory':
        $chunkie = new newChunkie($modx, array('basepath' => DF_PATH));
        $chunkie->setTpl($history['replace']);
        $chunkie->setPlaceholder('lang', $_lang, 'module');
        $chunkie->setPlaceholder('options', $options, 'module');
        $chunkie->prepareTemplate('', array(), 'module');
        $output = $chunkie->process('module');
        break;
    default:
        $chunkie = new newChunkie($modx, array('basepath' => DF_PATH));
        $chunkie->setPlaceholder('search_places', implode("\n", $searchPlaces), 'module');
        $chunkie->setPlaceholder('search_options', $searchOptions, 'module');
        $chunkie->setPlaceholder('history', $history, 'module');
        $chunkie->setPlaceholder('lang', $_lang, 'module');
        $chunkie->setPlaceholder('options', $options, 'module');

        $chunkie->setTpl($chunkie->getTemplateChunk('@FILE templates/module.template.html'));
        $chunkie->prepareTemplate('', array(), 'module');
        $output = $chunkie->process('module');
        break;
}
echo $output;
?>