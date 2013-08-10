<?php
$version = "0.1.0 beta";
/*
* PREPARE
**/

$_Lang='';
$lang = $modx->config['manager_language'];

$language = $lang != 'russian-UTF8'?'en':'ru';


if (file_exists( dirname(__FILE__) .  '/lang/'.$lang.'.php')){
	include_once(dirname(__FILE__) .  '/lang/'.$lang.'.php');
} else {
	include_once(dirname(__FILE__) .  '/lang/'.$lang.'.php');
}

define('MODX_BASE_PATH',realpath('../').'/');


$Store = new Store;
$Store->lang = $_Lang;

$action = isset($_GET['action'])?$_GET['action']:$_POST['action'];


switch($action){
	case 'saveuser':
		$_SESSION['STORE_USER'] = $modx->db->escape($_POST['res']);
	break;
	
	case 'exituser':
		$_SESSION['STORE_USER'] = '';
	break;
	
	case 'install':
		if (is_dir(MODX_BASE_PATH.'assets/cache/store/')) $Store->removeFolder(MODX_BASE_PATH.'assets/cache/store/');
		$id = $modx->db->escape($_REQUEST['cid']);
		@mkdir("../assets/cache/store", 0777);
		@mkdir("../assets/cache/store/tmp_install", 0777);
		@mkdir("../assets/cache/store/install", 0777);
		
		if ($_GET['file']!='%url%' && $_GET['file']!='' && $_GET['file']!=' '){
			$url = $_GET['file'];
		} else {
			$url = "http://modx-store.com/get.php?get=file&cid=".$id;
		}

		$Store->downloadFile($url ,MODX_BASE_PATH."assets/cache/store/temp.zip"); //"../assets/cache/store/".$modx->db->escape($_REQUEST['name']).".zip");
		
		$zip = new ZipArchive;
		$res = $zip->open(MODX_BASE_PATH."assets/cache/store/temp.zip");//.$modx->db->escape($_REQUEST['name']).'.zip');
		
		if ($res === TRUE) {
		
		  $zip->extractTo(MODX_BASE_PATH."assets/cache/store/tmp_install");
		  $zip->close();
		  
		  if ($handle = opendir('../assets/cache/store/tmp_install')) {
				while (false !== ($name = readdir($handle))) if ($name != "." && $name != "..") $dir = $name;
				closedir($handle);
			}
			
		  $Store->copyFolder('../assets/cache/store/tmp_install/'.$dir.'/assets', '../assets/');

		  
		  $Store->copyFolder('../assets/cache/store/tmp_install/'.$dir, '../assets/cache/store/install');
		  $Store->removeFolder('../assets/cache/store/tmp_install/');
		  
			if ($_GET['method']!= 'fast'){
				header("Location: http://".$_SERVER['HTTP_HOST']."/assets/modules/store/installer/index.php?action=options");
				die();
			} else {
			
				chdir('../assets/modules/store/installer/');
				ob_start();
				
				require "instprocessor-fast.php";
				$content = ob_get_contents();
				ob_end_clean();
				echo $content;
			} 
		} else {
		  
		}

		$Store->removeFolder(MODX_BASE_PATH.'assets/cache/store/');
		die('[{"result":"'.$res.'"}]');
	break;
	
	case 'GET_category':
		$list  = file_get_contents('http://modx-store.com/get.php?get=GET_LIST');
		$list  = file_get_contents('http://modx-store.com/get.php?get=GET_LIST');

	break;
	case 'GET_list':
		$list  = file_get_contents('http://modx-store.com/get.php?get=GET_LIST');
		$list  = file_get_contents('http://modx-store.com/get.php?get=GET_LIST');

	break;
	
	default:
		//prepare list of snippets
		$types = array('snippets','plugins','modules');
		foreach($types as $key=>$value){
			$result=$modx->db->query('SELECT name,description FROM '.$modx->db->config['table_prefix'].'site_'.$value.' WHERE id <> "qqq"');
			while($row = $modx->db->GetRow($result)) {
				$PACK[$value][$row['name']]= $Store->get_version($row['description']) ;
			}
		}
		
		$Store->lang['user_email'] = $_SESSION['mgrEmail'];
		
		$Store->lang['hash'] = stripslashes( $_SESSION['STORE_USER'] );
		$Store->lang['lang'] = $language;	
		$Store->lang['_type'] = json_encode($PACK);	
		$Store->lang['v'] = $version;
		$tpl = Store::parse( $Store->tpl(dirname( __FILE__ ).'/template/main.html') ,$modx->config ) ;
		$tpl = Store::parse( $tpl ,$Store->lang ) ;
		echo $tpl;
	break;
}


class Store{
	public $lang;
	function get_version($text){
		preg_match('/<strong>(.*)<\/strong>/s',$text, $match);
		return $match[1];
	}
	
	static function parse($tpl,$field){
		foreach($field as $key=>$value)  $tpl = str_replace('[+'.$key.'+]',$value,$tpl);
		return $tpl;
	}

	function tpl($file){
		$lang = $this->lang;
		ob_start();
		include($file);
		$tpl = ob_get_contents();  
		ob_end_clean(); 
		return $tpl;
	}
	
	
	public function downloadFile ($url, $path) {

		$newfname = $path;
		$file = fopen ($url, "rb");
		if ($file) {
		$newf = fopen ($newfname, "wb");

		if ($newf)
			while(!feof($file)) {
				fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
			}
		}
		
		if ($file) fclose($file);
		if ($newf) fclose($newf);
	}
	public function removeFolder($path){
		$dir = realpath($path);
		$it = new RecursiveDirectoryIterator($dir);
		$files = new RecursiveIteratorIterator($it,
					 RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->getFilename() === '.' || $file->getFilename() === '..') {
				continue;
			}
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($dir);
	}
	public static function copyFolder($src, $dest) {
		$path = realpath($src);
		$dest = realpath($dest);
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object)
		{
		  $startsAt = substr(dirname($name), strlen($path));
		  self::mkDir($dest.$startsAt);
		  if(is_writable($dest.$startsAt) and $object->isFile())
		  {
			  copy((string)$name, $dest.$startsAt.DIRECTORY_SEPARATOR.basename($name));
		  }
		}
	  }

	  private static function mkDir($folder, $perm=0777) {
		if(!is_dir($folder)) {
		  mkdir($folder, $perm);
		}
	  }
}
	
?>