<?php
/*
 * Name:	fileCache
 * URL:		http://neo22s.com/filecache/
 * Version:	v1.1
 * Date:	21/10/2010
 * Author:	Chema Garrido
 * License: GPL v3
 * Notes:	fileCache class, caches variables in standalone files if value is too long or uses unique file for small ones
 * Modified by thebat053 for MODx CacheAccelerator
 */

/////////////////////class file cache

class fileCache {
	private $cache_path;//path for the cache
	private $cache_expire;//seconds that the cache expires
//	private $application=array();//application object like in ASP
// 	private $application_file;//file for the application object
// 	private $application_write=false;//if application write is true means there was changes and we need to write the app file
 	private $debug=false; //no debug by default
	private $log=array();//log for the debug system
	private $start_time=0;//application start time
// 	private static $content_size=64;//this is the max size can be used in APP cache if bigger writes independent file
	private static $instance;//Instance of this class
	    
    // Always returns only one instance
    public static function GetInstance($exp_time=3600,$path='cache/'){
       	if($path[strlen($path)-1] != '/') $path .= '/';
        if (!isset(self::$instance)){//doesn't exists the isntance
        	 self::$instance = new self($exp_time,$path);//goes to the constructor
        }
        return self::$instance;
    }
    
	//cache constructor, optional expiring time and cache path
	private function __construct($exp_time,$path){
	    $this->start_time=microtime(true);//time starts
		$this->cache_expire=$exp_time;
		if ( ! is_writable($path) ) trigger_error('Path not writable:'.$path);
		else $this->cache_path=$path;
	}
	
	public function __destruct() {
    	$this->addLog('destruct');
		$this->returnDebug();
	}
	
	// Prevent users to clone the instance
    public function __clone(){
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	
	//deletes cache from folder
	public function deleteCache($older_than='', $groups = null){
	    //if not set MODX_BASE_PATH
		if(strpos($this->cache_path,MODX_BASE_PATH)===false) return;
		
	    $this->addLog('delete cache');
		if (!is_numeric($older_than)) $older_than=$this->cache_expire;
		if(!$groups)
			$this->deleteCacheAll($this->cache_path, $older_than);
		else {
			foreach($groups as $group){
				if(!$files = @scandir($this->cache_path.$group[0].'/')) break; 
				foreach($files as $file){
					if(substr($file, 0, strlen($group)) == $group){
						if (strlen($file)>2 && time() > (@filemtime($this->cache_path.$group[0].'/'.$file) + $older_than) ) {
							@unlink($this->cache_path.$group[0].'/'.$file);//echo "<br />-".$file; 
							$this->addLog('delete cache file:'.$this->cache_path.$group[0].'/'.$file);
						}
					}
				}
			}
		}
	}
	
	private function deleteCacheAll($dir, $older_than){
		if($dir[strlen($dir)-1] != '/')
			$dir .= '/';
        $nDir = @opendir($dir);
        if($nDir){
			while($file = readdir($nDir)){
           		if($file != '.' && $file != '..'){
                	$filename = $dir.$file;
					if(!is_dir($filename)){
						if (strlen($file)>2 && time() > (filemtime($filename) + $older_than) ) {
							@unlink($filename); 
							$this->addLog('delete cache file:'.$filename);
						}
					} else {
                   		$this->deleteCacheAll($filename, $older_than);
					}
				}
			}
		}
	}
	
	//writes or reads the cache
	public function cache($key, $group, $value=''){
		if ($value!=''){//wants to write
		    $this->addLog('cache function write in file key:'. $key);
			$this->put($key, $group, $value);
		}
		else{//reading value
		    $this->addLog('cache function read file key:'. $key);
		    return $this->get($key, $group);//returns from file cache
		}
	}
	
	//deletes a key from cache
	public function delete($name, $group){
		if ( file_exists($this->fileName($name, $group)) ){//unlink filename
		    $this->addLog('unset File key:'. $group.':'.$name);
			@unlink($this->fileName($name, $group));
		}
	}
	
	//////////Cache for files individually///////////////////
	
		//creates new cache files with the given data, $key== name of the cache, data the info/values to store
		private function put($key, $group, $data){
			$this->checkFileNamePath($group);
			if ( $this->get($key, $group)!= $data ){//only write if it's different
				$values = serialize($data);
				$filename = $this->fileName($key, $group);
				$file = fopen($filename, 'w');
			    if ($file){//able to create the file
			        $this->addLog('writing key: '.$key.' file: '.$filename);
			        fwrite($file, $values);
			        fclose($file);
			    }
			    else  $this->addLog('unable to write key: '.$key.' file: '.$filename);
			}//end if different
		}
		
		//returns cache for the given key
		private function get($key, $group){
			$filename = $this->fileName($key, $group);
			if (!file_exists($filename) || !is_readable($filename)){//can't read the cache
			    $this->addLog('can\'t read key: '.$key.' file: '.$filename);
				return null;
			}
			
			if ( time() < (filemtime($filename) + $this->cache_expire) ) {//cache for the key not expired
				$file = fopen($filename, 'r');// read data file
		        if ($file){//able to open the file
		            $data = fread($file, filesize($filename));
		            fclose($file);
		            $this->addLog('reading key: '.$key.' file: '.$filename);
		            return unserialize($data);//return the values
		        }
		        else{
		            $this->addLog('unable to read key: '.$key.' file: '.$filename);
		            return null;
		        }
			}
			else{
			    $this->addLog('expired key: '.$key.' file: '.$filename);
			    @unlink($filename);	
			    return null;//was expired you need to create new
			}
	 	}
		
	 	//returns the filename for the cache
		private function fileName($key, $group){
//			if(!$group)
//				$group = 'default';
//			return $this->cache_path.$group[0].'/'.$group.'/'.md5($key);
			return $this->cache_path.$group[0].'/'.$group.'_'.md5($key);
		}
		
		private function checkFileNamePath($group){
			try {
//				if(!$group)
//					$group = 'default';
				if(!file_exists($this->cache_path.$group[0]))
					mkdir($this->cache_path.$group[0]);
//					mkdir($this->cachePath.$group[0].'/'.$group);
//				} else
//					if(!file_exists($this->cachePath.$group[0].'/'.$group))
//						mkdir($this->cachePath.$group[0].'/'.$group);
			} catch(Error $e) {
				echo("CacheAccelerator Error! Can't create cache path! Error: ".$e->getMessage());
			}
		}
 	//////////END Cache for files individually///////////////////
 	
    ////DEBUG
    //sets debug on or off
		public function setDebug($state){
			$this->debug=(bool) $state;
		}
		
		public function returnDebug($type='HTML'){
			if ($this->debug){
				switch($type){
					case 'array':
						return $this->log;
					break;
					case 'HTML'://returns debug as HTML
						echo '<ol>';
						foreach($this->log as $key=>$value){//loop in the log var
							echo '<li>'.$value.'</li>';
						}
						echo '</ol>';	
					break;
				}	
			}
			else return false;	
		}
		
		//add debug log
		public function addLog($value){
			if ($this->debug){//only if debug enabled
				array_push($this->log, round((microtime(true) - $this->start_time),5).'s - '. $value);  
			}
		}
}
?>
