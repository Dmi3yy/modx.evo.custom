/**
 * ダッシュボード
 * 
 * 管理画面ダッシュボードテンプレート
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Manager and Admin
 * @internal    @installset base
 */
<!-- welcome -->
<div style="margin: 20px 12px;">
	<script type="text/javascript" src="media/script/tabpane.js"></script>
	<div class="tab-pane" id="welcomePane" style="border:0">
    <script type="text/javascript">
        tpPane = new WebFXTabPane(document.getElementById( "welcomePane" ),false);
    </script>

		<!-- home tab -->
		<div class="tab-page" id="tabhome" style="padding-left:0; padding-right:0;">
[+OnManagerWelcomePrerender+]			
			<h2 class="tab">メイン</h2>
			<script type="text/javascript">tpPane.addTabPage( document.getElementById( "tabhome" ) );</script>
			<div class="sectionHeader">[+welcome_title+]</div>
			<div class="sectionBody">
                <h3 style="margin:0;font-weight:normal;margin:7px;">[+site_name+]</h3>
                <table border="0" cellpadding="5">
                  <tr>
                    <td width="100" align="right">
                        <img src='media/style/[+theme+]/images/misc/logo.png' alt='[+logo_slogan+]' />
                        <br /><br />
                    </td>
                    <td valign="top">
                        [+OnManagerWelcomeHome+]
                        [+SecurityIcon+]
                        [+WebUserIcon+]
                        [+ModulesIcon+]
                        [+ResourcesIcon+]
                        [+BackupIcon+]
                        [+HelpIcon+]
                        <br style="clear:both" /><!--+Modules+--><br style="clear:both" />
                        [+MessageInfo+]
                    </td>
                  </tr>
                </table>
			</div>
		</div>
		
		<!-- system check -->
		<div class="tab-page" id="tabcheck" style="display:[+config_display+]; padding-left:0; padding-right:0;">
			<h2 class="tab" style="display:[+config_display+]"><strong style="color:#EF1D1D;">[+settings_config+]</strong></h2>
			<script type="text/javascript"> if('[+config_display+]'=='block') tpPane.addTabPage( document.getElementById( "tabcheck" ) );</script>
			<div class="sectionHeader">[+configcheck_title+]</div>
			<div class="sectionBody">
				<img src="media/style/[+theme+]/images/icons/error.png" />
				[+config_check_results+]
			</div>
		</div>
		
		<div class="tab-page" id="tabNews" style="padding-left:0; padding-right:0">
		<!-- modx news -->
			<h2 class="tab">[+modx_news+]</h2>
			<script type="text/javascript">tpPane.addTabPage( document.getElementById( "tabNews" ) );</script>
			<div class="sectionHeader">[+modx_news_title+]</div>
			<div class="sectionBody">
				[+modx_news_content+]
			</div>
		<!-- security notices -->
			<div class="sectionHeader">[+modx_security_notices_title+]</div>
			<div class="sectionBody">
				[+modx_security_notices_content+]
			</div>
		</div>

		<div class="tab-page" id="tabYour" style="padding-left:0; padding-right:0">
			<h2 class="tab">[+yourinfo_title+]</h2>
			<script type="text/javascript">tpPane.addTabPage( document.getElementById( "tabYour" ) );</script>
		<!-- recent activities -->
			<div class="sectionHeader">[+activity_title+]</div>
			<div class="sectionBody">
				[+RecentInfo+]
			</div>
		<!-- user info -->
			<div class="sectionHeader">[+yourinfo_title+]</div>
			<div class="sectionBody">
				[+UserInfo+]
			</div>
		</div>

		<!-- online info -->
		<div class="tab-page" id="tabOnline" style="padding-left:0; padding-right:0">
			<h2 class="tab">[+online+]</h2>
			<script type="text/javascript">tpPane.addTabPage( document.getElementById( "tabOnline" ) );</script>
			<div class="sectionHeader">[+onlineusers_title+]</div><div class="sectionBody">
				[+OnlineInfo+]
			</div>
		</div>
[+OnManagerWelcomeRender+]
	</div>
</div>