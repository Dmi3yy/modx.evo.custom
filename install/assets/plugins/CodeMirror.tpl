//<?php
/**
 * CodeMirror
 *
 * JavaScript library that can be used to create a relatively pleasant editor interface
 *
 * @category    plugin
 * @version     2.21
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      hansek from www.modxcms.cz <http://www.modxcms.cz>
 * @internal    @events OnDocFormRender,OnChunkFormRender,OnModFormRender,OnPluginFormRender,OnSnipFormRender,OnTempFormRender
 * @internal	@modx_category Manager and Admin
 */

/**
 * @name: CodeMirror
 * @description: <b>2.21</b> JavaScript library that can be used to create a relatively pleasant editor interface
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

// relative path to CodeMirror path from /manager
$_CM_URL = '/assets/plugins/codemirror/';

require('..'. $_CM_URL .'codemirror.plugin.php');
