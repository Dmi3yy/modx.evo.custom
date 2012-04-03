<?php
/*
*	CacheExtender by thebat053
*	Cache create class
*/

class CacheFill{
	var $data = '';
	var $index = array();
	var $indexData;
	var $pointer = 0;

	public function __construct(){
	}

	public function add($value){
		$len = strlen($value);
		$this->data .= $this->dec2bin($len, 3).$value;
		$lastPointer = $this->pointer;
		$this->pointer += $len + 3;
		return $lastPointer;
	}

	public function addIndex($key, $value){
		$this->index[(int)$key] = (int)$value;
	}

	private function generateIndex(){
		$this->indexData = $this->dec2bin(count($this->index), 4);
		ksort($this->index);
		foreach($this->index as $key => $value)
			$this->indexData .= $this->dec2bin($key, 4).$this->dec2bin($value, 4);
	}

	public function flush($cacheName){
		$this->generateIndex();
		if(!$fp = @fopen($cacheName, 'wb')) die ("Fatal error: Can't open cache file!");
      	fwrite($fp, $this->indexData.$this->data);
      	fflush($fp);
		return true;
	}

	private function dec2bin($num, $bytes){
		$result = "";
		for($i=0; $i<$bytes; ++$i){
			$result .= chr($num&0xFF);
			$num = $num >> 8;
		}
		return $result;
	}

}

class CacheFillUrl {
	var $data = '';
	var $index = array();
	var $indexData;
	var $pointer = 0;

	public function __construct(){
	}

	public function addIndex($key, $value){
		$this->index[(string)$key] = (int)$value;
	}

	private function generateIndexData(){
		$this->indexData = $this->dec2bin(count($this->index), 4);
		ksort($this->index, SORT_STRING);
		foreach($this->index as $key => $value){
			$this->data .= $this->dec2bin(strlen($key), 2).$key.$this->dec2bin($value, 4);
			$this->indexData .= $this->dec2bin($this->pointer, 4);
			$this->pointer += 2 + strlen($key) + 4;
		}
	}

	public function flush($cacheName){
		$this->generateIndexData();
		if(!$fp = @fopen($cacheName, 'wb')) die ("Fatal error: Can't open cache file!");
      	fwrite($fp, $this->indexData.$this->data);
      	fflush($fp);
		return true;
	}

	private function dec2bin($num, $bytes){
		$result = "";
		for($i=0; $i<$bytes; ++$i){
			$result .= chr($num&0xFF);
			$num = $num >> 8;
		}
		return $result;
	}
	
}
?>