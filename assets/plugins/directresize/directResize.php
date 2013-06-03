<?php

class directResize {

	var $drconfig;
		
	//-------------------------------------------------------------------------------------------------
	
	function directResize($drconfig, $input)
	{
		$this->Labels = array("drlightbox", "drthumbonly", "drskip");
		
		// Массив для складирования уже обработанных имен файлов. Служит для переименования файлов с одинаковыми именами но из разных источников в одном документе
		$this->namesStack = array();
		
		$this->drconfig = $drconfig;
		$this->ProcessContent($input);
	}
	//-------------------------------------------------------------------------------------------------
	/*
	Функция создания локальных копий файлов с удаленных серверов.
	Проверяет, существует ли локальная копия данного файла, проверяет время последнего обновления файла и сравнивает с $drconfig['remote_refresh_time']
	В случае необходимости - копирует файл с удаленного сервера и создает локальную копию, которая используется в последствии для генерации тумб
	*/
	function CheckRemoteImg($imgFile)
	{
		global $modx;
		
		$this->SetTargetFilename(true);
		
		if (file_exists($modx->config['base_path'].$this->targetFilename))
		{
			if (filemtime($modx->config['base_path'].$this->targetFilename)+$this->drconfig['remote_refresh_time']*60 <=time()) $update = TRUE;
		}
		else
		{
			$copy = TRUE;
		}

		/*
		 Сравниваем размеры удаленного файла с локальной копией, если высота или ширина не совпадают - закачиваем файл заново
		 Если совпадают, меняем время изменения файла на текущее
		*/
		if ($update)
		{
			$localSize=getimagesize($modx->config['base_path'].$this->targetFilename);
			$remoteSize=getimagesize($imgFile);
			
			if ($localSize[0]!=$remoteSize[0] || $localSize[1]!=$remoteSize[1])
			{
				$copy = TRUE;
			}
			else
			{
				touch($modx->config['base_path'].$this->targetFilename);
			}
		}
		
		if ($copy)	
		{
			//echo $imgFile;
			copy($imgFile, $this->getAbsPath($this->targetFilename));
			chmod($this->getAbsPath($this->targetFilename), 0777);
			$name = basename($this->targetFilename);
			$path = $modx->config['base_path'].$this->createDir();
			if (file_exists($path."big_".$name)) unlink($path."big_".$name);
			if (file_exists($path."thumb_".$name)) unlink($path."thumb_".$name);
			$name = str_replace("://", "---", $this->remotePath);
			$name = str_replace("/", "--", $name);
			if (file_exists($path."wysiwyg_".$name)) @unlink($path."wysiwyg_".$name);
		}
		
		$this->originalremotePath=$imgFile;
		$this->img_src = $this->targetFilename;
		
	}
	//-------------------------------------------------------------------------------------------------
	
	function PrepareImg($imgFile)
	{
		global $modx;
		
		$this->img_src = $imgFile;
		
		if (strstr($this->img_src, "://")) 
		{
			$this->CheckRemoteImg($this->img_src);
		}
		
		$abs_imgFile =  $modx->config['base_path'].$this->img_src;
		
		if (!file_exists($abs_imgFile)){	
			return false;
		} else {
			$size = getimagesize($abs_imgFile);
			$this->img_src_w = $size[0];
			$this->img_src_h = $size[1];
		}
			
		$img_ext = strtolower(substr(strrchr($imgFile,'.'),1));
				
		include_once($modx->config['base_path'].DIRECTRESIZE_PATH.'includes/Thumbnail.class.php');
				
		$this->thumbclass = new DRThumbnail($abs_imgFile);
		
		if($img_ext == "jpg" || $img_ext == "jpeg"){
			$this->thumbclass->output_format='JPG';
		}else if($img_ext == "png"){
			$this->thumbclass->output_format='PNG';
		}
		
		return $this->img_src;
	}
	
	//-------------------------------------------------------------------------------------------------
	/*
	Функция для определения размеров генерируемого изображения, базирующаяся на параметрах ширины imgWidth  и/или высоты imgHeight и метода изменения размера изображения resize_method
	resize_method принимает значение от 0 до 3, во всех случаях пропорции изображения сохраняются
	
	0 - изображение сначала уменьшается, затем обрезается так, чтобы вписаться в прямоугольник imgWidth х imgHeight
	1 - по заданной ширине автоматически вычисляется высота изображения
	2 - по заданной высоте автоматически вычисляется ширина изображения
	3 - изображение уменьшается таким образом чтобы не превышать заданные imgWidth и imgHeight
	*/
	function CalcSizes()
	{
		global $modx;
		$this->imgWidth = (int) $this->imgWidth;
		$this->imgHeight = (int) $this->imgHeight;
		
		$resize_method = $this->drconfig['resize_method'];
		
		if ($this->imgWidth == 0) $resize_method = 2;
		if ($this->imgHeight == 0) $resize_method = 1;
		if ($this->mode == "big") $resize_method = 3;
		
		switch ($resize_method){
			case 0:
				$this->thumbclass->crop = true;
				$this->thumbclass->size($this->imgWidth, $this->imgHeight);
				break;
			case 1:
				$this->thumbclass->size_width($this->imgWidth);
				break;
			case 2:
				$this->thumbclass->size_height($this->imgHeight);
				break;
			case 3:
				$this->thumbclass->size($this->imgWidth, $this->imgHeight);
				break;
		}
		
		$this->imgWidth = (int)($this->thumbclass->img["x_thumb"]);
		$this->imgHeight = (int)($this->thumbclass->img["y_thumb"]);
		
	}
	//-------------------------------------------------------------------------------------------------
	
	function SetTargetFilename($isremote = false)
	{
		global $modx;
		
		if (isset($this->remotePath)) 
		{
			$this->img_src=$this->remotePath;
			unset($this->remotePath);
		}
		
		$img_src_ext	= substr(strrchr($this->img_src,'.'),1);
		$img_src_name	= basename($this->img_src, ".".$img_src_ext);
				
		$prefix = $modx->isBackend() ? "wysiwyg" : $this->mode;

		if (strlen($prefix)>0) $prefix.="_";
		
		if ($isremote) 
		{
			$prefix="";
			$this->remotePath = $this->img_src;
		}
		
		$c = "";
		
		while (in_array($this->createDir().$prefix.$img_src_name.$c.".".$img_src_ext, $this->namesStack))
		{
			$c.="c";
		}
		
		$this->targetFilename = $this->createDir().$prefix.$img_src_name.$c.".".$img_src_ext;
		$this->namesStack[] = $this->targetFilename;
		
		if ($isremote) return;
		
		if ($modx->isBackend()) 
		{
			$dir = str_replace("://", "---", dirname($this->img_src));
			$dir = str_replace("/", "--", $dir);
			$this->targetFilename = $this->createDir().$prefix.$dir."--".$img_src_name.".".$img_src_ext;
		}
		//$this->targetFilename = $this->targetFilename;
	}
	
	//-------------------------------------------------------------------------------------------------
	/*
	Проверяем:
	//- существует ли целевая папка img_target_dir
	- заданы ли высота или ширина генерируемого изображения
	- не равны ли заданные высота и щирина генерируемого изображения реальным высоте и ширине исходного изображения (нужно ли вообще генерировать изображение)
	*/
	function Verify()
	{
		global $modx;

		//$abs_targetDir = $modx->config['base_path'].$this->createDir();
		
		//if (!is_dir($abs_targetDir)) return false;
		if ($this->imgWidth == 0 && $this->imgHeight == 0) return false;
		if ($this->imgWidth == $this->img_src_w && $this->imgHeight == $this->img_src_h) return false;
		
		return true;
	}
	
	//-------------------------------------------------------------------------------------------------
	/*
	Проверяем:
	-  существует ли уже сгенерированное ранее изображение по заданному пути с данными высотой и шириной
	*/
	function RecreateVerify()
	{
		global $modx;
		$abs_targetFile = $modx->config['base_path'].$this->targetFilename;
		
		if (file_exists($abs_targetFile))
		{
			$size = getimagesize($abs_targetFile);
			$img_target_w 	= $size[0];
			$img_target_h 	= $size[1];		
			if (($this->imgWidth == $img_target_w) && ($this->imgHeight == $img_target_h)) return true;
		}
		
		return false;
	}
	//-------------------------------------------------------------------------------------------------
	/*
	Наносим водяные знаки если необходимо
	*/
	function ApplyWatermark($watermark)
	{
		global $modx;
		
		if ($watermark[use_watermark]){	
			if ($watermark[watermark_type] == "image"){
				$this->thumbclass->img_watermark=$watermark[watermark_img];
				$this->thumbclass->img_watermark_Valing=strtoupper($watermark[watermark_valign]);
				$this->thumbclass->img_watermark_Haling=strtoupper($watermark[watermark_halign]);
			}else{
				$this->thumbclass->txt_watermark=$watermark[watermark_txt];
				$this->thumbclass->txt_watermark_color=$watermark[watermark_txt_color];
				$this->thumbclass->txt_watermark_font=$watermark[watermark_font];
				$this->thumbclass->txt_watermark_Valing=strtoupper($watermark[watermark_valign]);
				$this->thumbclass->txt_watermark_Haling=strtoupper($watermark[watermark_halign]);
				$this->thumbclass->txt_watermark_Hmargin=strtoupper($watermark[watermark_txt_hmargin]);
				$this->thumbclass->txt_watermark_Vmargin=strtoupper($watermark[watermark_txt_vmargin]);
			}
		}
		// Специальный водяной знак для WYSIWYG-редактора
		if ($modx->isBackend()) 
		{
			$this->thumbclass->img_watermark=$modx->config['base_path']."assets/plugins/directresize/images/wysiwyg.png";
			$this->thumbclass->img_watermark_Valing="BOTTOM";
			$this->thumbclass->img_watermark_Haling="RIGHT";
		}
	}
	//-------------------------------------------------------------------------------------------------
	
	function Process()
	{
		global $modx;
		
		if ($this->Verify())
		{	
			$this->SetTargetFilename();
			$this->CalcSizes();
		
			if ($this->RecreateVerify()) return $this->targetFilename;
			
			$this->thumbclass->quality = $this->mode == "thumb" ? $this->drconfig['thumb_quality'] : $this->drconfig['big_quality'];
			if ($modx->isBackend()) $this->thumbclass->quality =  $this->drconfig['wysiwyg_quality'];
			
			$watermark = $this->mode == "thumb" ? $this->drconfig['thumb_watermark'] : $this->drconfig['big_watermark'];
			$this->ApplyWatermark($watermark);
			
			$this->thumbclass->process();
			$this->thumbclass->save($modx->config['base_path'].$this->targetFilename);
			
			if ($this->thumbclass->img["src"]) {
				@ImageDestroy($this->thumbclass->img["src"]);
			}
			if ($this->thumbclass->img["watermark"]) {
				@ImageDestroy($this->thumbclass->img["watermark"]);
			}
			return $this->targetFilename;
		}
		return $this->img_src;
	}

	//-------------------------------------------------------------------------------------------------
	/*
	Проверяем разрешенные расширения для изображений
	*/	
	function CheckAllowedExt($imgFile)
	{	
		$img_ext = strtolower(substr(strrchr($imgFile,'.'),1));
			
		if ($img_ext != "jpg" && $img_ext != "jpeg" && $img_ext != "png"){
			return false;
		}
		
		return true;
	}
	//-------------------------------------------------------------------------------------------------
	function getAbsPath($path)
	{
		global $modx;
		return strstr($path, "://") ? $path : $modx->config['base_path'].$path;
	}
	//-------------------------------------------------------------------------------------------------
	/*
	Проверяем разрешенные и запрещенные пути для применения плагина
	*/
	function checkPath($path)
	{
		global $modx;
		
		// Путь - абсолютный, указан через /
		if (substr($path, 0, 1)=="/") $path = substr($path, 1, strlen($path));
		
		if (!strstr($path, "http://")) $path =$modx->config['base_path'].$path;
		
		if (!file_exists($path) && !strstr($path, "http://")) return false;
		
		if ($this->drconfig['allow_from_allremote'] &&  strstr($path, "http://")) return true;
		
		$path = dirname($path);
		
		if (strstr($path, "assets/drgalleries")) return true;
		
		if (!empty($this->drconfig['allow_from'])) 
		 {
		 	$pathArray =$this->drconfig['allow_from'];
		 	$mode = "allow";
		 }
		 else{
		 	$pathArray = $this->drconfig['deny_from'];
		 	$mode = "deny";
		 }
		 
		foreach($pathArray as $p)
		{
			if (substr($p,strlen($p)-1,1) == "/") $p = substr($p,0 ,strlen($p)-1);
			if (substr($path,0,strlen($p)) == $p || substr($path,0,strlen($modx->config['base_path'].$p)) == $modx->config['base_path'].$p) return $mode == "allow" ? true : false;
		}
		
		return $mode == "allow" ? false : true;
	}
	//-------------------------------------------------------------------------------------------------
	
	function CheckThumbSizes($img)
	{
		
		if ($this->drconfig['thumb_default_sizemode']==4 && $this->drconfig['thumb_default_height']>0 && $this->drconfig['thumb_default_width']>0)
		{
			$this->imgHTMLHeight = $this->drconfig['thumb_default_height'];
			$this->imgHTMLWidth = $this->drconfig['thumb_default_width'];
			return true;
		}
		
		preg_match("/[^-]height *(:|=) *[\"']* *\d+ *[\"']*/",$img,$array);
		$imgHTMLHeight = preg_replace("/[^0123456789]/","",$array[0]);
		if ($imgHTMLHeight>0) $this->imgHTMLHeight = $imgHTMLHeight;
		
		preg_match("/width *(:|=) *[\"']* *\d+ *[\"']*/",$img,$array);
		$imgHTMLWidth = preg_replace("/[^0123456789]/","",$array[0]);	
		if ($imgHTMLWidth>0) $this->imgHTMLWidth = $imgHTMLWidth;
		
		if ($imgHTMLHeight>0 && $imgHTMLWidth>0) 
		{
			return true;
		}
		
		if ($imgHTMLHeight == 0 && $imgHTMLWidth == 0 && $this->drconfig['thumb_default_sizemode']==1 && $this->drconfig['thumb_default_height']>0 && $this->drconfig['thumb_default_width']>0)
		{
			$this->imgHTMLHeight = $this->drconfig['thumb_default_height'];
			$this->imgHTMLWidth = $this->drconfig['thumb_default_width'];
			return true;
		}
		
		if ($imgHTMLWidth == 0 && $this->drconfig['thumb_default_sizemode']==2 && $this->drconfig['thumb_default_width']>0)
		{
			$this->imgHTMLWidth = $this->drconfig['thumb_default_width'];
			return true;
		}
		
		if ($imgHTMLHeight == 0 && $this->drconfig['thumb_default_sizemode']==3 && $this->drconfig['thumb_default_height']>0)
		{
			$this->imgHTMLHeight = $this->drconfig['thumb_default_height'];
			return true;
		}
		
		if ($imgHTMLHeight>0 || $imgHTMLWidth>0)
		{
			return true;
		}
		
		return false;

	}
	
	//-------------------------------------------------------------------------------------------------
	/*
	Удаляем метки из выходного кода
	*/
	function ReplaceLabels($input)
	{
		foreach ($this->Labels as $v)
		{
			$input = str_replace(" ".$v, "", $input);
			$input = str_replace($v." ", "", $input);
			$input = str_replace($v, "", $input);
		}
		return $input;
	}
	//-------------------------------------------------------------------------------------------------
	/*
	Поиск меток в изображении
	*/
	function CheckForLabels($img)
	{
		foreach ($this->Labels as $v)
		{
			preg_match("/".$v."/",$img,$match);
			//preg_match("/title) *= *[\"|']([^\"']*)[\"']/",$img,$array);	
			
			$this->label[$v] = $match[0] == $v ? TRUE : FALSE;
			//$this->label[$v] = (strstr($array[1], $v)) ? TRUE : FALSE;
		}
	}

	//-------------------------------------------------------------------------------------------------
	
	function ProcessContent($o)
	{
		global $modx;
		
		preg_match_all("/<img[^>]*>/", $o, $imgs, PREG_PATTERN_ORDER); 
		
		for($n=0;$n<count($imgs[0]);$n++){
	
			$currentImgPath = preg_replace("/^.+src=('|\")/i","",$imgs[0][$n]);
			$currentImgPath = preg_replace("/('|\").*$/i","",$currentImgPath);
			$currentImgPath = str_replace($modx->config[site_url], "", $currentImgPath);
			$currentImgPath = urldecode($currentImgPath);

			preg_match('~\[(\+|\*|\()([^:\+\[\]]+)([^\[\]]*?)(\1|\))\]~s', $currentImgPath, $matches);
			if (!empty($matches)) continue;
			
			// Ищем метки
			$this->CheckForLabels($imgs[0][$n]);
								
			//проверяем, можно ли для изображения в его папке использовать плагин, проверяем метку drskip, проверяем расширение файла

			if ($this->checkPath($currentImgPath) && !$this->label["drskip"] && $this->CheckAllowedExt($currentImgPath) && (($this->drconfig['lightbox_mode'] == 1 && $this->label["drlightbox"]) || $this->drconfig['lightbox_mode'] == 2)){									
				
				$img = strtolower($imgs[0][$n]);
				
				// проверяем, заданы ли у изображения height или width
				if ($this->CheckThumbSizes($img)){
					
					$imgf =$imgs[0][$n];

					preg_match("/^.+(src|Src|SRC)=('|\")/",$imgf,$lien_g);
					$imgf = preg_replace("/^.+src=('|\")/i","",$imgf);
					preg_match("/('|\").*$/",$imgf,$lien_d);
					
					// Генеруем тумбу
					$currentImgPath = $this->PrepareImg($currentImgPath);
					$this->imgWidth = $this->imgHTMLWidth;
					$this->imgHeight = $this->imgHTMLHeight;
					$this->mode = "thumb";
					$this->thumbPath = $this->Process();
				
					/*
					thumbImgTag - HTML-код тега img для тумбы
					thumbWidth, thumbHeight - финальные размеры сгенерированной тумбы
					*/
					$HTML = $lien_g[0].$this->thumbPath.$lien_d[0];
					$this->thumbWidth = $this->imgWidth;
					$this->thumbHeight = $this->imgHeight;
					
					//if ($this->label["drthumbonly"]) 
					{
						$s = "/(width *= *[\"|'])([^\"']*)([\"'])/i";
						$r = "\${1}{$this->thumbWidth}\${3}";
						$HTML = preg_replace($s,$r, $HTML);	
						$s = "/(height *= *[\"|'])([^\"']*)([\"'])/i";
						$r = "\${1}{$this->thumbHeight}\${3}";
						$HTML = preg_replace($s,$r, $HTML);							
					}
					
					// Генерируем большое изображение и lightbox HTML-код
					if (!$modx->isBackend() && !$this->label["drthumbonly"]){	
						
						$size = getimagesize($modx->config['base_path'].$currentImgPath);
						$currentImgWidth = $size[0];
						$currentImgHeight = $size[1];
				
						if ($currentImgWidth > $this->imgHTMLWidth || $currentImgHeight > $this->imgHTMLHeight){
							
							$this->originalPath = $currentImgPath;
							$this->originalWidth =$currentImgWidth;
							$this->originalHeight =$currentImgHeight;
							$this->bigPath = $currentImgPath;
							$this->bigWidth = $currentImgWidth;
							$this->bigHeight = $currentImgHeight;
							
							if ($currentImgWidth > $this->drconfig['big_width'] || $currentImgHeight > $this->drconfig['big_height']){
								// Генерируем большое изображение
								$this->PrepareImg($currentImgPath);
								$this->imgWidth = $this->drconfig['big_width'];
								$this->imgHeight = $this->drconfig['big_height'];
								$this->mode = "big";
								$this->bigPath = $this->Process();
								$this->bigWidth = $this->imgWidth;
								$this->bigHeight = $this->imgHeight;
							} else {					
								$this->bigPath = $currentImgPath;
							}
							// Генерируем HTML-код lightbox-ссылки
							$HTML = $this->ParseTemplate($imgs[0][$n], $n);
						}
					}
					$o = str_replace($imgs[0][$n],$HTML,$o);	
					$this->imgCounter++;
				}
			}
		}
		if (!$modx->isBackend()) $o = $this->ReplaceLabels($o);
		$this->output = $o;
	}
	
	//-------------------------------------------------------------------------------------------------
	/*
	Парсим шаблон, собираем выходной код
	*/
	function ParseTemplate($tpl, $currId)
	{
		global $modx, $_lang;
		
		if (!class_exists('DRChunkie')) {
			$chunkieclass = $modx->config['base_path'].DIRECTRESIZE_PATH.'includes/chunkie.class.inc.php';
			if (file_exists($chunkieclass)) {
				include_once $chunkieclass;
			}
		}	
		
		$drtemplate = new DRChunkie($this->drconfig['tpl']);
		
		/*
		Обрабатываем аттрибуты исходного изображения, помещаем их в плейсхолдеры
		*/
		$HTMLattr = array(
			"alt" 		=>	"alt|Alt|ALT", 
			"title"		=>	"title|Title|TITLE", 
			"class"		=>	"class|Class|CLASS",
			"valign"	=>	"valign|Valign|VALIGN",
			"align"		=>	"align|align|ALIGN",
			"style"		=>	"style|Style|STYLE",
		);
		
		foreach ($HTMLattr as $attr_k => $attr_v)
		{
			preg_match("/(".$attr_v.") *= *[\"|'][^\"']*[\"']/",$tpl,$array);	
			if (!empty($array[0]))
			{
				$tmp = preg_replace("/".$attr_k." *= *[\"|']/i","",$array[0]);
				$tmp = preg_replace("/[\"']*/i","",$tmp);
				$tpldata[$attr_k] = trim($tmp);
				
			}
		}
		
		$tpldata['id'] = $currId;
		$tpldata['thumbWidth'] =$this->thumbWidth;
		$tpldata['thumbHeight'] =$this->thumbHeight;
		$tpldata['thumbPath'] = $this->thumbPath;
		
		$tpldata['bigWidth'] =$this->bigWidth;
		$tpldata['bigHeight'] =$this->bigHeight;
		$tpldata['bigPath'] = $this->bigPath;
		
		$tpldata['originalPath'] = isset($this->originalremotePath) ? $this->originalremotePath : $this->originalPath;
		$tpldata['originalWidth'] =$this->originalWidth;
		$tpldata['originalHeight'] =$this->originalHeight;
		$tpldata['originalFilename'] =str_replace("big_", "", basename($this->bigPath));
		
		//$tpldata['originalremotePath'] = $this->originalremotePath;
		
		if (strstr($drtemplate->template, "[+dr.originalFilesize+]")) $tpldata['originalFilesize'] =  $this->ParseFilesize(filesize($modx->config['base_path'].$this->originalPath));
		if (strstr($drtemplate->template, "[+dr.thumbFilesize+]")) $tpldata['thumbFilesize'] =  $this->ParseFilesize(filesize($modx->config['base_path'].$this->thumbPath));
		if (strstr($drtemplate->template, "[+dr.bigFilesize+]")) $tpldata['bigFilesize'] =  $this->ParseFilesize(filesize($modx->config['base_path'].$this->bigPath));
		
		$drtemplate->addVar('dr', $tpldata);
		
		unset($this->thumbWidth, $this->thumbHeight, $this->thumbPath, $this->bigWidth, $this->bigHeight, $this->bigPath, $this->originalPath, $this->originalWidth, $this->originalHeight, $this->originalFilename, $this->originalremotePath);
		
		return $drtemplate->Render();
	}
	//-------------------------------------------------------------------------------------------------
	function ParseFilesize($size)
	{
		global $_lang;
		
		if ($size == 0) return;
		
		if($size < 1024)
				 $size = $size.' '.$_lang['filesize_b'];
		else if($size >= 1024 && $size < 1024*1024)
		{
				 $size = sprintf('%01.2f',$size/1024).' '.$_lang['filesize_Kb'];
		}
		else if($size >= (1024*1024) && $size < 1024*1024*1024)
		{
				 $size = sprintf('%01.2f',$size/(1024*1024)).' '.$_lang['filesize_Mb'];
		}
		else 
				$size = sprintf('%01.2f',$size/(1024*1024*1024)).' '.$_lang['filesize_Tb'];
		
		return $size;
	} 	
	//-------------------------------------------------------------------------------------------------
	/*
	Проверяем, существует ли папка, если нет - создаем ее в зависимости от заданных параметров (стандартными средствами PHP или чере FTP-команды)
	*/
	function CreateDir()
		{
			global $modx;
			
			$path_to_gal = "assets/drgalleries/".$this->drconfig['docID']."/";
			
			if (is_dir($modx->config['base_path'].$path_to_gal)) return $path_to_gal;
			
			if(!$this->drconfig['use_ftp_commands']) {    
				$old_umask = umask(0);
				if(!mkdir($modx->config['base_path'].$path_to_gal, 0777)) {
					$output = 'Directory creation failed!'; 
					return;
				}
				umask($old_umask);
			} else {
				$connect = ftp_connect($this->drconfig['ftp_server'], $this->drconfig['ftp_port']);
				if (!$connect) {
					$output = 'Connection to FTP failed.'; 
					return;
				}
				$login = ftp_login($connect, $this->drconfig['ftp_user'], $this->drconfig['ftp_pass']);
				if (!$login) {
					$output = 'Could not login to FTP.'; 
					return;	
				}
				$changeDir = ftp_chdir($connect, $this->drconfig['ftp_base_dir'].$path_to_gal);
				if (!$changeDir) {
					$output = 'Could not change directory to: '.$this->drconfig['ftp_base_dir'].$path_to_gal;
					return;
				}
				$makeDir = ftp_mkdir($connect, $this->drconfig['docID']);
				if (!$makeDir) {
					$output = 'Could not created directory.';
					return;
				}
				$old_umask = umask(0);
				$setPerm = ftp_site($connect, 'CHMOD 0777 /'.$path_to_gal);
				if (!$setPerm) {
					$output = 'Could not set permissions: '.'CHMOD 0777 /'.$path_to_gal;
				}
				umask($old_umask);
				ftp_close($connect);
			}
			return $path_to_gal;
		}
					
	//-------------------------------------------------------------------------------------------------
	/*
	Подключаем JS и CSS от Maxigallery.
	*/
	function RegisterMGpacks($maxigallery_js_packs)
	{
		global $modx, $_lang;
		
		$scripts = array("slimbox", "slidebox", "lightboxv2");
		if (in_array($maxigallery_js_packs, $scripts)){
		$scripts_codes = 
		array(
			"slimbox" => array(
							"js" => array(
											MODX_BASE_URL."assets/snippets/maxigallery/slimbox/js/mootools.js",
											MODX_BASE_URL."assets/snippets/maxigallery/slimbox/js/slimbox_lang_{$_lang['lang']}.js",
											MODX_BASE_URL."assets/snippets/maxigallery/slimbox/js/slimbox.js"
										),
							
							"css" => array(
											'<link rel="stylesheet" href="'.MODX_BASE_URL.'assets/snippets/maxigallery/slimbox/css/slimbox.css" type="text/css" media="screen" />'
										)
						),
			"slidebox" => array(
							"js" => array(
											MODX_BASE_URL."assets/snippets/maxigallery/slidebox/slidebox_setup.js",
											MODX_BASE_URL."assets/snippets/maxigallery/slidebox/slidebox_lang_{$_lang['lang']}.js",
											MODX_BASE_URL."assets/snippets/maxigallery/slidebox/prototype.js",
											MODX_BASE_URL."assets/snippets/maxigallery/slidebox/slidebox.js"
										),
							
							"css" => array(
											'<link rel="stylesheet" href="'.MODX_BASE_URL.'assets/snippets/maxigallery/slidebox/style.css" type="text/css" media="screen" />',
											'
			<!--[if gte IE 5.5]>
			<![if lt IE 7]>
			<style type="text/css">
			* html #overlay{
			background-color: #333;
			back\ground-color: transparent;
			background-image: url('.MODX_BASE_URL.'assets/snippets/maxigallery/slidebox/blank.gif);
			filter: progid:DXImageTransform.Microsoft.AlphaImageLoader (src="'.MODX_BASE_URL.'assets/snippets/maxigallery/slidebox/overlay.png", sizingMethod="scale");
			</style>
			<![endif]>
			<![endif]-->'
										)
						),
			"lightboxv2" => array(
							"js" => array(
											MODX_BASE_URL."assets/snippets/maxigallery/lightboxv2/js/lightbox_setup.js",
											MODX_BASE_URL."assets/snippets/maxigallery/lightboxv2/js/lightbox_lang_{$_lang['lang']}.js",
											MODX_BASE_URL."assets/snippets/maxigallery/lightboxv2/js/prototype.js",
											MODX_BASE_URL."assets/snippets/maxigallery/lightboxv2/js/scriptaculous.js?load=effects",
											MODX_BASE_URL."assets/snippets/maxigallery/lightboxv2/js/lightbox.js"
										),
							
							"css" => array(
											'<link rel="stylesheet" href="'.MODX_BASE_URL.'assets/snippets/maxigallery/lightboxv2/css/lightbox.css" type="text/css" media="screen" />'
										)
						)
		);
						
		foreach ($scripts_codes[$maxigallery_js_packs][js] as $v)
		if (!$modx->loadedjscripts[$v]) $header .="\n".'<script type="text/javascript" src="'.$v.'"></script>';

		foreach ($scripts_codes[$maxigallery_js_packs][css] as $v)
		if (!$modx->loadedjscripts[$v]) $header .= "\n".$v;
		
		return $header;	
		}
	}
}

//-------------------------------------------------------------------------------------------------
//function to convert gif to png
function gif2png($name){
	$src=imagecreatefromgif($name);
	//calculate size for the image
	$src_size = getimagesize($name);
	//create blank destination image
	$dest=imagecreate($src_size[0],$src_size[1]);
	//delete gif image
	unlink($name);
	$name = str_replace(".gif", ".png", $name);
	//resize the image
	if(function_exists('imagecopyresampled')){
		imagecopyresampled($dest,$src,0,0,0,0,$src_size[0],$src_size[1],$src_size[0],$src_size[1]);
	}else{
		imagecopyresized($dest,$src,0,0,0,0,$src_size[0],$src_size[1],$src_size[0],$src_size[1]);
	}
	//create new image
	imagepng($dest,$name);
	@imagedestroy($src);
	@imagedestroy($dest);
	return $name;
}

//-------------------------------------------------------------------------------------------------

function ConvertFromBackend($o, $escape= true)
{
	$reg = "/<img[^>]*>/";
	preg_match_all($reg, $o, $imgs, PREG_PATTERN_ORDER);
	for($n=0;$n<count($imgs[0]);$n++)
	{
		$lien_img = preg_replace("/^.+src=('|\")/i","",$imgs[0][$n]);
		$lien_img = preg_replace("/('|\").*$/i","",$lien_img);
		$lien_img = str_replace($modx->config[site_url], "", $lien_img);
		$lien_img = urldecode($lien_img);
		$lien_img = str_replace("---", "://", $lien_img);
		$lien_img = str_replace("--", "/", $lien_img);
		$imgf = $imgs[0][$n];
		preg_match("/^.+(src|Src|SRC)=('|\")/",$imgf,$lien_g);
		$imgf = preg_replace("/^.+src=('|\")/i","",$imgf);				
		preg_match("/('|\").*$/",$imgf,$lien_d);
		
		$thumbImgTag = $lien_g[0].$lien_img.$lien_d[0];
		$o = str_replace($imgs[0][$n],$thumbImgTag,$o);	
	}
	$o = str_replace(DIRECTRESIZE_GALLERYDIR.$_REQUEST[id]."/wysiwyg_","", $o);
	if ($escape) $o = mysql_real_escape_string($o);
	return $o;
	
}
//-------------------------------------------------------------------------------------------------
// ggarciaa at gmail dot com (04-July-2007 01:57)	
function SureRemoveDir($dir, $DeleteMe = false) {
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..') continue;
        if (!@unlink($dir.'/'.$obj)) SureRemoveDir($dir.'/'.$obj, false);
    }
    if ($DeleteMe){
        closedir($dh);
        @rmdir($dir);
    }
}
//-------------------------------------------------------------------------------------------------

function ClearDRCache($clearCache = 0)
{
	global $modx;
	
	if ($clearCache == 0 ) return;
	
	if ($clearCache == 1 && isset($_REQUEST[id])) 
	{
		SureRemoveDir($modx->config['base_path']."assets/drgalleries/".$_REQUEST[id]);
	}
	
	if ($clearCache == 2) 
	{
		SureRemoveDir($modx->config['base_path']."assets/drgalleries");
	}
	
}
//-------------------------------------------------------------------------------------------------

function RenderOnFrontend($o, $config)
{
	global $modx, $_lang;

	if (isset($config)) include_once $modx->config['base_path'].DIRECTRESIZE_PATH."configs/$config.config.php";
	
	$drconfig['allow_from'] = isset($allow_from) ? $allow_from : (isset($deny_from) ? NULL : "assets/images");
	$drconfig['deny_from'] = isset($deny_from) && !isset($allow_from) ? $deny_from : NULL;
	$drconfig['resize_method'] = isset($resize_method) ? $resize_method : 3;
	$drconfig['big_quality'] = isset($big_quality) ? $big_quality : 80;
	$drconfig['thumb_quality'] = isset($thumb_quality) ? $thumb_quality : 80;
	$drconfig['wysiwyg_quality'] = isset($wysiwyg_quality) ? $wysiwyg_quality : 40;
	$drconfig['lightbox_mode'] =  isset($lightbox_mode) ? $lightbox_mode : 1;
	
	$drconfig['thumb_default_width'] = isset($thumb_default_width) ? $thumb_default_width : 0;
	$drconfig['thumb_default_height'] = isset($thumb_default_height) ? $thumb_default_height : 0;
	$drconfig['thumb_default_sizemode'] = isset($thumb_default_sizemode) ? $thumb_default_sizemode : 1;
	
	$drconfig['big_width'] = isset($big_width) ? $big_width : 800;
	$drconfig['big_height'] = isset($big_height) ? $big_height : 600;
	$drconfig['remote_refresh_time'] = isset($remote_refresh_time) ? $remote_refresh_time : 60;
	$drconfig['thumb_watermark']['use_watermark'] = (isset($thumb_use_watermark)) ? $thumb_use_watermark : false; // [ true | false ]
	$drconfig['thumb_watermark']['watermark_txt'] = (isset($thumb_watermark_txt)) ? $thumb_watermark_txt : "Copyright ".date("Y"); // [ text ]
	$drconfig['thumb_watermark']['watermark_txt_color'] = (isset($thumb_watermark_txt_color)) ? $thumb_watermark_txt_color : "FFFFFF";	// [ RGB Hexadecimal ]
	$drconfig['thumb_watermark']['watermark_font'] = (isset($thumb_watermark_font)) ? $thumb_watermark_font : 1; // [ 1 | 2 | 3 | 4 | 5 ]
	$drconfig['thumb_watermark']['watermark_txt_vmargin'] = (isset($thumb_watermark_txt_vmargin)) ? $thumb_watermark_txt_vmargin : 2; // [ number ]
	$drconfig['thumb_watermark']['watermark_txt_hmargin'] = (isset($thumb_watermark_txt_hmargin)) ? $thumb_watermark_txt_hmargin : 2;	// [ number ]
	$drconfig['thumb_watermark']['watermark_img'] = (isset($thumb_watermark_img)) ? $thumb_watermark_img : DIRECTRESIZE_PATH.'images/watermark.png'; //path 
	$drconfig['thumb_watermark']['watermark_type'] = (isset($thumb_watermark_type)) ? $thumb_watermark_type : "text"; // [ text | image ]
	$drconfig['thumb_watermark']['watermark_valign'] = (isset($thumb_watermark_valign)) ? $thumb_watermark_valign : "bottom"; // [ top | center | bottom ]
	$drconfig['thumb_watermark']['watermark_halign'] = (isset($thumb_watermark_halign)) ? $thumb_watermark_halign : "right"; // [ left | center | right ]
	$drconfig['big_watermark']['use_watermark'] = (isset($big_use_watermark)) ? $big_use_watermark : false; // [ true | false ]
	$drconfig['big_watermark']['watermark_txt'] = (isset($big_watermark_txt)) ? $big_watermark_txt : "Copyright ".date("Y")." ".$modx->config['site_name']; // [ text ]
	$drconfig['big_watermark']['watermark_txt_color'] = (isset($big_watermark_txt_color)) ? $big_watermark_txt_color : "FFFFFF";	// [ RGB Hexadecimal ]
	$drconfig['big_watermark']['watermark_font'] = (isset($big_watermark_font)) ? $big_watermark_font : 3; // [ 1 | 2 | 3 | 4 | 5 ]
	$drconfig['big_watermark']['watermark_txt_vmargin'] = (isset($big_watermark_txt_vmargin)) ? $big_watermark_txt_vmargin : 10; // [ number ]
	$drconfig['big_watermark']['watermark_txt_hmargin'] = (isset($big_watermark_txt_hmargin)) ? $big_watermark_txt_hmargin : 10;	// [ number ]
	$drconfig['big_watermark']['watermark_img'] = (isset($big_watermark_img)) ? $big_watermark_img : DIRECTRESIZE_PATH.'images/watermark.png'; //path 
	$drconfig['big_watermark']['watermark_type'] = (isset($big_watermark_type)) ? $big_watermark_type : "text"; // [ text | image ]
	$drconfig['big_watermark']['watermark_valign'] = (isset($big_watermark_valign)) ? $big_watermark_valign : "bottom"; // [ top | center | bottom ]
	$drconfig['big_watermark']['watermark_halign'] = (isset($big_watermark_halign)) ? $big_watermark_halign : "right"; // [ left | center | right ]
	$drconfig['use_ftp_commands'] = (isset($use_ftp_commands)) ? $use_ftp_commands : false; // [ true | false ]
	$drconfig['ftp_server'] = (isset($ftp_server)) ? $ftp_server : "ftp.yourserver.fi"; // [ text ]
	$drconfig['ftp_port'] = (isset($ftp_port)) ? $ftp_port : 21; // [ number ]
	$drconfig['ftp_user'] = (isset($ftp_user)) ? $ftp_user : "username"; // [ text ]
	$drconfig['ftp_pass'] = (isset($ftp_pass)) ? $ftp_pass : "password"; // [ text ]
	$drconfig['ftp_base_dir'] = (isset($ftp_base_dir)) ? $ftp_base_dir : "/"; // [ text ]
	
	$drconfig['docID'] = $modx->isBackend() ? $_REQUEST[id] : $modx->documentIdentifier;
	$drconfig['tpl'] = (isset($tpl)) ? $tpl : '';
	$drconfig['allow_from_allremote'] =  isset($allow_from_allremote) ? $allow_from_allremote : FALSE;
	
	if (!empty($drconfig['allow_from']))
	{
		$drconfig['allow_from'] = str_replace(" ", "", $drconfig['allow_from']);
		$drconfig['allow_from'] = urldecode($drconfig['allow_from']);
		$drconfig['allow_from'] = explode(",", $drconfig['allow_from']);
	}else
		if (!empty($drconfig['deny_from']))
		{
			$drconfig['deny_from'] = str_replace(" ", "", $drconfig['deny_from']);
			$drconfig['deny_from'] = urldecode($drconfig['deny_from']);
			$drconfig['deny_from'] = explode(",", $drconfig['deny_from']);
		}
	
	if (!$modx->isBackend())
	{
		$language = isset($language)? $language:$modx->config['manager_language'];
		include_once(DIRECTRESIZE_PATH."lang/english.inc.php");
		if($language!="english" && $language!='') 
		{
			if(file_exists(DIRECTRESIZE_PATH ."lang/".$language.".inc.php")) include_once DIRECTRESIZE_PATH ."lang/".$language.".inc.php";
		}
	}
	
	$direct = new directResize($drconfig, $o);
            
	if (isset($maxigallery_jscss_packs)) $header=$direct->RegisterMGpacks($maxigallery_jscss_packs);				
	
	if (isset($header) && !$modx->isBackend() && $direct->imgCounter>0) 
	{
		$head = strstr($direct->output, "</head>") ? "</head>" : "</HEAD>";
		$direct->output = str_replace($head, $header."\n".$head, $direct->output);
	}
	return $direct->output;
}

?>