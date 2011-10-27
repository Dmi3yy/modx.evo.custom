<?php
	include("vars.php");
	
	$ts 		= $_POST["ts"];
	
	$m_ts		= $_POST["parent"] == "month" ? mktime(1, 1, 1, date("n", $ts), 1, date("Y", $ts)) : ""; //Selected month timestamp
	
	$c_ts 		= mktime(1, 1, 1, date("n"), 1, date("Y"));	//Timestamp current month
	
	$ts_year	= date("Y", $ts);
	
	$pr_ts		= mktime(1, 1, 1, 1, 1, $ts_year-1);
	$nx_ts		= mktime(1, 1, 1, 1, 1, $ts_year+1);
?>
<table class="year" cellpadding="0" summary="{'ts': '<?php $ts?>', 'pr_ts': '<?php $pr_ts?>', 'nx_ts': '<?php $nx_ts;?>', 'label': '<?php $ts_year?>', 'current': 'year', 'parent': 'decade'<?php ($ts_year == 1979 ? ", 'hide_left_arrow': '1'" : '').($ts_year == 2030 ? ", 'hide_right_arrow': '1'" : '')?>}">
<?php
	//Add months
	$m = 1;
	for($i = 0; $i < 3; $i++) {
		echo "\t<tr>";
		for($y = 0; $y < 4; $y++) {
			$i_ts = mktime(1, 1, 1, $m, 1, $ts_year);
			echo '<td ts="'.$i_ts.'" class="'.($m_ts == $i_ts ? 'selected' : '').($c_ts == $i_ts ? 'current' : '').'">'.$month_s_labels[date('n', $i_ts)-1].'</td>';
			$m++;
		}
		echo "</tr>\n";
		//onclick=\"$name.u('inc/month', 'ts=$i_ts', '$name.fade()');\"
	}
?>
</table>