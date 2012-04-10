//<?php
/**
 * 管理画面カスタマイズ
 *
 * ログイン画面・ダッシュボードのカスタマイズコード
 *
 * @category 	plugin
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@events OnManagerLoginFormPrerender,OnManagerWelcomePrerender
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base
 */

/*当プラグインを無効にした場合はMODX本体内蔵のコードが出力されます*/

global $tpl;
switch($modx->event->name)
{
	case 'OnManagerLoginFormPrerender':
		$tpl = $modx->getChunk('ログイン画面');
		break;
	case 'OnManagerWelcomePrerender':
		$tpl = $modx->getChunk('ダッシュボード');
		break;
}
