<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
/**
 * $Id: plugin.php 25 2010-03-24 20:34:24Z stefan $
 *
 * Manage TV assignments on template-page
 *
 * @events
 *
 *  OnTempFormPrerender
 *  OnTempFormRender
 *  OnTempFormDelete
 *  OnTempFormSave
 *
 * @version 0.3.4
 *
 * @TODO cleaner way to collect and store the tmplvars and there assignments/rank
 *
 * fixed bugs
 *      # Sorting tvs see: http://modxcms.com/forums/index.php/topic,42098.msg317713.html#msg317713 
 *
 * fixed bugs 0.3.3:
 *      # Content Encoding Error:
 *        if output compression is set via htaccess (php_flag zlib.output_compression ON)
 *        An error occur on the template page
 *
 *        "ob_end_clean() [ref.outcontrol]: failed to delete buffer zlib output compression."
 *
 */

global $_lang, $_style, $_plugin_params;

$_plugin_params = array(
    'name'          => 'tvs_on_template',
    'path'          => realpath( dirname( __FILE__ ) ) . '/',
    'rel_path'      => '../assets/plugins/' . basename( dirname( __FILE__ ) ) . '/'
);

$e =& $modx->event;
switch( $e->name )
{
    case 'OnTempFormPrerender':
        /**
         * bug in 1.0.0
         * @see http://svn.modxcms.com/jira/browse/MODX-1114
         * 
         * On updateing a template, its possible to change the name to an 
         * already existing name.
         * 
         * @TODO compare templatenames with ajax request
         * 
         * Get all templatenames for comparing with userinput using js
         * var templatenames =[ $templates_arraystring ];
         */
        $res = $modx->db->select(
            '`templatename`, `id`',
            $modx->getFullTableName('site_templates')
        );

        $templates_arraystring = '';
        if( $modx->db->getRecordCount( $res ) !== 0 )
        {
            while( $_template = $this->db->getRow( $res ) )
            {
                // exclude current Template
                if( (int)$_template['id'] === (int)$e->params['id'] ) 
                {
                    continue;
                }
                $templates_arraystring .= "'". strtolower($_template['templatename']) . "',";  
            }
            $templates_arraystring = substr($templates_arraystring, 0,-1 );
        }

        include $_plugin_params['path'] . 'skin/' . strtolower( $e->name ) . '.phtml';
        $e->output( ob_get_clean() );
        ob_start();
        return;  
        break;
    case 'OnTempFormRender':

        /**
         * Get all categories
         * 
         * check Parameter use_cm for the column "rank"
         * 
         */
        $_rank = '';
        if( $e->params['use_cm'] === 'true' )
        {
            $_rank = '`rank`, ';
        }
        $categories = array();
        $res = $modx->db->select( 
            '*', 
            $modx->getFullTableName('categories'),
            '1',
            $_rank . '`category`'
        );

        while( $category = $this->db->getRow( $res ) )
        {
            $categories[$category['id']] = $category;
        }

        // create "uncategorized" category
        $categories[0] = array(
            'id'       => 0,
            'category' => $_lang['no_category'],
            'rank'     => 0
        );

        /**
         * Get all template-vars
         */
        $tmplvars = array();
        $res = $modx->db->select(
            '`id`, `name`, `caption`, `description`, `category`, `locked`, `rank`',
            $modx->getFullTableName('site_tmplvars'),
            '1',
            '`rank`'
        );

        while( $tmplvar = $this->db->getRow( $res ) )
        {
            /**
             *  skip locked tmpl-vars
             *  @TODO remove them here or show "(locked)" in list?

            if( (int)$tmplvar['locked'] === 1 
                && (int)$_SESSION['mgrRole'] !== 1 )
            {
                continue;
            }
            */

            $tmplvar['tpl_rank'] = 0;
            
            if( in_array( $tmplvar['category'], array_keys( $categories ) ) )
            {
                $categories[$tmplvar['category']]['elements'][$tmplvar['id']] = $tmplvar;  
            }
            else
            {
                $categories[0]['elements'][$tmplvar['id']] = $tmplvar;
            }
            $tmplvars[$tmplvar['id']] = $tmplvar;
        }

        /**
         * Delete Categories without assigned elements
         * 
         * @TODO find a better solution
         */
        foreach( $categories as $category )
        {
            if( !isset( $category['elements'] )  )
            {
                unset( $categories[$category['id']] );
            }
        }

        $assigned_tmplvars = array();
        switch( (int)$modx->manager->action )
        {
            case 16 :
                /**
                 * Updateing a template, get the assigned templatevars
                 */
                $res = $modx->db->select( 
                    '`tmplvarid`, `rank` AS `tpl_rank`',
                    $modx->getFullTableName('site_tmplvar_templates'),
                    "`templateid` = '" . $e->params['id']  . "'",
                    '`rank`'
                );

                while( $tmplvar = $this->db->getRow( $res ) )
                {
                    /**
                     * overwrite tpl_rank on assigned tmplvars/elements
                     * @TODO find a better solution
                     */ 
                    $_tmplvarid = $tmplvar['tmplvarid'];
                    $_catid     = (int)$tmplvars[$_tmplvarid]['category'];
                    $categories[$_catid]['elements'][$_tmplvarid]['assigned'] = 1;
                    $categories[$_catid]['elements'][$_tmplvarid]['tpl_rank'] = $tmplvar['tpl_rank'];

                    $assigned_tmplvars[$_tmplvarid] = $tmplvar;
                }
                
                /**
                 * sort the elements by the "templvars_template" rank
                 * @TODO find a better solution
                 */
                foreach( $categories as $category_id => $data )
                {
                    $_sort = array();
                    foreach( $data['elements'] as $_tmplvar )
                    {
                        $_sort[] = $_tmplvar['tpl_rank'];
                    }
                    array_multisort( $_sort,  $data['elements'] );
                    $categories[$category_id]['elements'] = $data['elements'];
                }      
                include $_plugin_params['path'] . 'skin/' . strtolower( $e->name ) . '_upd.phtml';
                break;
            case 19 :
            case 20 :
                include $_plugin_params['path'] . 'skin/' . strtolower( $e->name ) . '_new.phtml';
                break;
        }
        $e->output( ob_get_clean() );
        ob_start();
        return;
        break;
    case 'OnTempFormSave':
        if( $_tmplvars = $_POST['plugin']['tt']['tplvars'] )
        {
            if( $e->params['mode'] === 'upd' )
            {
                /**
                 * Delete all assignments...
                 * its faster than check every assignment
                 */   
                $modx->db->delete(
                    $modx->getFullTableName('site_tmplvar_templates'),
                    "`templateid` ='" . $e->params['id'] . "'"
                );
            }
            
            if( $ids = $_tmplvars['id'] )
            {
                /**
                 * create the new assignments
                 */
                foreach( $ids as $tmplvar_id )
                {
                    $_insert = array(
                        'tmplvarid'  => (int)$tmplvar_id,
                        'templateid' => (int)$e->params['id'],
                        'rank'       => (int)$_tmplvars['rank'][$tmplvar_id]
                    );
                    $modx->db->insert(
                        $_insert,
                        $modx->getFullTableName('site_tmplvar_templates')
                    );
                }
            }

            // reset the session container    
            unset( $_POST['plugin']['tt'] );
        }
        break ;
    case 'OnTempFormDelete':
        /**
         * bug in 1.0.0 
         * @see http://svn.modxcms.com/jira/browse/MODX-1110
         * 
         * After deleting a template, the assignments to tvs are still exists
         */
        $modx->db->delete(
            $modx->getFullTableName('site_tmplvar_templates'),
            "`templateid` = '" . $e->params['id'] ."'"
        ); 
        break;
    default:
        return;
        break;
}
return;