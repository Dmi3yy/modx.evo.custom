<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<script type="text/javascript" src="media/script/tabpane.js"></script>

<h1><?php echo $_lang['help']; ?></h1>

<div class="sectionBody">
    <div class="tab-pane" id="resourcesPane">
        <script type="text/javascript">
            tpResources = new WebFXTabPane( document.getElementById( "resourcesPane" ), <?php echo $modx->config['remember_last_tab'] == 0 ? 'false' : 'true'; ?> );
        </script>
<?php
$help_dir = MODX_BASE_PATH . 'assets/templates/help';
if(file_exists($help_dir)==false)
{
	echo '<h3>' . $_lang["credits"] . '</h3>';
	echo '<div>' . $_lang["about_msg"] . '</div>';
	echo '<h3>' . $_lang["help"] . '</h3>';
	echo '<div>' . $_lang["help_msg"] . '</div>';
	exit;
}

if ($files = scandir($help_dir))
{
	foreach ($files as $file)
	{
		if ($file != "." && $file != ".." && $file != ".svn")
		{
			$help[] = $file;
		}
	}
}


natcasesort($help);

foreach($help as $k=>$v) {

    $helpname =  substr($v, 0, strrpos($v, '.'));

    $prefix = substr($helpname, 0, 2);
    if(is_numeric($prefix)) {
        $helpname =  substr($helpname, 2, strlen($helpname)-1 );
    }

    $helpname = str_replace('_', ' ', $helpname);
    echo '<div class="tab-page" id="tab'.$v.'Help">';
    echo '<h2 class="tab">'.$helpname.'</h2>';
    echo '<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tab'.$v.'Help" ) );</script>';
    include ($help_dir . '/' . $v);
    echo '</div>';
}
?>
    </div>
</div>
