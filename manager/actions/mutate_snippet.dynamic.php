<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

switch((int) $_REQUEST['a'])
{
	case 22:
		if(!$modx->hasPermission('edit_snippet'))
		{
			$e->setError(3);
			$e->dumpError();
		}
		break;
	case 23:
		if(!$modx->hasPermission('new_snippet'))
		{
			$e->setError(3);
			$e->dumpError();
		}
		break;
	default:
		$e->setError(3);
		$e->dumpError();
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

// Get table Names (alphabetical)
$tbl_active_users       = $modx->getFullTableName('active_users');
$tbl_site_module_depobj = $modx->getFullTableName('site_module_depobj');
$tbl_site_modules       = $modx->getFullTableName('site_modules');
$tbl_site_snippets      = $modx->getFullTableName('site_snippets');

// check to see the snippet editor isn't locked
$rs = $modx->db->select('internalKey, username',$tbl_active_users,"action=22 AND id='{$id}'");
$limit = $modx->db->getRecordCount($rs);
if($limit>1) {
	for ($i=0;$i<$limit;$i++)
	{
		$lock = $modx->db->getRow($rs);
		if($lock['internalKey']!=$modx->getLoginUserID())
		{
			$msg = sprintf($_lang['lock_msg'],$lock['username'],"snippet");
			$e->setError(5, $msg);
			$e->dumpError();
		}
	}
}
// end check for lock


if(isset($_GET['id'])) {
	$rs = $modx->db->select('*',$tbl_site_snippets,"id='{$id}'");
	$limit = $modx->db->getRecordCount($rs);
	if($limit>1) {
		echo "Oops, Multiple snippets sharing same unique id. Not good.<p>";
		exit;
	}
	if($limit<1) {
		header("Location: /index.php?id=".$site_start);
	}
	$content = $modx->db->getRow($rs);
	$_SESSION['itemname']=$content['name'];
	if($content['locked']==1 && $_SESSION['mgrRole']!=1) {
		$e->setError(3);
		$e->dumpError();
	}
} else {
	$_SESSION['itemname']="New snippet";
}
?>
<script type="text/javascript">

function duplicaterecord(){
	if(confirm("<?php echo $_lang['confirm_duplicate_record']?>")==true) {
		documentDirty=false;
		document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=98";
	}
}

function deletedocument() {
	if(confirm("<?php echo $_lang['confirm_delete_snippet']?>")==true) {
		documentDirty=false;
		document.location.href="index.php?id=" + document.mutate.id.value + "&a=25";
	}
}

function setTextWrap(ctrl,b){
	if(!ctrl) return;
	ctrl.wrap = (b)? "soft":"off";
}

// Current Params
var currentParams = {};

function showParameters(ctrl) {
	var c,p,df,cp;
	var ar,desc,value,key,dt;

	currentParams = {}; // reset;

	if (ctrl) {
		f = ctrl.form;
	} else {
		f= document.forms['mutate'];
		if(!f) return;
	}

	// setup parameters
	tr = (document.getElementById) ? document.getElementById('displayparamrow'):document.all['displayparamrow'];
	dp = (f.properties.value) ? f.properties.value.split("&"):"";
	if(!dp) tr.style.display='none';
	else {
		t='<table width="300" style="margin-bottom:3px;">';
		for(p = 0; p < dp.length; p++) {
			dp[p]=(dp[p]+'').replace(/^\s|\s$/,""); // trim
			ar = dp[p].split("=");
			key = ar[0]		// param
			ar = (ar[1]+'').split(";");
			desc = ar[0];	// description
			dt = ar[1];		// data type
			value = decode((ar[2])? ar[2]:'');

			// store values for later retrieval
			if (key && dt=='list') currentParams[key] = [desc,dt,value,ar[3]];
			else if (key) currentParams[key] = [desc,dt,value];

			if (dt) {
				switch(dt) {
				case 'int':
					c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="30" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
					break;
				case 'menu':
					value = ar[3];
					c = '<select name="prop_'+key+'" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
					ls = (ar[2]+'').split(",");
					if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
					for(i=0;i<ls.length;i++){
						c += '<option value="'+ls[i]+'"'+((ls[i]==value)? ' selected="selected"':'')+'>'+ls[i]+'</option>';
					}
					c += '</select>';
					break;
				case 'list':
					value = ar[3];
					ls = (ar[2]+'').split(",");
					if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
					c = '<select name="prop_'+key+'" size="'+ls.length+'" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
					for(i=0;i<ls.length;i++){
						c += '<option value="'+ls[i]+'"'+((ls[i]==value)? ' selected="selected"':'')+'>'+ls[i]+'</option>';
					}
					c += '</select>';
					break;
				case 'list-multi':
					value = (ar[3]+'').replace(/^\s|\s$/,"");
					arrValue = value.split(",")
					ls = (ar[2]+'').split(",");
					if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
					c = '<select name="prop_'+key+'" size="'+ls.length+'" multiple="multiple" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
					for(i=0;i<ls.length;i++){
						if(arrValue.length){
							for(j=0;j<arrValue.length;j++){
								if(ls[i]==arrValue[j]){
									c += '<option value="'+ls[i]+'" selected="selected">'+ls[i]+'</option>';
								}else{
									c += '<option value="'+ls[i]+'">'+ls[i]+'</option>';
								}
							}
						}else{
							c += '<option value="'+ls[i]+'">'+ls[i]+'</option>';
						}
					}
					c += '</select>';
					break;
				case 'textarea':
                    c = '<textarea class="phptextarea" name="prop_'+key+'" cols="50" rows="4" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">'+value+'</textarea>';
					break;
				default:  // string
					c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="30" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
					break;

				}
				t +='<tr><td><div>'+desc+'</div><div>'+c+'</div></td></tr>';
			};
		}
		t+='</table>';
		td = (document.getElementById) ? document.getElementById('displayparams'):document.all['displayparams'];
		td.innerHTML = t;
		tr.style.display='';
	}
	implodeParameters();
}

function setParameter(key,dt,ctrl) {
	var v;
	if(!ctrl) return null;
	switch (dt) {
		case 'int':
			ctrl.value = parseInt(ctrl.value);
			if(isNaN(ctrl.value)) ctrl.value = 0;
			v = ctrl.value;
			break;
		case 'menu':
			v = ctrl.options[ctrl.selectedIndex].value;
			currentParams[key][3] = v;
			implodeParameters();
			return;
			break;
		case 'list':
			v = ctrl.options[ctrl.selectedIndex].value;
			currentParams[key][3] = v;
			implodeParameters();
			return;
			break;
		case 'list-multi':
			var arrValues = new Array;
			for(var i=0; i < ctrl.options.length; i++){
				if(ctrl.options[i].selected){
					arrValues.push(ctrl.options[i].value);
				}
			}
			currentParams[key][3] = arrValues.toString();
			implodeParameters();
			return;
			break;
		default:
			v = ctrl.value+'';
			break;
	}
	currentParams[key][2] = v;
	implodeParameters();
}

// implode parameters
function implodeParameters(){
	var v, p, s='';
	for(p in currentParams){
		if(currentParams[p]) {
			v = currentParams[p].join(";");
			if(s && v) s+=' ';
			if(v) s += '&'+p+'='+ encode(v);
		}
	}
	document.forms['mutate'].properties.value = s;
}

function encode(s){
	s=s+'';
	s = s.replace(/\=/g,'%3D'); // =
	s = s.replace(/\&/g,'%26'); // &
	return s;
}

function decode(s){
	s=s+'';
	s = s.replace(/\%3D/g,'='); // =
	s = s.replace(/\%26/g,'&'); // &
	return s;
}

</script>

<form name="mutate" method="post" action="index.php?a=24" enctype="multipart/form-data">
<?php
	// invoke OnSnipFormPrerender event
	$evtOut = $modx->invokeEvent("OnSnipFormPrerender",array("id" => $id));
	if(is_array($evtOut)) echo implode("",$evtOut);
?>
	<input type="hidden" name="id" value="<?php echo $content['id']?>">
	<input type="hidden" name="mode" value="<?php echo $_GET['a']?>">

    <div id="actions">
    	  <ul class="actionButtons">
    		  <li id="Button1">
    			<a href="#" onclick="documentDirty=false; document.mutate.save.click();saveWait('mutate');">
    			  <img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?>
    			</a>
    			  <span class="and"> + </span>				
    			<select id="stay" name="stay">
    			  <option id="stay1" value="1" <?php echo $_REQUEST['stay']=='1' ? ' selected=""' : ''?> ><?php echo $_lang['stay_new']?></option>
    			  <option id="stay2" value="2" <?php echo $_REQUEST['stay']=='2' ? ' selected="selected"' : ''?> ><?php echo $_lang['stay']?></option>
    			  <option id="stay3" value=""  <?php echo $_REQUEST['stay']=='' ? ' selected=""' : ''?>  ><?php echo $_lang['close']?></option>
    			</select>		
    		  </li>
    		  <?php
    			if ($_GET['a'] == '22') { ?>
    		  <li id="Button2"><a href="#" onclick="duplicaterecord();"><img src="<?php echo $style_path; ?>icons/copy.gif" /> <?php echo $_lang["duplicate"]; ?></a></li>
    		  <li id="Button3" class="disabled"><a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"] ?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } else { ?>
    		  <li id="Button3"><a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"] ?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } ?>	
    		  <li id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=76';"><img src="<?php echo $_style['icons_cancel']; ?>" /> <?php echo $_lang['cancel']?></a></li>
    	  </ul>
    </div>

<h1><?php echo $_lang['snippet_title']?></h1>

<div class="sectionBody">
<?php echo $_lang['snippet_msg']?>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<div class="tab-pane" id="snipetPane">
	<script type="text/javascript">
		tpSnippet = new WebFXTabPane( document.getElementById( "snipetPane"), <?php echo (($modx->config['remember_last_tab'] == 2) || ($_GET['stay'] == 2 )) ? 'true' : 'false'; ?> );
	</script>

	<!-- General -->
    <div class="tab-page" id="tabSnippet">
    	<h2 class="tab"><?php echo $_lang['settings_general']?></h2>
    	<script type="text/javascript">tpSnippet.addTabPage( document.getElementById( "tabSnippet" ) );</script>
		<table>
		  <tr>
			<th align="left"><?php echo $_lang['snippet_name']?></th>
			<td align="left">[[<input name="name" type="text" maxlength="100" value="<?php echo htmlspecialchars($content['name'])?>" class="inputBox" style="width:300px;" onChange="documentDirty=true;">]]<span class="warning" id="savingMessage">&nbsp;</span></td>
		  </tr>
		</table>
		<!-- PHP text editor start -->
		<div>
		    <div style="padding:3px 8px; overflow:hidden;zoom:1; background-color:#eeeeee; border:1px solid #c3c3c3; border-bottom:none;margin-top:5px;">
		    	<span style="float:left;font-weight:bold;"><?php echo $_lang['snippet_code'];?></span>
		    	<span style="float:right;color:#707070;"><?php echo $_lang['wrap_lines'];?>
		    	<input name="wrap" type="checkbox" checked="checked" class="inputBox" onclick="setTextWrap(document.mutate.post,this.checked)" /></span>
		  	</div>
			<textarea class="phptextarea" dir="ltr" name="post" style="width:100%; height:370px;" wrap="soft" onchange="documentDirty=true;"><?php echo "<?php"."\n".trim(htmlspecialchars($content['snippet']))."\n"."?>"?></textarea>
			</div>
		<!-- PHP text editor end -->
		  	</div>
		
	<!-- Properties -->
    <div class="tab-page" id="tabProps">
    	<h2 class="tab"><?php echo $_lang['settings_properties']?></h2>
    	<script type="text/javascript">tpSnippet.addTabPage( document.getElementById( "tabProps" ) );</script>
		<table>
          <tr>
			<th align="left"><?php echo $_lang['existing_category']?>:</th>
			<td align="left">
			<select name="categoryid" style="width:300px;" onChange="documentDirty=true;">
			<option>&nbsp;</option>
				<?php
					include_once "categories.inc.php";
					$ds = getCategories();
					if($ds) foreach($ds as $n=>$v){
						echo '<option value="'.$v['id'].'"'.($content['category']==$v['id']? ' selected="selected"':'').'>'.htmlspecialchars($v['category']).'</option>';
					}
				?>
				</select>
			</td>
		  </tr>
          <tr>
			<th align="left" valign="top" style="padding-top:10px;"><?php echo $_lang['new_category']?>:</th>
			<td align="left" valign="top" style="padding-top:10px;"><input name="newcategory" type="text" maxlength="45" value="" class="inputBox" style="width:300px;" onChange="documentDirty=true;"></td>
		  </tr>
		  <tr>
			<th align="left" style="padding-top:10px"><?php echo $_lang['snippet_desc']?>:</th>
			<td align="left" style="padding-top:10px">
				<textarea name="description" onChange="documentDirty=true;" style="padding:0;height:4em;"><?php echo $content['description']?></textarea></td>
		  </tr>
		  <tr>
			<td style="padding-top:10px" align="left" valign="top" colspan="2">
			<label><input  style="padding:0;margin:0;" name="locked" type="checkbox" <?php echo $content['locked']==1 ? "checked='checked'" :'';?> class="inputBox"> <b><?php echo $_lang['lock_snippet']?></b> <span class="comment"><?php echo $_lang['lock_snippet_msg']?></span></label></td>
		  </tr>
<?php
		$from = "{$tbl_site_modules} AS sm ".
		       "INNER JOIN {$tbl_site_module_depobj} AS smd ON smd.module=sm.id AND smd.type=40 ".
		       "INNER JOIN {$tbl_site_snippets} AS ss ON ss.id=smd.resource ";
		$ds = $modx->db->select('sm.id,sm.name,sm.guid',$from,"smd.resource='{$id}' AND sm.enable_sharedparams='1'",'sm.name');
		$guid_total = $modx->db->getRecordCount($ds);
		if($guid_total > 0)
		{
			$options = '';
			while($row = $modx->db->getRow($ds))
			{
				$options .= "<option value='".$row['guid']."'".($content['moduleguid']==$row['guid']? " selected='selected'":"").">".htmlspecialchars($row['name'])."</option>";
			}
		}
?>
<?php if($guid_total > 0)
{
?>
          <tr>
			<th align="left" style="padding-top:10px;"><?php echo $_lang['import_params']?>:</th>
			<td align="left" valign="top" style="padding-top:10px;">
				<select name="moduleguid" style="width:300px;" onChange="documentDirty=true;">
				<?php echo $options; ?>
				</select>
			</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td align="left" valign="top"><span class="comment" ><?php echo $_lang['import_params_msg']?></div></td>
		  </tr>
<?php } ?>
		  <tr>
			<th align="left" valign="top"><?php echo $_lang['snippet_properties']?>:</th>
			<td align="left" valign="top"><textarea name="properties" maxlength="65535" class="inputBox phptextarea" onChange="showParameters(this);documentDirty=true;"><?php echo $content['properties']?></textarea></td>
		  </tr>
		  <tr id="displayparamrow">
			<td valign="top" align="left">&nbsp;</td>
			<td align="left" id="displayparams">&nbsp;</td>
		  </tr>
		</table>
		  	</div>
			</div>
		<input type="submit" name="save" style="display:none">
	</div>
<?php
// invoke OnSnipFormRender event
$evtOut = $modx->invokeEvent("OnSnipFormRender",array("id" => $id));
if(is_array($evtOut)) echo implode("",$evtOut);
?>
</form>

<script type="text/javascript">
setTimeout('showParameters();',10);
</script>
