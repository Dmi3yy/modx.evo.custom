//<?php
/**
 * ManagerManager
 * 
 * Customize the MODx Manager to offer bespoke admin functions for end users.
 *
 * @category 	plugin
 * @version 	0.4 (2012-11-14)
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &remove_deprecated_tv_types_pref=Remove deprecated TV types;list;yes,no;yes &which_jquery=jQuery source;list;local (assets/js),remote (google code),manual url (specify below);local (assets/js) &jquery_manual_url=jQuery URL override;text; &config_chunk=Configuration Chunk;text;
 * @internal	@events OnDocFormRender,OnDocFormPrerender,OnBeforeDocFormSave,OnPluginFormRender,OnTVFormRender
 * @internal	@modx_category Manager and Admin
 * @internal    @legacy_names Image TV Preview, Show Image TVs
 */

include($modx->config['base_path'].'assets/plugins/managermanager/mm.inc.php');
