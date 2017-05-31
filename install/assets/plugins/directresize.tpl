//<?php
/**
 * directResize
 *
 * Fully customizable plugin with a number of functions like: automatic thumbnails creation, watermarking (text or transparent PNG), using config files to set plugin parameters, big images openning with AJAX (lightbox, slimbox, highslide...), fully templateable output, thumbnails for WYSIWYG-editor etc...
 *
 * @category    plugin
 * @version     0.9.0
 * @author		Metaller
 * @author		PATRIOT
 * @internal    @events OnWebPagePrerender,OnCacheUpdate,OnBeforeDocFormSave,OnDocFormPrerender
 * @internal    @properties &config=Configuration;text;highslide;highslide &clearCache=Clear cache;list;0,1,2;2 &excludeDocs=Do not run on documents (,);string;
 * @internal    @installset base
 */

require MODX_BASE_PATH.'assets/plugins/directresize/directResize.plugin.php';