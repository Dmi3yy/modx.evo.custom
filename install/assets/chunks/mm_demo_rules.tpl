/**
 * mm_demo_rules
 * 
 * ManagerManager用のカスタマイズルール(サンプル)
 * 
 * @category	chunk
 * @version 	1.0.5r1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Demo Content
 * @internal    @overwrite false
 * @internal    @installset base, sample
 */

/* ****************
【ご注意】
実運用の際は、必ずこのチャンクの名前を変更し(たとえばmm_rulesなど)、ManagerManagerプラグインの「Configuration Chunk」で新しいチャンク名を設定してください。
**************** */

mm_widget_showimagetvs(); // Imageタイプのテンプレート変数の画像をプレビューします
if($modx->config['track_visitors']==='0') mm_hideFields('log');



/* ==========================================================
http://modx.jp/docs/extras/plugins/mm.html
ドキュメント

// 以下、サンプルとして参考にしてみてください。

//     mm_widget_tags('documentTags'); // Give blog tag editing capabilities to the 'documentTags' TV

// mm_renameField('introtext','ページの要約'); //「introtext」の項目名を変更する
// mm_changeFieldHelp('alias', 'このページのエイリアス名を入力。URLとして用いるため半角英数で。'); // エイリアスのチップヘルプをカスタマイズ
// mm_widget_colors('color', '#666666'); // マウス操作で色を選択しカラーコードを入力するウィジェット

// Administratorロール以外(!1でID=1以外という意味)のメンバーに対する指定
// mm_hideFields('link_attributes', '!1');
// mm_hideFields('loginName ', '!1');
// mm_renameField('alias','URL alias','!1');


// 「$news_role」と「$news_tpl」それぞれにロールID・テンプレートIDを設定します。

// $news_role = '3';
// mm_hideFields('pagetitle,menutitle,link_attributes,template,menuindex,description,show_in_menu,which_editor,is_folder,is_richtext,log,searchable,cacheable,clear_cache', $news_role); // 大半の入力項目を隠して投稿画面をシンプルにします
// mm_renameTab('settings', '公開設定', $news_role); // settingsタブのタブ名を変更します
// mm_synch_fields('pagetitle,menutitle,longtitle', $news_role); // 3つの入力項目の値を揃えます
// mm_renameField('longtitle','Headline', $news_role, '', 'This will be displayed at the top of each page');

// 新着情報テンプレート用(サンプルコンテンツには含まれない架空のテンプレートです)
// $news_tpl = '8';
// mm_createTab('Categories','HrCats', '', $news_tpl, '', '600'); // 投稿画面にHrCatsというタブを追加
// mm_moveFieldsToTab('updateImage1', 'general', '', $news_tpl); // テンプレート変数「updateImage1」をgeneralタブに移動
// mm_hideFields('menuindex,show_in_menu', '', $news_tpl); // menuindexとshow_in_menuを隠す
// mm_changeFieldHelp('longtitle', 'ヘッドラインとして表示されます', '', $news_tpl); // チップヘルプをカスタマイズ
// mm_changeFieldHelp('introtext', 'A short summary of the story', '', $news_tpl);
// mm_changeFieldHelp('parent', 'To move this story to a different folder: Click this icon to activate, then choose a new folder in the tree on the left.', '', $news_tpl);
*/
