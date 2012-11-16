//<?php
/**
 * Strict URLs 
 * 
 * Enforces the use of strict URLs to prevent duplicate content.
 *
 * @category    plugin
 * @version     1.0.1
 * @author		By Jeremy Luebke @ www.xuru.com
 * @internal    @properties &editDocLinks=Edit document links;int;1 &makeFolders=Rewrite containers as folders;int;1 &emptyFolders=Check for empty container when rewriting;int;0 &override=Enable manual overrides;int;1 &overrideTV=Override TV name;string;seoOverride
 * @internal    @events OnWebPageInit,OnWebPagePrerender
 * @internal    @modx_category Manager and Admin
 */

require MODX_BASE_PATH.'assets/plugins/seostricturls/plugin.seostricturls.php';