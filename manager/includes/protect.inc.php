<?php
/**
 *    Protect against some common security flaws
 */

$gpc = array_merge($_GET, $_POST, $_COOKIE);
if(FindDangerValue($gpc)!==false)
{
	header('Status: 422 Unprocessable Entity');
	die();
}

// Null is evil
if (isset($_SERVER['QUERY_STRING']) && strpos(urldecode($_SERVER['QUERY_STRING']), chr(0)) !== false)
    die();

// Unregister globals
if (@ ini_get('register_globals')) {
    foreach ($_REQUEST as $key => $value) {
        $$key = null; // This is NOT paranoid because
        unset ($$key); // unset may not work.
    }
}
if (!function_exists('modx_sanitize_gpc'))
{
	function modx_sanitize_gpc(& $target, $count=0)
	{
		$s = array('[[',']]','[!','!]','[*','*]','[(',')]','{{','}}','[+','+]','[~','~]','[^','^]');
		$r = array('[ [','] ]','[ !','! ]','[ *','* ]','[ (',') ]','{ {','} }','[ +','+ ]','[ ~','~ ]','[ ^','^ ]');
		foreach ($target as $key => $value)
		{
			if (is_array($value))
			{
				$count++;
				if(10 < $count)
				{
					echo 'too many nested array';
					exit;
				}
				modx_sanitize_gpc($value, $count);
			}
			else
			{
				$value = str_replace($s,$r,$value);
				$value = preg_replace('/<script/i', 'sanitized_by_modx<s cript', $value);
				$value = preg_replace('/&#(\d+);/', 'sanitized_by_modx& #$1', $value);
				$target[$key] = $value;
			}
			$count=0;
		}
		return $target;
	}
}
modx_sanitize_gpc($_GET);
if (!defined('IN_MANAGER_MODE') || (defined('IN_MANAGER_MODE') && (!IN_MANAGER_MODE || IN_MANAGER_MODE == 'false')))
{
    modx_sanitize_gpc($_POST);
}
modx_sanitize_gpc($_COOKIE);
modx_sanitize_gpc($_REQUEST);

foreach (array ('PHP_SELF', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'QUERY_STRING') as $key) {
    $_SERVER[$key] = isset ($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES) : null;
}

// Unset vars
unset ($key, $value);

/*
// php bug 53632 (php 4 <= 4.4.9 and php 5 <= 5.3.4)
if (strstr(str_replace('.','',serialize(array_merge($_GET, $_POST, $_COOKIE))), '22250738585072011')) {
    header('Status: 422 Unprocessable Entity');
    die();
}
*/
function FindDangerValue($value, $found = false) {
	if($found || (strpos(str_replace('.', '', serialize($value)), '22250738585072011') !== false))
	{
		//文字列の中に問題の数字が埋め込まれているケースを排除する by @enogu
		if (is_array($value))
		{
			foreach ($value as $item)
			{
				if(FindDangerValue($item, true))
				{
					return true;
				}
			}
		}
		else
		{
			$item = strval($value);
			$matches = '';
			if (preg_match('/^([0.]*2[0125738.]{15,16}10*)e(-[0-9]+)$/i', $item, $matches))
			{
				$exp = intval($matches[2]) + 1;
				if (2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}"))
				{
					return true;
				}
			}
		}
	}
	return false;
}
