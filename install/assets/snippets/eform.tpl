//<?php
/**
 * eForm
 * 
 * メール送信フォームなどに使える多機能フォームプロセッサー
 *
 * @category 	snippet
 * @version 	1.4.4.7
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &sendAsText=テキストで送る;string;1
 * @internal	@modx_category Forms
 * @internal    @installset base, sample
 */

# eForm 1.4.4.7 - Electronic Form Snippet
# Original created by Raymond Irving 15-Dec-2004.
# Version 1.3+ extended by Jelle Jager (TobyL) September 2006
# -----------------------------------------------------
# Captcha image support - thanks to Djamoer
# Multi checkbox, radio, select support - thanks to Djamoer
# Form Parser and extened validation - by Jelle Jager
#

# Set Snippet Paths
$snip_dir = isset($snip_dir) ? $snip_dir : 'eform';
$snipPath = "{$modx->config['base_path']}assets/snippets/{$snip_dir}/";

# check if inside manager
if ($modx->isBackend()) return ''; // don't go any further when inside manager

# Start processing

$version = '1.4.4.7';
include_once ("{$snipPath}eform.inc.php");

$output = eForm($modx,$params);

# Return
return $output;