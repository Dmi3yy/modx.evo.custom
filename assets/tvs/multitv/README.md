multiTV custom template variable
================================================================================

Transform template variables into a sortable multi item list for the MODX Evolution content management framework

Events example:

![Eventlist example](https://github.com/Jako/multiTV/blob/master/multitv.events.png?raw=true)

Images example:

![Images example](https://github.com/Jako/multiTV/blob/master/multitv.images.png?raw=true)

Links example (with editing layer):

![Images example](https://github.com/Jako/multiTV/blob/develop/multitv.links.png?raw=true)
![Images example](https://github.com/Jako/multiTV/blob/develop/multitv.links_edit.png?raw=true)

Part 1: custom template variable
================================================================================

Features:
--------------------------------------------------------------------------------
With this code a MODX Evo template variable could be transformed into a sortable multi item list
  
Installation:
--------------------------------------------------------------------------------
1. Upload all files into the new folder *assets/tvs/multitv*
2. Create a new template variable with imput type *custom input* (if you name this template variable *multidemo* it will use the multidemo config file)
3. Insert the following code into the *input option values* 
```
@INCLUDE/assets/tvs/multitv/multitv.customtv.php
```
4. If you want to modify the multiTV with ManagerManager **before MODX version 1.0.9** you have to patch the file `mm.inc.php` and insert
```
case 'custom_tv':
```
in line 136 just before the line 
```
$t = 'textarea';
```
(Note 4) 
5. If you want to use multiTV with YAMS you have to patch yams.plugin.inc.php according to the instructions on https://github.com/Jako/multiTV/issues/9#issuecomment-6992127 
6. If you are updating from 1.4.10 and below you could install the updateTV snippet (see part 4) and modify the data in your multiTVs to the new format. Since the custom tv and the snippet code supports the old and new format, this is only nessesary, if you want to add/remove columns in your multiTVs or if you want to sort the output by a column. 
7. If you want to use PHx with multiTV you have to modify the PHx plugin code a bit:

```
if (!class_exists('PHxParser')) {
    include MODX_BASE_PATH . "assets/plugins/phx/phx.parser.class.inc.php";
}

$e = &$modx->Event;
switch($e->name) {
	case 'OnParseDocument':
		$PHx = new PHxParser($phxdebug,$phxmaxpass);
		$PHx->OnParseDocument();
		break;
}
```

Options:
--------------------------------------------------------------------------------
All options for a custom template variable are set in a config file in the folder *configs* with the same name as the template variable (otherwise the default config is used) and *.config.inc.php* as extension

The display of the input fields in the multi field list could be set in `$settings['display']` to *horizontal* (events example), *vertical* (images example), *datatable* (links or multicontent example) or *single*. A multiTV with single display configuration contains only one list element.

The input fields of one list element could be defined in `$settings['fields']`. This variable contains an array of fieldnames and each fieldname contains an array of field properties.

Property | Description | Default
-------- | ----------- | -------
caption | caption (horizontal) or label (vertical) for the input | -
type | type of the input (could be set to all MODX input types - without url and richtext - and thumb for thumbnail display of image tvs - see images config for thumb) | text
elements | could be set according to the input option values of a normal MODX template variable i.e. for a dropdown with all documents in the MODX root: ``@SELECT `pagetitle`, `id` FROM `modx_site_content` WHERE parent = 0 ORDER BY `menuindex` ASC`` | -
default | default value for the input. This value could contain calculated parts. There are two placeholders available: `{i}` contains an autoincremented index, `{alias}` contains the alias of the edited document. | -
thumbof | name of an image input. a thumbnail of the selected image will be rendered into this area | -
width | the width of the input (only used if the display of the list element is horizontal) | 100

* Supported MODX input types: text, rawtext, email, number, textareamini, textarea, rawtextarea, htmlarea, date, dropdown, listbox, listbox-multiple, checkbox, option, image, file

In datatable mode the columns for the datatable could be defined in `$settings['columns']`. This variable contains an array of column settings. Each column setting contains an array of properties. If a property is not set, the field property in `$settings['fields']` is used.

Property | Description | Default
-------- | ----------- | -------
fieldname | **(required)** fieldname that is displayed in this column | -
caption | the caption of the column | caption for this field in `$settings['fields']`
width | the width of the column | width for this field in `$settings['fields']`
render | the column will be rendered with this PHx capable string  | -

In datatable mode the content of the editing layer could be defined in `$settings['form']`. This variable contains an array of form tab settings. Each form tab setting contains an array of field properties in the value of the content key. If a field property is not set, the field property in `$settings['fields']` is used.

Property | Description | Default
-------- | ----------- | -------
caption | caption for the input | caption for this field in `$settings['fields']`

* In the editing layer the MODX input type richtext is possible.

The default output templates for the snippet could be defined in `$settings['templates']`. 

Property | Description | Default
---- | ----------- | -------
rowTpl | default row template chunk for the snippet output. Could be changed in snippet call. See snippet description for placeholders | -
outerTpl | default outer template chunk for the snippet output. Could be changed in snippet call. See snippet description for placeholders | -

The other configurations for one multiTV could be defined in `$settings['configuration']`

Property | Description | Default
---- | ----------- | -------
enablePaste | The multiTV could contain *paste table data* link that displays a paste box. In this box you could paste Word/HTML table clipboard data, Google Docs table clipboard data and csv data. | TRUE 
enableClear | The multiTV could contain *clear all* link that clears the content of the multiTV | TRUE 
csvseparator | column separator for csv clipboard table data. The csv clipboard table data should contain a new line for each row. | , 
radioTabs | The tabs in the datatable editing layer are displayed as radio buttons. The button state is saved in the field `fieldTab`.

See the *multidemo* config for all usable vertical settings and the *multicontent* config for all usable datatable settings.

Part 2: multiTV Snippet
================================================================================

Installation:
--------------------------------------------------------------------------------
Create a new snippet called multiTV with the following snippet code

```
<?php
return include(MODX_BASE_PATH.'assets/tvs/multitv/multitv.snippet.php');
?>
```

Usage:
--------------------------------------------------------------------------------
Call the snippet like this (most expample parameters are using the default values in this example call and could be removed from the call – parameter tvName is required)

```
[!multiTV?
&tvName=`yourMultiTVname`
&docid=`[*id*]`
&tplConfig=``
&outerTpl=`@CODE:<ul>((wrapper))</ul>`
&rowTpl=`@CODE:<li>((event)), ((location)), ((price))</li>`
&display=`5`
&offset=`0`
&rows=`all`
&toPlaceholder=``
&randomize=`0`
&orderBy=``
&published=`1`
&emptyOutput=`1`
&outputSeparator=``
!]
```

Parameters:
--------------------------------------------------------------------------------

Name | Description | Default value
---- | ----------- | -------------
tvName | **(required)** name of the template variable that contains the multiTV (the column names of the mulitTV are received from the config file) | -
docid | document id where the custom tv is retreived from (i.e. if the multiTV Snippet is called in a Ditto template) | current document id
tplConfig | array key in the config file that contains the output templates configuration (will be prefixed with `templates`) | ''
outerTpl | outer template: chunkname, filename (value starts with `@FILE`) or code (value starts with `@CODE` - placeholders have to be masked by `((` and `))`. (Note 3) | `@CODE:<select name="$tvName">[+wrapper+]</select>` or custom template in template variable config file
rowTpl | row template: chunkname, filename (value starts with `@FILE`) or code (value starts with `@CODE` - placeholders have to be masked by `((` and `))`. (Note 3) | `@CODE:<option value="[+value+]">[+key+]</option>` or custom template in template variable config file
display | count of rows that are displayed, `all` for all rows | 5
offset | count of rows from start that are not displayed | 0
rows | comma separated list of row numbers (or all rows) that should be displayed | all
randomize | random order of displayed rows | 0
orderBy | column name and order direction (direction defaults to asc) to sort the output | -
toPlaceholder | the snippet output is assigned to a placeholder named as the parameter value (i.e. [+myPlaceholder+]), single items are assigned to placeholders named as the parameter value followed by the row number (i.e. [+myPlaceholder.1+]). Normal snippet output is suppressed. (Note 2) | -
published | display only multiTVs of published (1), unpublished (0) or both (2) kind of documents | 1
emptyOutput | return empty string if the multiTV is empty, otherwise return outer template | 1
outputSeparator | string inserted between two row templates | empty

The default templates for outer template and row template could be defined in the config file for the custom template variable. These custom definitions could be overwritten by *rowTpl* and *outerTpl* in snippet call. Both template chunks are parsed by PHx (chunkie class).

Placeholder rowTpl:
--------------------------------------------------------------------------------

Name | Description
---- | -----------
"fieldname" | each fieldname defined in config file could be used
iteration | contains the iteration of the current multiTV element
row.number | contains the row number of the current multiTV element
row.class | 'first' for first displayed row, 'last' for last displayed row
row.total | contains the count of all displayable rows 
docid | value of docid parameter or current document id

Placeholder outerTpl:
--------------------------------------------------------------------------------
Name | Description
---- | -----------
wrapper | contains the output of all rows
rows.offset | contains the count of rows from start that are not displayed 
rows.total | contains the count of all displayable rows 
docid | value of docid parameter or current document id

Part 3: PHx modifier
================================================================================
Since the JSON string in multiTV starts with `[[` and ends with `]]` (Note 1), you *can't* check if the multiTV is empty by i.e. ```[*multittvname:ne=``:then=`not empty`*]```. 

But you could to use the PHx modifier in the folder `phx-modifier` in that case. Move the two files to `assets/plugins/phx/modifiers` and call it like this ``[+phx:multitvisempty=`tvname|docid`:then=`xxx`:else=`yyy`+]`` or like this ``[+phx:multitvisnotempty=`tvname|docid`:then=`xxx`:else=`yyy`+]``. If the docid is not set it defaults to current document.

Part 4: updateTV Snippet
================================================================================

Installation:
--------------------------------------------------------------------------------
Create a new snippet called updateTV with the following snippet code

```
<?php
return include(MODX_BASE_PATH.'assets/tvs/multitv/updatetv.snippet.php');
?>
```

Usage:
--------------------------------------------------------------------------------
Version 1.4.11 of multiTV uses a new data format (the column names are saved as key with each value). The custom tv and the snippet code supports the old and new format, so you don't have to update your multiTVs. It is only nessesary, if you want to add/remove columns in your multiTVs. Call the snippet on one (temporary) MODX document like this:

```
[!updateTV?
&tvNames=`yourMultiTVname1,yourMultiTVname2`
!]
```

Parameters:
--------------------------------------------------------------------------------

Name | Description | Default value
---- | ----------- | -------------
tvNames | **(required)** comma separated list of template variable names that contain multiTV data | -


Notes:
--------------------------------------------------------------------------------
1. The JSON string the multitv is converted to starts with `[[` and ends with `]]` so the MODX parser thinks it contains a snippet and you can't place the template variable directly in the template.
2. If the snippet output is assigned to placeholder and PHx is installed, the page should be set to uncached and the Snippet should be called cached. Otherwise PHx will 'steal' the placeholders before the Snippet could fill them.
3. MODX does not like `=`, `?` and `&` in snippet parameters. If the template code has to use those signs, put the template code in a chunk or change the default templates in the config file.
4. ManagerManager expects a custom tv field to be an input tag. Because of single and double quote issues the field containing the multiTV value is a textarea.
