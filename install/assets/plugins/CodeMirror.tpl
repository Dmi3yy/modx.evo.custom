//<?php
/**
 * CodeMirror
 *
 * JavaScript library that can be used to create a relatively pleasant editor interface
 *
 * @category    plugin
 * @version     2.23
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      hansek from www.modxcms.cz <http://www.modxcms.cz>
 * @internal    @events OnDocFormRender,OnChunkFormRender,OnModFormRender,OnPluginFormRender,OnSnipFormRender,OnTempFormRender
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &theme=Theme;list;,cobalt,ecllipse,elegant,monokai,neat,night,rubyblue; &indentUnit=Indent unit;int;4 &tabSize=The width of a tab character;int;4
 */

/**
 * @name: CodeMirror
 * @description: <b>2.23</b> JavaScript library that can be used to create a relatively pleasant editor interface
 *
 * @events:
 * - OnDocFormRender
 * - OnChunkFormRender
 * - OnModFormRender
 * - OnPluginFormRender
 * - OnSnipFormRender
 * - OnTempFormRender
 *
 */

$_CM_BASE = 'assets/plugins/codemirror/';

$_CM_URL = $modx->config['site_url'] . $_CM_BASE;

require(MODX_BASE_PATH. $_CM_BASE .'codemirror.plugin.php');

