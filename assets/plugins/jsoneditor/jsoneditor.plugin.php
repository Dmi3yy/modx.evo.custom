<?php
/**
* JSONeditor 
*/
global $content;
$e = &$modx->Event; 
switch ($e->name)
{
        case "OnRichTextEditorRegister":
                $e->output("JSONeditor"); 
                break;
                
        case "OnRichTextEditorInit":
                if($editor!=='JSONeditor') return;
                $base_path = MODX_BASE_URL;
				
                $richIds=implode('","',$elements);
                $output .= <<< OUT
				<!-- JSON editor -->
				<link rel="stylesheet" type="text/css" href="{$base_path}assets/plugins/jsoneditor/jsoneditor/jsoneditor.css">
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/jsoneditor.js"></script>

				<!-- ace code editor -->
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/lib/ace/ace.js"></script>
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/lib/ace/mode-json.js"></script>
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/lib/ace/theme-textmate.js"></script>
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/lib/ace/theme-jsoneditor.js"></script>

				<!-- json lint -->
				<script type="text/javascript" src="{$base_path}assets/plugins/jsoneditor/jsoneditor/lib/jsonlint/jsonlint.js"></script>
				<script type="text/javascript">
				var richIds = ["$richIds"];
				var jsonEditors = {};
				for (var richField=0;richField<richIds.length;richField++){
						var richId = richIds[richField];
						var el = document.getElementById(richId);
						var newDiv = document.createElement('div');
						newDiv.setAttribute('id', 'jsonEditor'+richId);
						newDiv.style.height = '$default_height'+'px';
						
						var options = {
							mode: 'tree',
							modes: ['code', 'form', 'text', 'tree', 'view'], // allowed modes
							error: function (err) {
							  alert(err.toString());
							}
						  };

						var editor = new jsoneditor.JSONEditor(newDiv, options);
						editor.setText(el.innerHTML || "$init_value");
						editor.expandAll();
						jsonEditors[richId] = editor;
						el.parentNode.insertBefore(newDiv,el.nextSibling);
						el.style.display='none';
						
						var help = document.createElement('div');
						help.addEventListener("click", function(){document.getElementById("jsonEditorHelp").style.display="block";});
						var helpInner = document.createElement('div');
						var helpText = document.createTextNode("Keyboard shortcuts");
						helpInner.style.display='none';
						helpInner.setAttribute("id", "jsonEditorHelp");
						helpInner.innerHTML = "<table>"+
												"<tr><th>Key</th><th>Description</th></tr>"+
												"<tr><td>Alt+Arrows</td><td>Move the caret up/down/left/right between fields</td></tr>"+
												"<tr><td>Shift+Alt+Arrows</td><td>Move field up/down/left/right</td></tr>"+
												"<tr><td>Ctrl+D</td><td>Duplicate field</td></tr>"+
												"<tr><td>Ctrl+Del</td><td>Remove field</td></tr>"+
												"<tr><td>Ctrl+Enter</td><td>Open link when on a field containing an url</td></tr>"+
												"<tr><td>Ctrl+Ins</td><td>Insert a new field with type auto</td></tr>"+
												"<tr><td>Ctrl+Shift+Ins</td><td>Append a new field with type auto</td></tr>"+
												"<tr><td>Ctrl+E</td><td>Expand or collapse field</td></tr>"+
												"<tr><td>Alt+End</td><td>Move the caret to the last field</td></tr>"+
												"<tr><td>Ctrl+F</td><td>Find</td></tr>"+
												"<tr><td>F3, Ctrl+G<br></td><td>Find next</td></tr>"+
												"<tr><td>Shift+F3, Ctrl+Shift+G</td><td>Find previous</td></tr>"+
												"<tr><td>Alt+Home</td><td>Move the caret to the first field</td></tr>"+
												"<tr><td>Ctrl+M</td><td>Show actions menu</td></tr>"+
												"<tr><td>Ctrl+Z</td><td>Undo last action</td></tr>"+
												"<tr><td>Ctrl+Shift+Z</td><td>Redo</td></tr>"+
											  "</table>";
						help.appendChild(helpText);
						help.appendChild(helpInner);
						newDiv.parentNode.insertBefore(help,newDiv.nextSibling);
						
						form = el.form;
						if (form.attachEvent) {
							form.attachEvent("submit", jsonEditorSave);
						} else {
							form.addEventListener("submit", jsonEditorSave);
						}
						
				}
				
				function jsonEditorSave(e){
					for (key in jsonEditors){
						var el = document.getElementById(key);
						if (jsonEditors[key].getText() != '$init_value'){
							el.innerHTML = jsonEditors[key].getText();
						}
					}
				}
				</script>

OUT;
                $e->output($output);
                break;
                
        default:        
                return; // stop here - this is very important. 
                break; 
}
