<?php

/*  CacheExtender Wrapper for MODx Evo by thebat053
	Array $d['url'] => id
	Array $a[id] => array(id, alias, path, parent, array(child1, child2, child3));
						  [0] [1]    [2]   [3]     [4]
*/
abstract class documentCacheBase implements Iterator, Countable {
	protected $cache;
	protected $cacheLen;
	protected $pointer = 0;
	protected $dataStart;
	protected $records;
	protected $curRecord;
	protected $recordLen;
    public function rewind() {$this->pointer = $this->dataStart;$this->curRecord = 0;}
    public function key() {return $this->bin2dec(substr($this->cache, 4 + $this->curRecord*8, 4),4);}
    public function next() {$this->pointer += $this->recordLen;$this->curRecord++;}
    public function valid() {return($this->pointer >= $this->dataStart && $this->pointer < $this->cacheLen);}   
    public function count() {return $this->records;}
	protected function bin2dec(&$str, $len){$shift = 0;$result = 0;for($i=0; $i<$len; ++$i){$result |= (@ord($str[$i])<<$shift);$shift += 8;}return $result;}
	protected function getRecord($key){if(!isset($key))return false;if(!$offset = $this->findOffset((int)$key)) return false; $len = $this->bin2dec(substr($this->cache, $offset , 3),3); return substr($this->cache, $offset + 3, $len);}
	protected function getRecordCurrent(){$len = $this->bin2dec(substr($this->cache, $this->pointer , 3),3); $this->recordLen = (int)$len + 3; return substr($this->cache, $this->pointer + 3, $len);}
	protected function findOffset($key){$left = 1; $right = $this->records; while ($left <= $right){ $middle = (int)(($left+$right)/2); $ckey = (int)$this->bin2dec(substr($this->cache,$middle*8 - 4,4),4); if(($left == $right) && ($ckey != $key)) return false; else if($ckey == $key) return $this->bin2dec(substr($this->cache, $middle*8,4),4) + $this->dataStart; else if($key < $ckey) $right = $middle - 1; else if($key > $ckey) $left = $middle + 1;} return false;}
}

//AliasListingClass
//call: aliasListing[documentId]
//returns: array('id' => documentId, 'alias' => documentAlias, 'path' => documentPath, 'parent' => documentParent), analog $a array
class AliasListingClass extends documentCacheBase implements ArrayAccess {
	public function __construct($cache){$this->cache = $cache;$this->records = $this->bin2dec(substr($cache,0,4),4);$this->dataStart = $this->records * 8 + 4;$this->cacheLen = strlen($cache);}
	public function offsetExists($offset){return $this->findOffset((int)$offset);}
	public function offsetGet($offset){return $this->parseOutput($this->getRecord((int)$offset));}
	public function offsetSet($key, $value){echo("Cache content can't be modified!");return false;}
	public function offsetUnset($key){echo("Cache content can't be modified!");return false;}
    public function current(){return $this->parseOutput($this->getRecordCurrent());}
	private function parseOutput($item){
		if(!$item)
			return false;
		$tmp = unserialize($item);
		return array('id' => $tmp[0], 'alias' => $tmp[1], 'path' => $tmp[2], 'parent' => $tmp[3]);
	}
	function __destruct() {unset($this->cache);}
}

//DocumentMapCacheClass
//call: documentMap_cache[documentId]
//returns: array(child1, child2, child3 ..... childN), analog function getChildIds(..)  documentMap_cache array
class DocumentMapCacheClass extends documentCacheBase implements ArrayAccess {
	public function __construct($cache){$this->cache = $cache;$this->records = $this->bin2dec(substr($cache,0,4),4);$this->dataStart = $this->records * 8 + 4;$this->cacheLen = strlen($cache);}
	public function offsetExists($offset){return $this->findOffset((int)$offset);}
	public function offsetGet($offset){return $this->parseOutput($this->getRecord((int)$offset));}
	public function offsetSet($key, $value){echo("Cache content can't be modified!");return false;}
	public function offsetUnset($key){echo("Cache content can't be modified!");return false;}
    public function current(){return $this->parseOutput($this->getRecordCurrent());}
	private function parseOutput($item){
		if(!$item)
			return false;
		$tmp = unserialize($item);
		return $tmp[4];
	}
	function __destruct() {unset($this->cache);}
}
//DocumentMapClass
//call: documentMap[anyid], may foreach
//returns: array(parent => documentId)  analog $m array
class DocumentMapClass extends documentCacheBase implements ArrayAccess {
	public function __construct($cache){$this->cache = $cache;$this->records = $this->bin2dec(substr($cache,0,4),4);$this->dataStart = $this->records * 8 + 4;$this->cacheLen = strlen($cache);}
	public function offsetExists($offset){return $this->findOffset((int)$offset);}
	public function offsetGet($offset){return $this->parseOutput($this->getRecord((int)$offset));}
	public function offsetSet($key, $value){echo("Cache content can't be modified!");return false;}
	public function offsetUnset($key){echo("Cache content can't be modified!");return false;}
    public function current(){return $this->parseOutput($this->getRecordCurrent());}
	private function parseOutput($item){
		if(!$item)
			return false;
		$tmp = unserialize($item);
		return array($tmp[3] => $tmp[0]); //parent => id
	}
	function __destruct() {unset($this->cache);}
}

class DocumentListingClass implements ArrayAccess, Iterator, Countable {
	private $cache;
	private $cacheLen;
	private $pointer = 0;
	private $dataStart;
	private $records;
	private $curRecord;
	private $recordLen;
	public function __construct($cache){$this->cache = $cache;$this->records = $this->bin2dec(substr($cache,0,4),4);$this->dataStart = $this->records * 4 + 4;$this->cacheLen = strlen($cache);}
    public function rewind() {$this->pointer = $this->dataStart; $this->curRecord = 0;}
    public function key() {$offset = $this->bin2dec(substr($this->cache, 4 + $this->curRecord*4, 4),4); $len = $this->bin2dec(substr($this->cache, $this->dataStart + $offset,2),2); return substr($this->cache, $this->dataStart + $offset + 2,$len);}
    public function next() {$this->pointer += $this->recordLen; $this->curRecord++;}
    public function valid() {return($this->pointer >= $this->dataStart && $this->pointer < $this->cacheLen);}   
    public function count() {return $this->records;}
	public function offsetExists($offset){return $this->findOffset($offset);}
	public function offsetGet($offset){return $this->parseOutput($this->getRecord($offset));}
	public function offsetSet($key, $value){echo("Cache content can't be modified!");return false;}
	public function offsetUnset($key){echo("Cache content can't be modified!");return false;}
    public function current(){return $this->parseOutput($this->getRecordCurrent());}
	protected function bin2dec(&$str, $len){$shift = 0;$result = 0;for($i=0; $i<$len; ++$i){$result |= (@ord($str[$i])<<$shift);$shift += 8;}return $result;}
	protected function getRecord($key){if(!$key) return false; if(!$offset = $this->findOffset($key)) return false; $len = $this->bin2dec(substr($this->cache, $offset , 2),2); return (int)$this->bin2dec(substr($this->cache, $offset + $len + 2, 4), 4);}
	protected function getRecordCurrent(){$len = $this->bin2dec(substr($this->cache, $this->pointer , 2),2); $this->recordLen = (int)$len + 2 + 4; return $this->bin2dec(substr($this->cache, $this->pointer + 2 + $len, 4),4);}
	protected function findOffset($key){$key = (string)$key; $left = 1; $right = $this->records; while ($left <= $right){$middle = (int)(($left+$right)/2); $idxoffs = $this->bin2dec(substr($this->cache,$middle * 4 ,4),4); $len = $this->bin2dec(substr($this->cache, $this->dataStart + $idxoffs, 2),2); $ckey = substr($this->cache, $this->dataStart + $idxoffs + 2, $len); if(($left == $right) && ((string)$ckey != (string)$key)) return false; else if((string)$ckey == (string)$key) return $idxoffs + $this->dataStart; else if((string)$key < (string)$ckey) $right = $middle - 1; else if((string)$key > (string)$ckey) $left = $middle + 1;} return false;}

// $strict not supported at this time
	public function array_search($value, &$obj = null, $strict = false){ if(is_null($value)) return null; $value = (int)$value; foreach($this as $item) if($item == $value) return $this->key(); return false;}
	public function array_key_exists($key, &$obj = null){return $this->offsetExists($key);}
	public function array_values(&$obj){$result = array(); foreach($this as $item) array_push($result, $item); return $result;}
	public function array_keys(&$obj = null, $search_value = null, $strict = false){$result = array(); foreach($this as $item){ if(!$search_value) array_push($result, $this->key()); else if($this->key() == $search_value) array_push($result, $this->key());} return $result;}
	public function array_push(&$obj, $value){echo("Cache content can't be modified!");return false;}
	public function array_shift(&$obj){echo("Cache content can't be modified!");return false;}
	public function array_pop(&$obj, $offset, $length = 0, $preserve_keys = false){echo("Cache content can't be modified!");return false;}
	public function in_array($value, &$obj = null){return ($this->array_search($value) !== false);}
	private function parseOutput($item){
		if(!$item) return false;
		return (int)$item;
	}
	function __destruct() {unset($this->cache);}
}

if($cacheMode == 'part')
	$this->documentListing = &$d;
else {
	$cacheFileUrl = file_get_contents($cacheFileNameUrl);
	$this->documentListing = new DocumentListingClass($cacheFileUrl);
}
$cacheFile = file_get_contents($cacheFileName);
$this->aliasListing = new AliasListingClass($cacheFile);
$this->documentMap = new DocumentMapClass($cacheFile);
$this->documentMap_cache = new DocumentMapCacheClass($cacheFile);
?>