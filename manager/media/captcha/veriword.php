<?php
if(!isset($modx))
{
	define('MODX_API_MODE',true);
	$base_path = str_replace('\\','/',realpath('../../')) . '/';
	require_once("{$base_path}index.php");
	$modx->db->connect();
	$modx->getSettings();
}
if(!isset($vword))
{
	$vword = new VeriWord(135,43);
	$vword->output_image();
	$vword->destroy_image();
}

#captchaClass.php file below

## v1 Stable ##

######
## Verification Word
######
## This class generate an image with random text
## to be used in form verification. It has visual
## elements design to confuse OCR software preventing
## the use of BOTS.
##
#######
## Author: Huda M Elmatsani
## Email:   justhuda at netrada.co.id
##
## 25/07/2004
#######
## Copyright (c) 2004 Huda M Elmatsani All rights reserved.
## This program is free for any purpose use.
########
class VeriWord {
	/* path to font directory*/
	var $dir_font;
	/* path to background image directory*/
	var $dir_noise;
	var $word;
	var $im_width;
	var $im_height;
	var $words;

	function VeriWord($w=200, $h=80)
	{
		global $modx;
		$vw_path = str_replace('\\','/',dirname(__FILE__)) . '/';
		$this->dir_font  = 'ttf/';
		$this->dir_noise = $vw_path . 'noises/';
		$this->word = '';
		$this->words = $modx->config['captcha_words'];
		$this->im_width         = $w;
		$this->im_height        = $h;
		/* create session to set word for verification */
		$this->set_veriword();
		$this->dir_font = $vw_path . $this->dir_font;
	}

	function set_veriword()
	{
		/* create session variable for verification,
		you may change the session variable name */
		$this->word             = $this->pick_word();
		$_SESSION['veriword']   = $this->word;
	}

	function output_image()
	{
		/* output the image as jpeg */
		$this->draw_image();
		header('Content-type: image/jpeg');
		imagejpeg($this->im);
	}

	function pick_word()
	{
		$arr_words = explode(',', $this->words);
		/* pick one randomly for text verification */
		return (string) $arr_words[array_rand($arr_words)].mt_rand(10,999);
	}

	function draw_text()
	{
		$files = scandir($this->dir_font);
		$fontstmp = array();
		foreach ($files as $file)
		{
			if(substr($file, -4) == '.ttf')
			{
				$fontstmp[] = $this->dir_font.$file;
			}
		}
		$text_font = (string) $fontstmp[array_rand($fontstmp)];
		
		/* angle for text inclination */
		$text_angle = mt_rand(-9,9);
		/* initial text size */
		$text_size  = 30;
		/* calculate text width and height */
		$box        = imagettfbbox ( $text_size, $text_angle, $text_font, $this->word);
		$text_width = $box[2]-$box[0]; //text width
		$text_height= $box[5]-$box[3]; //text height
		
		/* adjust text size */
		$text_size  = round((20 * $this->im_width)/$text_width);
		
		/* recalculate text width and height */
		$box        = imagettfbbox ( $text_size, $text_angle, $text_font, $this->word);
		$text_width = $box[2]-$box[0]; //text width
		$text_height= $box[5]-$box[3]; //text height
		
		/* calculate center position of text */
		$text_x         = ($this->im_width - $text_width)/2;
		$text_y         = ($this->im_height - $text_height)/2;
		
		/* create canvas for text drawing */
		$im_text        = imagecreate ($this->im_width, $this->im_height);
		$bg_color       = imagecolorallocate ($im_text, 255, 255, 255);
		
		/* pick color for text */
		$text_color     = imagecolorallocate ($im_text, 10, 10, 10);
		
		/* draw text into canvas */
		imagettftext(
			$im_text,
			$text_size,
			$text_angle,
			$text_x,
			$text_y,
			$text_color,
			$text_font,
			$this->word);
		
		/* remove background color */
		imagecolortransparent($im_text, $bg_color);
		return $im_text;
		imagedestroy($im_text);
	}

	function draw_image()
	{
		/* pick one background image randomly from image directory */
		$img_file       = $this->dir_noise."noise".mt_rand(1,4).".jpg";
		
		/* create "noise" background image from your image stock*/
		$noise_img      = @imagecreatefromjpeg ($img_file);
		$noise_width    = imagesx($noise_img);
		$noise_height   = imagesy($noise_img);
		
		/* resize the background image to fit the size of image output */
		$this->im       = imagecreatetruecolor($this->im_width,$this->im_height);
		imagecopyresampled(
			$this->im,
			$noise_img,
			0, 0, 0, 0,
			$this->im_width,
			$this->im_height,
			$noise_width,
			$noise_height);
		
		/* put text image into background image */
		imagecopymerge(
			$this->im,
			$this->draw_text(),
			0, 0, 0, 0,
			$this->im_width,
			$this->im_height,
			70);
		
		return $this->im;
	}
	
	function destroy_image()
	{
		imagedestroy($this->im);
	}
}
