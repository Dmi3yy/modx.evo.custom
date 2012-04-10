<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<style type="text/css">
h3 {font-weight:bold;letter-spacing:2px;font-size:1;margin-top:10px;}
h4 {font-weight:bold;letter-spacing:2px;}
pre {border:1px dashed #ccc;background-color:#fcfcfc;padding:15px;}
ul {margin-bottom:15px;}
td {vertical-align:top;padding-bottom:7px;}
</style>

<div class="sectionHeader">サポートに必要な情報</div>
<div class="sectionBody" style="padding:10px 20px;">
<h3>サポートに必要な情報</h3>
<p>
<a href="http://forum.modx.jp/" target="_blank">公式フォーラム</a>でサポートを受けることができます。以下の情報を付記いただくと解決の助けとなります。<br />
<a href="index.php?a=114">イベントログ</a>に重要なヒントが記録されていることもあります。
</p>
<?php


$info = array(
              'OS'  => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m'),
              'PHPのバージョン' => PHP_VERSION,
              'セーフモード'  => (ini_get('safe_mode') ==0) ? 'off' : 'on',
              'php_sapi_name'  => php_sapi_name(),
              'MySQLのバージョン'=>$modx->db->getVersion(),
              'MySQLホスト情報' => mysql_get_host_info(),
//              'mysql_get_client_info' => mysql_get_client_info(),
              'MODXのバージョン' => $modx_version,
              'サイトのURL'  => $modx->config['site_url'],
              'ホスト名' => gethostbyaddr(getenv('SERVER_ADDR')),
              'MODX_BASE_URL' => MODX_BASE_URL,
              'siteCacheのサイズ' => $modx->nicesize(filesize(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php')) . '(コンテンツ構成およびシステム構成のキャッシュです。あまり大きくなるとパフォーマンスに影響します。不要な拡張機能を整理する・拡張機能の本体コードを外部ファイル化するなどして抑制できます。サイト構成にもよりますが、1MB前後までなら問題ありません)',
              'upload_tmp_dir' => ini_get('upload_tmp_dir') . '(ファイルアップロード処理のために一時的なファイル保存領域として用いるテンポラリディレクトリ。この値が空になっている時は、OSが認識するテンポラリディレクトリが用いられます)',
              'memory_limit' => ini_get('memory_limit') . '(スクリプトが確保できる最大メモリ。通常はpost_max_sizeよりも大きい値にします)',
              'post_max_size' => ini_get('post_max_size') . '(POSTデータに許可される最大サイズ。POSTには複数のデータが含まれるので、通常はupload_max_filesizeよりも大きい値にします)',
              'upload_max_filesize' => ini_get('upload_max_filesize') . '(アップロードを受け付けるファイルの最大サイズ)',
              'max_execution_time' => ini_get('max_execution_time') . '秒(PHP処理の制限時間。スクリプト暴走の継続を防止します)',
              'max_input_time' => ini_get('max_input_time') . '秒(POST・GET・ファイルアップロードなどの入力を処理する制限時間。回線の太さの影響を受けることもあります)',
              'session.save_path' => ini_get('session.save_path') . '(セッションデータを保存するディレクトリ。CGI版PHPの場合はユーザの違いが原因でここに書き込み権限がない場合があるため、注意が必要です)',
              );

echo '<p>'.getenv('SERVER_SOFTWARE') .'</p>'. PHP_EOL . PHP_EOL;

echo '<table style="margin-bottom:20px;">';
foreach($info as $key=>$value)
{
    echo '<tr><td style="padding-right:30px;">' . $key . '</td><td>' . $value . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;



echo '<h4>mbstring</h4>' . PHP_EOL . PHP_EOL;
echo '<table style="margin-bottom:20px;">';
$mbstring_array = array('mbstring.detect_order',
'mbstring.encoding_translation',
'mbstring.func_overload',
'mbstring.http_input',
'mbstring.http_output',
'mbstring.internal_encoding',
'mbstring.language',
'mbstring.strict_detection',
'mbstring.substitute_character');

foreach($mbstring_array as $v)
{
    $key = $v;
    $value = ini_get($v)!==false ? ini_get($v): 'no value';
    echo '<tr><td style="padding-right:30px;">' . $key . '</td><td>' . $value . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;

//Mysql char set
echo '<h4>MySQLの文字コード情報</h4>' . PHP_EOL . PHP_EOL;
echo '<table style="margin-bottom:20px;">';
$res = $modx->db->query("SHOW VARIABLES LIKE 'collation_database';");
$collation = $modx->db->getRow($res, 'num');
global $database_connection_method;
echo '<tr><td style="padding-right:30px;">接続メソッド</td><td>' . $database_connection_method . '</td></tr>' . PHP_EOL;
echo '<tr><td style="padding-right:30px;">文字セット照合順序</td><td>' . $collation[1] . '</td></tr>' . PHP_EOL;
$rs = $modx->db->query("SHOW VARIABLES LIKE 'char%';");
while ($row = $modx->db->getRow($rs)){
  echo '<tr><td style="padding-right:30px;">' . $row['Variable_name'] . '</td><td>' . $row['Value'] . '</td></tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;

?>
<h3>さらに詳細な情報</h3>
<p>
<a href="index.php?a=200">phpinfo</a> をご覧ください。文字化け関係は<a href="index.php?a=200#module_mbstring">mbstring</a>、captcha関係は<a href="index.php?a=200#module_gd">GDやFreeType</a>などを確認する必要があります。
</p>
</div>
