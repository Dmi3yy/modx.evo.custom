<?php
/** 
 * mm_ddResizeImage
 * @version 1.2b (2012-08-30)
 * 
 * Виджет для уменьшения размеров картинок (из TV-параметров).
 * 
 * @uses phpThumb lib 1.7.11-201108081537-beta (http://phpthumb.sourceforge.net/).
 * @uses сниппет ddGetMultipleField 2.10.1, если необходимо работать с множественными полями (для mm_ddMultipleFields).
 * 
 * @events OnBeforeDocFormSave.
 * 
 * @todo replaceFieldVal не умеет работать с множественными полями (для mm_ddMultipleFields)!
 * 
 * @param tvs {comma separated string} - Список ID TV изображений через запятую.
 * @param roles {comma separated string} - Список ID ролей через запятую. По умолчанию: '' (для всех ролей).
 * @param templates {comma separated string} - Список ID шаблонов через запятую. По умолчанию: '' (для всех шаблонов).
 * @param width {integer} - Список ширин создаваемых изображений.
 * @param height {integer} - Список высот создаваемых изображений.
 * @param cropping {string} - Обрезать ли картинки. Возможные значения: '1', '0', 'crop_resized' (уменьшить + обрезать), 'fill_resized' (пропорционально уменьшить, заполнив поля цветом (rulBackground)). По умолчанию: 'crop_resized'.
 * @param suffix {string} - Суффикс для имен создаваемых изображений. По умолчанию: '_ddthumb'.
 * @param replaceFieldVal {0; 1} - Нужно ли переписывать значения полей изображений (TV) на создаваемые изображения. По умолчанию: 0.
 * @param background {string} - Цвет фона (при rulCropping = 'fill_resized'). По умолчанию: '#ffffff'.
 * @param multipleField {0; 1} - Является ли поле множественным (для mm_ddMultipleFields). По умолчанию: '0';
 * @param colNum {integer} - Номер колонки, в которой находится изображение (для mm_ddMultipleFields). По умолчанию: 0.
 * @param splY {string} - Разделитель строк (для mm_ddMultipleFields). По умолчанию: '||'.
 * @param splX {string} - Разделитель колонок (для mm_ddMultipleFields). По умолчанию: '::'.
 * @param num {integer} - Количество строк (для mm_ddMultipleFields). По умолчанию: all.
 * 
 * @link http://code.divandesign.biz/modx/mm_ddresizeimage/1.2b
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

function mm_ddResizeImage($tvs='', $roles='', $templates='', $width = '', $height = '', $cropping = 'crop_resized', $suffix = '_ddthumb', $replaceFieldVal = 0, $background = '#FFFFFF', $multipleField = 0, $colNum = 0, $splY = '||', $splX = '::', $num = 'all'){

	global $modx, $mm_current_page, $tmplvars, $content;
	$e = &$modx->Event;
	
	if(!function_exists('ddCreateThumb')){
		/**
		 * Делает превьюшку
		 * 
		 * @param $thumbData {array}
		 */
		function ddCreateThumb($thumbData){
	
			//Вычислим размеры оригинаольного изображения
			$originalImg = array();
			list($originalImg['width'], $originalImg['height']) = getimagesize($thumbData['originalImage']);
			
			//Пропрорции реального изображения
			$originalImg['ratio'] = $originalImg['width'] / $originalImg['height'];
			
			//Если по каким-то причинам высота не задана
			if ($thumbData['height'] == '' || $thumbData['height'] == 0) $thumbData['height'] = $thumbData['width'] / $originalImg['ratio'];
			//Если по каким-то причинам ширина не задана
			if ($thumbData['width'] == '' || $thumbData['width'] == 0) $thumbData['width'] = $thumbData['height'] * $originalImg['ratio'];
			
			//Если превьюшка уже есть и имеет нужный размер, ничего делать не нужно
			if ($originalImg['width'] == $thumbData['width'] &&
				$originalImg['height'] == $thumbData['height'] &&
				file_exists($thumbData['thumbName'])) return;
			
			$thumb = new phpThumb();
			//Путь к оригиналу
			$thumb->setSourceFilename($thumbData['originalImage']);
			//Качество (для JPEG) = 100
			$thumb->setParameter('q', '100');
			//
			$thumb->setParameter('aoe', 1);
			
			//Если просто нужно обрезать
			if($thumbData['cropping'] == '1'){
				//Ширина превьюшки
				$thumb->setParameter('sw', $thumbData['width']);
				//Высота превьюшки
				$thumb->setParameter('sh', $thumbData['height']);
				
				//Если ширина оригинального изображения больше
				if ($originalImg['width'] > $thumbData['width']){
					//Позиция по оси x оригинального изображения (чтобы было по центру)
					$thumb->setParameter('sx', ($originalImg['width'] - $thumbData['width']) / 2);
				}
				//Если высота оригинального изображения больше
				if ($originalImg['height'] > $thumbData['height']){
					//Позиция по оси y оригинального изображения (чтобы было по центру)
					$thumb->setParameter('sy', ($originalImg['height'] - $thumbData['height']) / 2);
				}
			}else{
				//Ширина превьюшки
				$thumb->setParameter('w', $thumbData['width']);
				//Высота превьюшки
				$thumb->setParameter('h', $thumbData['height']);
				
				//Если нужно уменьшить + отрезать
				if($thumbData['cropping'] == 'crop_resized'){
					$thumb->setParameter('zc', '1');
				//Если нужно пропорционально уменьшить, заполнив поля цветом
				}else if($thumbData['cropping'] == 'fill_resized'){
					//Устанавливаем фон (без решётки)
					$thumb->setParameter('bg', str_replace('#', '', $thumbData['backgroundColor']));
					//Превьюшка должна точно соответствовать размеру и находиться по центру (недостающие области зальются цветом)
					$thumb->setParameter('far', 'c');
				}
			}
			
			//Создаём превьюшку
			$thumb->GenerateThumbnail();
			//Сохраняем в файл
			$thumb->RenderToFile($thumbData['thumbName']);
		}
	}
	
	//Проверим, чтобы было нужное событие, чтобы были заполнены обязательные параметры и что правило подходит под роль
	if ($e->name == 'OnBeforeDocFormSave' &&
		$tvs != '' && ($width != '' || $height != '') &&
		useThisRule($roles, $templates)){
		
		//Получаем tv изображений для данного шаблона
		$tvs = tplUseTvs($mm_current_page['template'], $tvs, 'image', 'id,name');
		
		//Если что-то есть
		if (count($tvs) > 0){
			//Обработка параметров
			$replaceFieldVal = ($replaceFieldVal == '1') ? true : false;
			$multipleField = ($multipleField == '1') ? true : false;
			
			$base_path = $modx->config['base_path'];
			$widgetDir = $base_path.'assets/plugins/managermanager/widgets/ddresizeimage/';
			
			//Подключаем библиотеку EasyPhpThumbnail
			require_once $widgetDir.'phpthumb.class.php';
			
			//Перебираем их
			foreach ($tvs as $tv){
				//Если в значении tv что-то есть
				if (isset($tmplvars[$tv['id']]) && trim($tmplvars[$tv['id']][1]) != ''){
					$image = trim($tmplvars[$tv['id']][1]);
					
					//Если это множественное поле
					if ($multipleField){
						//Получим массив изображений
						$images = $modx->RunSnippet('ddGetMultipleField', array(
							'field' => $image,
							'splY' => $splY,
							'splX' => $splX,
							'num' => ($num == 'all' ? 0 : $num),
							'count' => ($num == 'all' ? 'all' : 1),
							'format' => 'JSON',
							'colNum' => $colNum
						));

						if ($num == 'all'){
							$images = json_decode($images, true);
						}else{
							$images = array(trim(stripcslashes($images), '\'\"'));
						}
					}else{
						//Запишем в массив одно изображение
						$images = array($image);
					}
					
					foreach ($images as $image){
						//Если есть лишний слэш в начале, убьём его
						if (strpos($image, '/') === 0) $image = substr($image, 1);
						
						//На всякий случай проверим, что файл существует
						if (file_exists($base_path.$image)){
							//Полный путь изображения
							$imageFullPath = pathinfo($base_path.$image);
							
							//Имя нового изображения
							$newImageName = $imageFullPath['filename'].$suffix.'.'.$imageFullPath['extension'];
						
							//Делаем превьюшку
							ddCreateThumb(array(
								//Ширина превьюшки
								'width' => $width,
								//Высота превьюшки
								'height' => $height,
								//Фон превьюшки (может понадобиться для заливки пустых мест)
								'backgroundColor' => $background,
								//Режим обрезания
								'cropping' => $cropping,
								//Формируем новое имя изображения (полный путь)
								'thumbName' => $imageFullPath['dirname'].'/'.$newImageName,
								//Ссылка на оригинальное изображение
								'originalImage' => $base_path.$image
							));
							
							//Если нужно заменить оригинальное значение TV на вновь созданное
							if ($replaceFieldVal) $tmplvars[$tv['id']][1] = dirname($tmplvars[$tv['id']][1]).'/'.$newImageName;
						}
					}
				}
			}
		}
	}
}
?>