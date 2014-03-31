<?php
/*
menu->Build('id','parent','name','link','alt','onclick','permission','target','divider 1/0','menuindex')
*/
class EVOmenu{
	var $defaults = array();
	var $menu;
	var $output;
	function Build($menu,$setting=array()){
		$this->defaults['outerClass'] 	= 'nav';
		$this->defaults['parentClass'] 		= 'dropdown';
		$this->defaults['parentLinkClass'] = 'dropdown-toggle';
		$this->defaults['parentLinkAttr'] = 'data-toggle="dropdown"';
		$this->defaults['parentLinkIn'] = '<b class="caret"></b>';
		$this->defaults['innerClass'] = 'subnav';
		
		$this->defaults = $this->defaults + $setting; 
		$this->Structurise($menu);
		$this->output = $this->DrawSub('main',0);
		echo $this->output;
	}
	
	function Structurise($menu){
		foreach ($menu as $key => $row) {
			$data[$key] = $row[9];
		}

		array_multisort($data,SORT_ASC, $menu);

		foreach($menu as $key=>$value){
			$new[$value[1]][] = $value;
		}


		$this->menu = $new;
	}
	

	function DrawSub($parentid,$level){
		global $modx;
		if (isset($this->menu[$parentid])){
			
			$countChild = 0;
			foreach($this->menu[$parentid] as $key=>$value){
				$prms = false;
				$permissions = explode(',',$value[6]);
				foreach($permissions as $val) if($modx->hasPermission($val)) $prms = true;
				if (!$prms && $value[6]!='') continue;
				
				$countChild++;
				$output .= '
				<li id="'.$value[11].'" class="'.(isset($this->menu[$value[0]]) ? $this->defaults['parentClass'] : '').' '.$value[10].'">
					<a href="'.$value[3].'" alt="'.$value[4].'" target="'.$value[7].'" onclick="'.$value[5].'"
						'.(isset($this->menu[$value[0]])?' class="'.$this->defaults['parentLinkClass'].'"':'').' 
						'.(isset($this->menu[$value[0]])?' '.$this->defaults['parentLinkAttr']:'').'>
						'.$value[2].(isset($this->menu[$value[0]])?$this->defaults['parentLinkIn']:'').'</a>';
				
				if (isset($this->menu[$value[0]])){
					$level++;
					$output .= $this->DrawSub( $value[0] , $level);
					$level--;
				}
				$output .='</li>';
				if ($value[8]==1) $output .=  '<li class="divider"></li>';
			}

			if ($countChild>0) {
				$output =  '<ul  id="'.($level==0?$this->defaults['outerClass']:'').'" class="'.($level==0?$this->defaults['outerClass']:$this->defaults['innerClass']).'">'.$output.'</ul>';
			}
		}
		return $output;
	}
}


?>