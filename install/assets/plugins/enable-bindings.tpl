//<?php
/**
 * Bindings機能の有効無効
 * 
 * グローバル設定にBindings機能の有効無効の設定項目を追加します
 *
 * @category 	plugin
 * @version 	0.1.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@events OnInterfaceSettingsRender 
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base
  *
 * @author yama  / created: 2010/10/03
 */

$e = &$modx->event; 
global $settings;
$action = $modx->manager->action;
if($action!==17) return;
$enable_bindings = (is_null($settings['enable_bindings'])) ? '1' : $settings['enable_bindings'];
$html = render_html($enable_bindings);
$e->output($html);

function render_html($enable_bindings)
{
	global $_lang;
	$str = '<h4 style="padding:5px;background-color:#eeeeee;">@Bindingsの設定</h4><table id="enable_bindings" class="settings">' . "\n";
	$str .= '  <tr>' . "\n";
	$str .= '    <th>@Bindingsを有効にする</th>' . "\n";
	$str .= '    <td><input onchange="documentDirty=true;" type="radio" name="enable_bindings" value="1" ' . ($enable_bindings=='1' ? 'checked="checked"' : "") . ' />' . "\n";
	$str .=       $_lang["yes"] . '<br />' . "\n";
	$str .= '      <input onchange="documentDirty=true;" type="radio" name="enable_bindings" value="0" ' . (($enable_bindings=='0' || !isset($enable_bindings)) ? 'checked="checked"' : "" ) . ' />' . "\n";
	$str .=       $_lang["no"] . '<div><a href="http://www.google.com/cse?cx=007286147079563201032%3Aigbcdgg0jyo&q=Bindings" target="_blank">@Bindings機能</a>を有効にします。この機能は、投稿画面上の入力フィールド(テンプレート変数)に任意のコマンドを記述し、実行するものです。PHP文の実行などが可能なため、複数メンバーでサイトを運用する場合、当機能の運用には注意が必要です。</div></td>' . "\n";
	$str .= '  </tr>' . "\n";
	$str .= '</table>' . "\n";
	return $str;
}
