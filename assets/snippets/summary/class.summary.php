<?php
/**
 * SummaryText
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @see https://github.com/AgelxNash/DocLister/blob/master/core/controller/extender/summary.extender.inc
 * @see http://wiki.modx.im/evolution:snippets:doclister:extender:summary
 * @date 14.03.2012
 * @version 1.0.0
 */
 
class SummaryText{
	private $_cfg = array('content'=>'','summary'=>'');
	
	public function __construct($text,$action){
		$this->_cfg['content'] = is_scalar($text) ? $text : '';
		$this->_cfg['summary'] = is_scalar($action) ? $action : '';
	}

    public function run(){
        if(isset($this->_cfg['content'],$this->_cfg['summary']) && $this->_cfg['summary']!='' && $this->_cfg['content']!=''){
            $param=explode(",",$this->_cfg['summary']);
            foreach($param as $doing){
                $process=explode(":",$doing);
                switch($process[0]){
                    case 'notags':{
                        $this->_cfg['content']=strip_tags($this->_cfg['content']);
                        break;
                    }
                    case 'noparser':{
                        $this->_cfg['content']=$this->sanitarData($this->_cfg['content']);
                        break;
                    }
                    case 'len':{
                        if(!(isset($process[1]) && $process[1]>0)){
                            $process[1]=200;
                        }
                        $this->_cfg['content']=$this->summary($this->_cfg['content'],$process[1],50,true,"<cut/>");
                        break;
                    }
                }
            }
        }
        return $this->_cfg['content'];
    }

	/*
     * Clean up the modx and html tags
     *
     * @param string $data String for cleaning
     * @return string Clear string
     */
	final public function sanitarData($data){
		return is_scalar($data) ? str_replace(array('[', '%5B', ']', '%5D','{','%7B','}','%7D'), array('&#91;', '&#91;', '&#93;', '&#93;','&#123;','&#123;','&#125;','&#125;'),htmlspecialchars($data)) : '';	
	}
	 
    public function summary($resource, $truncLen, $truncOffset, $truncChars,$splitter){
        if ((mb_strstr($resource, $splitter, 'UTF-8'))) {
		    // HTMLarea/XINHA encloses it in paragraph's
			$summary = explode('<p>' . $splitter . '</p>', $resource); // For TinyMCE or if it isn't wrapped inside paragraph tags
			$summary = explode($splitter, $summary['0']);
			$summary = $summary['0'];
		}else if (mb_strlen($resource, 'UTF-8') > $truncLen) {
		    $summary = $this->html_substr($resource, $truncLen, $truncOffset, $truncChars);
		} else {
		    $summary = $resource;
		}

        $summary = $this->closeTags($summary);
		$summary=$this->rTriming($summary);

        return $summary;
	}
    public function html_substr($posttext, $minimum_length = 200, $length_offset = 100, $truncChars=false) {
		   $tag_counter = 0;
		   $quotes_on = FALSE;
		   if (mb_strlen($posttext) > $minimum_length && $truncChars != true) {
		       $c = 0;
		       for ($i = 0; $i < mb_strlen($posttext, 'UTF-8'); $i++) {
		           $current_char = mb_substr($posttext,$i,1, 'UTF-8');
		           if ($i < mb_strlen($posttext, 'UTF-8') - 1) {
		               $next_char = mb_substr($posttext,$i + 1,1, 'UTF-8');
		           }else {
		               $next_char = "";
		           }
		           if (!$quotes_on) {
		               // Check if it's a tag
		               // On a "<" add 3 if it's an opening tag (like <a href...)
		               // or add only 1 if it's an ending tag (like </a>)
		               if ($current_char == '<') {
		                   if ($next_char == '/') {
		                       $tag_counter += 1;
		                   }
		                   else {
		                       $tag_counter += 3;
		                   }
		               }
		               // Slash signifies an ending (like </a> or ... />)
		               // substract 2
		               if ($current_char == '/' && $tag_counter <> 0) $tag_counter -= 2;
		               // On a ">" substract 1
		               if ($current_char == '>') $tag_counter -= 1;
		               // If quotes are encountered, start ignoring the tags
		               // (for directory slashes)
		               if ($current_char == '"') $quotes_on = TRUE;
		           }
		           else {
		               // IF quotes are encountered again, turn it back off
		               if ($current_char == '"') $quotes_on = FALSE;
		           }

		           // Count only the chars outside html tags
		           if($tag_counter == 2 || $tag_counter == 0){
		               $c++;
		           }

		           // Check if the counter has reached the minimum length yet,
		           // then wait for the tag_counter to become 0, and chop the string there
		           if ($c > $minimum_length - $length_offset && $tag_counter == 0) {
		               $posttext = mb_substr($posttext,0,$i + 1, 'UTF-8');
		               return $posttext;
		           }
		       }
		   }  return $this->textTrunc($posttext, $minimum_length + $length_offset);
		}
	public function textTrunc($string, $limit, $break=". ") {
	  	// Original PHP code from The Art of Web: www.the-art-of-web.com

	    // return with no change if string is shorter than $limit
	    if(mb_strlen($string, 'UTF-8') < $limit) return $string;

	    $string = mb_substr($string, 0, $limit, 'UTF-8');
	    if(false !== ($breakpoint = mb_strrpos($string, $break, 'UTF-8'))) {
	      $string = mb_substr($string, 0, $breakpoint+1, 'UTF-8');
	    }else{
			if($break!=' '){
				$string=$this->textTrunc($string,$limit," ");
			}
		}
	    return $string;
	  }
	public function rTriming($str){
			$str=str_replace(" &ndash; "," - ",$str);
			$str=preg_replace('/[\r\n]++/',' ', $str);
			$str=preg_replace("/([\.,\-:!?;\s]+)$/ui","",$str);
			$str=str_replace(" - "," &ndash; ",$str);
			return $str;
		}
    private function closeTags($text) {
			$openPattern = "/<([^\/].*?)>/";
			$closePattern = "/<\/(.*?)>/";
			$endOpenPattern = "/<([^\/].*?)$/";
			$endClosePattern = "/<(\/.*?[^>])$/";
			$endTags = '';

			preg_match_all($openPattern, $text, $openTags);
			preg_match_all($closePattern, $text, $closeTags);

			$c = 0;
			$loopCounter = count($closeTags[1]); //used to prevent an infinite loop if the html is malformed
			while ($c < count($closeTags[1]) && $loopCounter) {
				$i = 0;
				while ($i < count($openTags[1])) {
					$tag = trim($openTags[1][$i]);

					if (mb_strstr($tag, ' ', 'UTF-8')) {
						$tag = mb_substr($tag, 0, strpos($tag, ' '), 'UTF-8');
					}
					if ($tag == $closeTags[1][$c]) {
						$openTags[1][$i] = '';
						$c++;
						break;
					}
					$i++;
				}
				$loopCounter--;
			}

			$results = $openTags[1];

			if (is_array($results)) {
				$results = array_reverse($results);

				foreach ($results as $tag) {
					$tag = trim($tag);

					if (mb_strstr($tag, ' ', 'UTF-8')) {
						$tag = mb_substr($tag, 0, strpos($tag, ' '), 'UTF-8');
					}
					if (!mb_stristr($tag, 'br', 'UTF-8') && !mb_stristr($tag, 'img', 'UTF-8') && !empty ($tag)) {
						$endTags .= '</' . $tag . '>';
					}
				}
			}
			return $text . $endTags;
		}
}