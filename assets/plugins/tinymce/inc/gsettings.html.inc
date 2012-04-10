<style type="text/css">
textarea.mce {width:95%;height:40px;display:block;}
</style>
<table id="editorRow_TinyMCE" class="settings">
  <tr class="row1" style="display: [+display+];">
    <th colspan="2" style="color:#707070; background-color:#eeeeee"><h4 style="margin:3px;">[+mce_settings+]</h4></th>
  </tr>
  <tr class="row1" style="display: [+display+];">
    <th>[+mce_editor_theme_title+]</th>
    <td>
    <select name="tinymce_editor_theme" onchange="documentDirty=true;">
    [+theme_options+]
	</select>
    <div>[+mce_editor_theme_message+]</div>
	</td>
  </tr>
  <tr class="row1" style="display: [+display+];">
    <th>[+mce_editor_skin_title+]</th>
    <td>
    <select name="mce_editor_skin" onchange="documentDirty=true;">
    [+skin_options+]
	</select>
    <div>[+mce_editor_skin_message+]</div>
	</td>
  </tr>
  <tr class="row1" style="display: [+display+];">
    <th>[+mce_editor_entermode_title+]</th>
    <td>
    [+entermode_options+]
    <div>[+mce_editor_entermode_message+]</div>
	</td>
  </tr>
  <tr class="row1" style="display: [+display+];">
	<th>[+mce_editor_custom_plugins_title+]</th>
	<td>
		<textarea onchange="documentDirty=true;" type="text" class="inputBox mce" name="tinymce_custom_plugins" />[+custom_plugins+]</textarea>
		<div>[+mce_editor_custom_plugins_message+]</div>
	</td>
  </tr>
  <tr class="row1" style="display: [+display+];">
	<th>[+mce_editor_custom_buttons_title+]</th>
	<td>
	Row 1: <textarea onchange="documentDirty=true;" class="inputBox mce" name="tinymce_custom_buttons1" />[+custom_buttons1+]</textarea>
	Row 2: <textarea onchange="documentDirty=true;" class="inputBox mce" name="tinymce_custom_buttons2" />[+custom_buttons2+]</textarea>
	Row 3: <textarea onchange="documentDirty=true;" class="inputBox mce" name="tinymce_custom_buttons3" />[+custom_buttons3+]</textarea>
	Row 4: <textarea onchange="documentDirty=true;" class="inputBox mce" name="tinymce_custom_buttons4" />[+custom_buttons4+]</textarea>
	<div>[+mce_editor_custom_buttons_message+]</div>
	</td>
  </tr>
  <tr class="row1" style="display: [+display+];">
	<th>[+mce_editor_css_selectors_title+]</th>
	<td><input onchange="documentDirty=true;" type="text" class="inputBox" style="width: 300px;" name="tinymce_css_selectors" value="[+css_selectors+]" />
	<div>[+mce_editor_css_selectors_message+]</div>
	</td>
  </tr>
</table>
