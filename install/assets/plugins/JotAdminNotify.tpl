//<?php
/**
 * JotAdminNotify
 * 
 * На главной странице админки отображается количество неопубликованных коментариев снипета JotX
 *
 * @category 	plugin
 * @version 	0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Karpenko Alexey (tonatos@gmail.com)
 * @internal	@properties
 * @internal	@events OnManagerWelcomePrerender
 * @internal    @installset base
 * @internal    @legacy_names JotAdminNotify
 * @internal    @disabled 1
 */
 
$output = "";
$e = &$modx->Event;
if($e->name == 'OnManagerWelcomePrerender'){

    $table = $modx->getFullTableName('jot_content');
    $sitecontent = $modx->getFullTableName('site_content');
    $rs = $modx->db->query("SELECT count(jc.uparent) as count, jc.uparent, sc.pagetitle FROM $table jc left join $sitecontent sc on sc.id = uparent where jc.published=0 group by jc.uparent");
    while($row=$modx->db->GetRow($rs)){
        if ($row['count']>0){
            $id = $row['uparent'];
            $count = $row['count'];
            $url = $modx->makeUrl($id);
            $output .= "<li><a href='$url' target='_blank'>".$row['pagetitle'].": $count</a></li>";
        }
    }
    
    if (!empty($output)){
        $output = '<div class="sectionHeader" style="color:red">Имеются неопубликованные комментарии</div><div class="sectionBody"><ul>'.$output.'</ul></div>';
    }
    $e->output($output);
}
