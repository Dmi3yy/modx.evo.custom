<div id="content">

<form action="[+action+]" method="post">
<input type="hidden" name="edit" value="[+id+]" />

<p class="thumbPreview">
	<img src="[+image+]" alt="[+filename+]" title="[+filename+]" />
</p>

<label for="title">[+lang.title+]:</label> <div class="field"><input type="text" name="title" id="title" value="[+title+]" size="30" /></div>
<label for="description">[+lang.description+]:</label> <div class="field"><textarea name="description" id="description" rows="5" cols="35">[+description+]</textarea></div>
<label for="title">[+lang.keywords+]:</label> <div class="field"><input type="text" name="keywords" id="keywords" value="[+keywords+]" size="30" />[+keyword_tagList+]</div>
<div class="submit">
	<input type="submit" value="[+lang.update+]" id="cmdsave" class="awesome" name="cmdsave" />
</div>
<div class="imageupdater">
    <label for="newimage">[+lang.update_image+]</label><br />
    <input id="newimage" name="newimage" type="file" /> 
    <a id="newimageupload" href="#">[+lang.upload_file+]</a></div>
</div>
</form>
