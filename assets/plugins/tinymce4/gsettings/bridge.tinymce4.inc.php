<?php
/* TinyMCE4 for Modx Evolution
   Base: v4.3.4
*/

// @todo: check all needed themes
// @todo: add https://www.tinymce.com/docs/configure/content-filtering/#invalid_styles
// @todo: add https://www.tinymce.com/docs/configure/content-filtering/#remove_trailing_brs
// @todo: youtube-plugin is commercial? -> http://www.cfcms.nl/tinymce-youtube/index.html

// Editor-Settings
$editorLabel    = 'TinyMCE 4';          // Name displayed in Modx-Dropdowns - No HTML!
$skinsDirectory = 'tinymce/skins';      // Relative to plugin-dir
$editorVersion  = '4.3.7';              // Version of TinyMCE4-Library
$editorLogo     = 'tinymce/logo.png';   // Optional Image displayed in Modx-settings


// Dynamic translation of Modx-settings to editor-settings
$bridgeParams = array(

    // https://www.tinymce.com/docs/demo/url-conversion/
    'url_setup' => function () {
        global $modx;

        // $pathSetup = array( relative_urls, remove_script_host, convert_urls )

        switch ($this->pluginParams['pathOptions']) {
            case 'Site config':
            case 'siteconfig':
                if ($modx->config['strip_image_paths'] == 1) {
                    $pathSetup = array( true, true, true );
                } else {
                    $pathSetup = array( false, false, true );
                }
                break;
            case 'Root relative':
            case 'docrelative':
                $pathSetup = array( true, true, true );
                break;
            case 'Absolute path':
            case 'rootrelative':
                $pathSetup = array( false, true, true );
                break;
            case 'URL':
            case 'fullpathurl':
                $pathSetup = array( false, false, true );
                break;
            case 'No convert':
            default:
                $pathSetup = array( true, true, false );
        }
        $this->set('relative_urls', $pathSetup[0], 'bool');
        $this->set('remove_script_host', $pathSetup[1], 'bool');
        $this->set('convert_urls', $pathSetup[2], 'bool');
    },

    // https://www.tinymce.com/docs/configure/content-formatting/#style_formats
    'style_formats' => function () {
        $sfArray[] = array('title'=>'Paragraph','format'=>'p');
        $sfArray[] = array('title'=>'Header 1','format'=>'h1');
        $sfArray[] = array('title'=>'Header 2','format'=>'h2');
        $sfArray[] = array('title'=>'Header 3','format'=>'h3');
        $sfArray[] = array('title'=>'Header 4','format'=>'h4');
        $sfArray[] = array('title'=>'Header 5','format'=>'h5');
        $sfArray[] = array('title'=>'Header 6','format'=>'h6');
        $sfArray[] = array('title'=>'Div','format'=>'div');
        $sfArray[] = array('title'=>'Pre','format'=>'pre');

        // Set in plugin-configuration, format: Title,cssClass|Title2,cssClass
        if(isset($this->pluginParams['styleFormats'])) {
            $styles_formats = explode('|', $this->pluginParams['styleFormats']);
            foreach ($styles_formats as $val) {
                $style = explode(',', $val);
                $sfArray[] = array('title'=>$style['0'], 'selector'=>'a,strong,em,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,tr,span,img', 'classes'=>$style['1']);
            }
        }
        return $sfArray;    // return NULL would avoid bridging this parameter
    },

    // https://www.tinymce.com/docs/configure/editor-appearance/#resize
    'advanced_resizing' => function () {
        switch($this->pluginParams['resizing']) {
            case 'true':
                $this->set('resize', 'both', 'string');
                break;
            default:
                $this->set('resize', true, 'bool'); // Only up/down
        };
    },

    // https://www.tinymce.com/docs/configure/content-filtering/#forced_root_block
    'forced_root_block' => function () {
        switch($this->modxParams['entermode']) {
            case 'br':
                $this->set('forced_root_block', false, 'bool');
                break;
            default:
                $this->set('forced_root_block', 'p', 'string');
        };
        return NULL;    // Important
    },

    // @todo: Remove? RTL will be set by language-pack -> http://www.tinymce.com/forum/viewtopic.php?id=32748
    'contentsLangDirection' => function () {
        if( $this->pluginParams['webAlign'] == 'rtl') {
            $this->set('directionality', 'rtl', 'string');
            $this->appendInitOnce('<style>.mce-toolbar .mce-last { float: right; }</style>');   // Force editor by CSS ?
        };
    },

    // disabled_buttons-param is deprecated - bridge replaces old TinyMCE v3-param
    'disabledButtons' => function () {
        if( !empty( $this->pluginParams['disabledButtons'])) {
            $buttons = explode(' ', $this->pluginParams['disabledButtons']);
            if(isset($this->pluginParams['toolbar1'])) $this->pluginParams['toolbar1'] = str_replace( $buttons, '', $this->pluginParams['toolbar1'] );
            if(isset($this->pluginParams['toolbar2'])) $this->pluginParams['toolbar2'] = str_replace( $buttons, '', $this->pluginParams['toolbar2'] );
            if(isset($this->pluginParams['toolbar3'])) $this->pluginParams['toolbar3'] = str_replace( $buttons, '', $this->pluginParams['toolbar3'] );
            if(isset($this->pluginParams['toolbar4'])) $this->pluginParams['toolbar4'] = str_replace( $buttons, '', $this->pluginParams['toolbar4'] );
        };
    },

    // Sets selectorPrefix for Manager or Frontendediting
    'selectorPrefix' => function () {
        global $modx;

        $inlineMode = $this->determineValue('inline') == true && $this->pluginParams['inlineMode'] == 'enabled' ? true : false;

        // Manager Mode
        if(!$inlineMode) {
            $this->setPlaceholder('selectorPrefix', '#');   // Selectors = #ta, #tv9

        // Prepare Inline-Magic
        } else {
            $this->setPlaceholder('selectorPrefix', '.');   // Single selector = .editable

            $this->force('setup',          NULL);               // Remove from parameters for Frontend
            $this->force('save_onsavecallback', 'function () {
            triggerSave();
        }', 'object');

            // Prepare save-button
            $this->appendInitOnce('
            <style>
              #action-save { position:fixed;top:0px;left:0px; color: #000000; background-color: #F0F0F0; text-shadow: -1px 1px #aaaaaa; display: inline-block; padding: 15px 30px !important; font-size: 24px; font-family: sans-serif; line-height: 1.8; appearance: none; box-shadow: none; border-radius: 0; border: none; cursor:pointer; }
              #action-save:hover { color: #222222; outline: none; background-color: #E3E3E3; }
            </style>
            <button id="action-save" class="button" title="Save Ressource">SAVE</button>
            <script>
            // Remove every attribute starting with data-mce-
            function tinymce_clean_html_before_save( _container ) {
             $(_container).find("*").each(function(){
              var elem = $(this);
              var attributes = $(this).get(0).attributes;
              $(attributes).each(function(index){
               var attribute = attributes[index].name;
               if( attribute.substring(0, 9) == "data-mce-" ){
                elem.removeAttr(attribute);
               }
              });
             });
             return _container;
            }

            $("#action-save").on("click", function() { triggerSave(); });
            function triggerSave() {

            [+dataObject+]

            var saving = $.post( "' . $this->pluginParams['base_url'] . 'connector.tinymce4.saveProcessor.php", data );

            saving.done(function( data ) {
                if( data == ' . $modx->documentIdentifier . ' ) {
                    $("#action-save").css("color","#00ff00");
                    setTimeout(function(){ $("#action-save").css("color","#000000") }, 3000);
                    // Force all instances to not dirty state
                    for (var key in window.tinymce.editors) {
                        tinymce.get(key).setDirty(false);
                    }
                } else {
                    $("#action-save").css("color","#ff0000");
                    alert( data );  // Show (PHP-)Errors for debug
                }
            });
    }
</script>
');
            // Prepare dataObject for submitting changes
            $editableIds = explode(',', $this->pluginParams['editableIds']);
            if(!empty($editableIds)) {
                $dataEls = array();
                foreach ($editableIds as $idStr) {
                    $editable = explode('->', $idStr);
                    $modxPh = trim($editable[0]);
                    $cssId = trim($editable[1]);

                    $dataEls[] = "'{$modxPh}': tinymce_clean_html_before_save( $('{$cssId}').html() )";
                }
                $dataEls = join(",\n                ", $dataEls);

                $this->setPlaceholder('dataObject', "
                var data = {
                    'pluginName':'{$this->pluginParams['pluginName']}',
                    'rid':{$modx->documentIdentifier},
                    {$dataEls}
                };");
            }
        }
        return NULL;
    },


    // Handles customSetting "blockFormats" -
    // https://www.tinymce.com/docs/configure/content-formatting/#block_formats
    'block_formats' => function () {
        // Format: Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3
        // params-string could be bridged/modified here from Modx-config to Editor-config
        // Right now its enough to return the string
        return $this->modxParams['blockFormats'];
    }



);

// Custom settings to show below Modx- / user-configuration
$gSettingsCustom = array(

    'css_selectors' => NULL,         // Hides "CSS Selectors" from settings

    // 'blockFormats' will be available as $this->modxParams['blockFormats']
    // will be handled by $this->bridgeParams[blockFormats]()
    'blockFormats' => array(
        'title' => 'blockFormats_title',
        'configTpl' => '<textarea class="inputBox mce" name="[+name+]">[+[+editorKey+]_blockFormats+]</textarea>',
        'message' => 'blockFormats_message'
    )
);

// For Modx- and user-configuration
$gSettingsDefaultValues = array(
    'entermode' => 'p',
    'element_format' => 'xhtml',
    'schema' => 'html5',
    'blockFormats'=>'Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3',
    'custom_plugins'=>'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen spellchecker insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor codesample colorpicker textpattern imagetools paste modxlink youtube',
    'custom_buttons1'=>'undo redo | cut copy paste | searchreplace | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | styleselect',
    'custom_buttons2'=>'link unlink anchor image media codesample table | hr removeformat | subscript superscript charmap | nonbreaking | visualchars visualblocks print preview fullscreen code'
);

// Add translation for monolingual custom-messages with $this->setLang( key, string, overwriteExisting=false )
$this->setLang('editor_custom_buttons1_msg', '<div style="width:70vw;word-wrap:break-word;overflow-wrap:break-word;">[+default+]<i>'.$defaultValues['custom_buttons1'].'</i></div>' );
$this->setLang('editor_custom_buttons2_msg', '<div style="width:70vw;word-wrap:break-word;overflow-wrap:break-word;">[+default+]<i>'.$defaultValues['custom_buttons2'].'</i></div>' );
$this->setLang('editor_css_selectors_schema', 'Title==Tag==CSS-Class');
$this->setLang('editor_css_selectors_example', 'Mono==pre==mono||Small Text==span==small');
$this->setLang('editor_css_selectors_separator', '||');