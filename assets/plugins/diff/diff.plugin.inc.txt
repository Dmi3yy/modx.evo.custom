//<?
/**************************************/
/** Diff plugin for Modx Evo
*
* @version 2.6
* @author Borisov Evgeniy aka Agel Nash (agel-nash@xaker.ru)
* @date 20.07.2012
*
* @category plugin
* @internal @event OnTempFormDelete,OnTempFormSave,OnTempFormRender,OnSnipFormDelete,OnSnipFormSave,OnSnipFormRender,OnPluginFormDelete,OnPluginFormSave,OnPluginFormRender,OnModFormDelete,OnModFormSave,OnModFormRender,OnChunkFormDelete,OnChunkFormSave,OnChunkFormRender,OnDocFormDelete,OnDocFormRender,OnDocFormSave
* @internal @properties &idBlock=ID блока;text;Version &folderPlugin=Папка плагина;text;diff &which_jquery=Подключить jQuery;list;Не подключать,/assets/js/,google code,custom url;/assets/js/ &js_src_type=Свой url к библиотеке jQuery;text; &jqname=Имя Jquery переменной в noConflict;text;j &ignoredChunk=ID игнорируемых чанков;text; &ignoredSnippet=ID игнорируемых сниппетов;text; &ignoredPlugin=ID игнорируемых плагинов;text; &ignoredDoc=ID игнорируемых документов;text; &ignoredModule=ID игнорируемых модулей;text; &ignoredTPL=ID игнорируемых шаблонов;text; &countTPL=Кол-во версий одного шаблона;text; &countChunk=Кол-во версий одного чанка;text; &countPlugin=Кол-во версий одного плагина;text; &countModule=Кол-во версий одного модуля;text; &countSnippet=Кол-во версий одного сниппета;text; &countDoc=Кол-во версий одного документа;text;
* @internal @modx_category Manager and Admin
*
*/
/*************************************/
if(file_exists($modx->config['base_path'].'assets/plugins/'.$folderPlugin.'/lang/'.$modx->config['manager_language'].'.inc.php')){
  include($modx->config['base_path'].'assets/plugins/'.$folderPlugin.'/lang/'.$modx->config['manager_language'].'.inc.php');
}else{
  include($modx->config['base_path'].'assets/plugins/'.$folderPlugin.'/lang/english.inc.php');
}
include($modx->config['base_path'].'assets/plugins/'.$folderPlugin.'/version.class.php');
switch($modx->Event->name){
	/** Template */
	case 'OnTempFormDelete':{
		$Diff=new ElementVer($modx,'template',$folderPlugin);
		if($Diff->ignored($ignoredTPL)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnTempFormSave':{
		$Diff=new ElementVer($modx,'template',$folderPlugin);
		if($Diff->ignored($ignoredTPL)){
			$Diff->countVer=(int)$countTPL;
			$Diff->save($modx->Event->params['id'],'post');
		}
		break;
	}
	case 'OnTempFormRender':{
		$Diff=new ElementVer($modx,'template',$folderPlugin);
		if($Diff->ignored($ignoredTPL)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
	
	/** Snippet */
	case 'OnSnipFormDelete':{
		$Diff=new ElementVer($modx,'snippet',$folderPlugin);
		if($Diff->ignored($ignoredSnippet)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnSnipFormSave':{
		$Diff=new ElementVer($modx,'snippet',$folderPlugin);
		if($Diff->ignored($ignoredSnippet)){
			$Diff->countVer=(int)$countSnippet;
			$Diff->save($modx->Event->params['id'],'post');
		}
		break;
	}
	case 'OnSnipFormRender':{
		$Diff=new ElementVer($modx,'snippet',$folderPlugin);
		if($Diff->ignored($ignoredSnippet)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
	
	/** Plugin */
	case 'OnPluginFormDelete':{
		$Diff=new ElementVer($modx,'plugin',$folderPlugin);
		if($Diff->ignored($ignoredPlugin)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnPluginFormSave':{
		$Diff=new ElementVer($modx,'plugin',$folderPlugin);
		if($Diff->ignored($ignoredPlugin)){
			$Diff->countVer=(int)$countPlugin;
			$Diff->save($modx->Event->params['id'],'post');
		}
		break;
	}
	case 'OnPluginFormRender':{
		$Diff=new ElementVer($modx,'plugin',$folderPlugin);
		if($Diff->ignored($ignoredPlugin)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
	
	/** Module */
	case 'OnModFormDelete':{
		$Diff=new ElementVer($modx,'module',$folderPlugin);
		if($Diff->ignored($ignoredModule)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnModFormSave':{
		$Diff=new ElementVer($modx,'module',$folderPlugin);
		if($Diff->ignored($ignoredModule)){
			$Diff->countVer=(int)$countModule;
			$Diff->save($modx->Event->params['id'],'post');
		}
		break;
	}
	case 'OnModFormRender':{
		$Diff=new ElementVer($modx,'module',$folderPlugin);
		if($Diff->ignored($ignoredModule)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
	
	/** Chunk */
	case 'OnChunkFormDelete':{
		$Diff=new ElementVer($modx,'chunk',$folderPlugin);
		if($Diff->ignored($ignoredChunk)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnChunkFormSave':{
		$Diff=new ElementVer($modx,'chunk',$folderPlugin);
		if($Diff->ignored($ignoredChunk)){
			$Diff->countVer=(int)$countChunk;
			$Diff->save($modx->Event->params['id'],'post');
		}
		break;
	}
	case 'OnChunkFormRender':{
		$Diff=new ElementVer($modx,'chunk',$folderPlugin);
		if($Diff->ignored($ignoredChunk)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
	/** Document */
	case 'OnDocFormDelete':{
		$Diff=new ElementVer($modx,'document',$folderPlugin);
		if($Diff->ignored($ignoredDoc)){
			$Diff->del($modx->Event->params['id']);
		}
		break;
	}
	case 'OnDocFormSave':{
		$Diff=new ElementVer($modx,'document',$folderPlugin);
		if($Diff->ignored($ignoredDoc)){
			$Diff->countVer=(int)$countDoc;
			$Diff->save($modx->Event->params['id'],'ta');
		}
		break;
	}
	case 'OnDocFormRender':{
		$Diff=new ElementVer($modx,'document',$folderPlugin);
		if($Diff->ignored($ignoredDoc)){
			$out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
			$modx->Event->output($out);
		}
		break;
	}
}
//?>