<?php
interface langVer
{
   const err_nomodx = 'No access to MODX API';
	const err_mode = 'Invalid mode';
	const err_loadjs = 'You must specify a set before calling the jquery forms';
	const err_noload = 'Unable to load data';
	const err_fatalload = 'An error occurred during startup';
	const err_del = 'An error occurred during the removal of';
	
	const form_nameblock = 'Versions';
	const form_descver = 'Description of changes';
	const form_savever = 'Save this version';
	const form_beforever = 'The previous version of the snippet';
	const form_noversion = 'Other versions of this snippet is not';
	const form_nodesc = 'no description';
	
	const word_del = 'Remove';
	const word_load = 'Load';
	const word_ver = 'Ver';
}
?>