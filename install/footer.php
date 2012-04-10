<?php
$ph = get_ph_footer();
$src = get_src_footer();
echo parse($src,$ph);

function get_ph_footer()
{
	global $_lang;
	$ph['footer1'] = $_lang['modx_footer1'];
	$ph['footer2'] = $_lang['modx_footer2'];
	return $ph;
}

function get_src_footer()
{
	$src = <<< EOT
			</div>
		</div><!-- // content -->
	</div><!-- // contentarea -->
<div id="footer">
	<div id="footer-inner">
	<div class="container_10">
		[+footer1+]
		<br />
		[+footer2+]
	</div>
	</div>
</div>
<!-- end install screen-->
</body>
</html>
EOT;
	return $src;
}
