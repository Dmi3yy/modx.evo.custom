<?php

/*
 * Title: Filter Class
 * Purpose:
 *  	The Filter class contains all functions relating to filtering,
 * 		the removing of documents from the result set
*/

class filter {
	var $array_key, $filtertype, $filterValue, $flip_mode, $filterArgs;

// ---------------------------------------------------
// Function: execute
// Filter documents via either a custom filter or basic filter
// ---------------------------------------------------
	function execute($resource, $filter)
	{
		global $modx;
		foreach ($filter['basic'] AS $currentFilter)
		{
			if (is_array($currentFilter) && count($currentFilter) > 0)
			{
				$this->array_key = $currentFilter['source'];
				
				$this->flip_mode  = (substr($currentFilter['mode'],0,1)==='!' && substr($currentFilter['mode'],0,2)!=='!!') ? 1 : 0;
				if($this->flip_mode) $currentFilter['mode'] = substr($currentFilter['mode'],1);
				
				switch($currentFilter['value'])
				{
					case '>':
					case '>=':
					case '<':
					case '<=':
					case '!=':
					case '<>':
					case '==':
					case '=~':
					case '!~':
						$t = $currentFilter['value'];
						$currentFilter['value'] = $currentFilter['mode'];
						$currentFilter['mode'] = $t;
						unset($t);
						break;
				}
				
				if(substr($currentFilter['value'],0,5) === '@EVAL')
				{
					$eval_code = trim(substr($currentFilter['value'],6));
					$eval_code = trim($eval_code,';') . ';';
					if(strpos($eval_code,'return')===false)
					{
						$eval_code = 'return ' . $eval_code;
					}
					$this->filterValue = eval($eval_code);
				}
				else
				{
					$this->filterValue = $currentFilter['value'];
				}
				if(strpos($this->filterValue,'[+') !== false)
				{
					$this->filterValue = $modx->mergePlaceholderContent($this->filterValue);
				}
				$this->filtertype = (isset($currentFilter['mode'])) ? $currentFilter['mode'] : 1;
				
				$resource = array_filter($resource, array($this, 'basicFilter'));
			}
		}
		foreach ($filter['custom'] AS $currentFilter)
		{
			$resource = array_filter($resource, $currentFilter);
		}
		return $resource;
	}
	
// ---------------------------------------------------
// Function: basicFilter
// Do basic comparison filtering
// ---------------------------------------------------
	
	function basicFilter ($options) {
			$unset = 1;
			switch ($this->filtertype) {
				case '!=' :
				case '<>' :
				case 'ne' :
				case 1 :
					if (!isset ($options[$this->array_key]) || $options[$this->array_key] != $this->filterValue)
						$unset = 0;
					break;
				case '==' :
				case 'eq' :
				case 2 :
					if ($options[$this->array_key] == $this->filterValue)
						$unset = 0;
					break;
				case '<' :
				case 'lt' :
				case 3 :
					if ($options[$this->array_key] < $this->filterValue)
						$unset = 0;
					break;
				case '>' :
				case 'gt' :
				case 4 :
					if ($options[$this->array_key] > $this->filterValue)
						$unset = 0;
					break;
				case 5 :
					if (!($options[$this->array_key] < $this->filterValue))
						$unset = 0;
					break;
				case 6 :
					if (!($options[$this->array_key] > $this->filterValue))
						$unset = 0;
					break;
				case '<=' :
				case 'lte' :
				case 'le' :
					if ($options[$this->array_key] <= $this->filterValue)
						$unset = 0;
					break;
				case '>=' :
				case 'gte' :
				case 'ge' :
					if ($options[$this->array_key] >= $this->filterValue)
						$unset = 0;
					break;
					
				// Cases 7 & 8 created by MODx Testing Team Member ZAP
				case 'find':
				case 'search':
				case 'strpos':
				case '=~':
				case 7 :
					if (strpos($options[$this->array_key], $this->filterValue)===FALSE)
						$unset = 0;
					break;
				case '!~':
				case 8 :
					if (strpos($options[$this->array_key], $this->filterValue)!==FALSE)
						$unset = 0;
					break;
				
				// Cases 9-11 created by highlander
				case 9 : // case insenstive version of #7 - exclude records that do not contain the text of the criterion
					if (strpos(strtolower($options[$this->array_key]), strtolower($this->filterValue))===FALSE)
						$unset = 0;
					break;
				case 10 : // case insenstive version of #8 - exclude records that do contain the text of the criterion
					if (strpos(strtolower($options[$this->array_key]), strtolower($this->filterValue))!==FALSE)
						$unset = 0;
					break;
				case 11 : // checks leading character of the field
					$firstChr = strtoupper(substr($options[$this->array_key], 0, 1));
					if ($firstChr!=$this->filterValue)
						$unset = 0;
					break;
				case 'regex':
				case 'preg':
					if (preg_match($options[$this->array_key], $this->filterValue)!==FALSE)
						$unset = 0;
					break;
		}
		if($this->flip_mode) $unset = ($unset===1) ? 0 : 1;
		return $unset;
	}
	
}
?>