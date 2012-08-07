<?php
/**
 * Commonly used login functions
 * Writen By Raymond Irving April, 2005
 *
 */

    // extract declarations
    function webLoginExtractDeclarations(&$html){
        $declare  = array();
        if(strpos($html,"<!-- #declare:")===false) return $declare;
        $matches= array();
        if (preg_match_all("/<\!-- \#declare\:(.*)[^-->]?-->/i",$html,$matches)) {    
            for($i=0;$i<count($matches[1]);$i++) {
                $tag = explode(" ",$matches[1][$i]);
                $tagname=trim($tag[0]);
                $tagvalue=trim($tag[1]);
                $declare[$tagname] = $tagvalue;
            }
            // remove declarations
            $html = str_replace($matches[0],"",$html);
        }
        return $declare;
    }

    // show javascript alert    
    function webLoginAlert($msg,$alerttpl=''){
    	global $modx;
        if(!empty($alerttpl)){
          $output = $modx->parseChunk($alerttpl, array("msg"=>$msg), '[+', '+]');
        }else{
          $output = "<script>window.setTimeout(\"alert('".addslashes($modx->db->escape($msg))."')\",10);</script>";
        }
        return $output;
    }

    // generate new password
    function webLoginGeneratePassword($length = 10) {
        $allowable_characters = "abcdefghjkmnpqrstuvxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        $ps_len = strlen($allowable_characters);
        mt_srand((double)microtime()*1000000);
        $pass = "";
        for($i = 0; $i < $length; $i++) {
            $pass .= $allowable_characters[mt_rand(0,$ps_len-1)];
        }
        return $pass;
    } 
    
    function sendMail($subject,$email,$body){
      global $modx;
      $charset = $modx->config['modx_charset'];
      $site_name = $modx->config['site_name'];
      $adminEmail = $modx->config['emailsender'];
      require_once(MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php");
      $mail = new PHPMailer();
      $mail->IsMail();
      $mail->IsHTML(false);
      $mail->CharSet = $charset;
      $mail->From	= $adminEmail;
      $mail->FromName	= $site_name;
      $mail->Subject	= $subject;
      $mail->Body	= $body;
      $mail->AddAddress($email);
      if(!$mail->send()){
        echo $mail->ErrorInfo;
        exit;
      }
    }
    
    
    // Send new password to the user
    function webLoginSendNewPassword($email,$uid,$pwd,$ufn){
        global $modx, $site_url;
        $mailto = $modx->config['mailto'];
        $websignupemail_message = $modx->config['websignupemail_message'];    
        $emailsubject = $modx->config['emailsubject'];
        $emailsender = $modx->config['emailsender']; 
        $site_name = $modx->config['site_name'];
        $site_start = $modx->config['site_start'];
        $message = sprintf($websignupemail_message, $uid, $pwd); // use old method
        // replace placeholders
        $message = str_replace("[+uid+]",$uid,$message);
        $message = str_replace("[+pwd+]",$pwd,$message);
        $message = str_replace("[+ufn+]",$ufn,$message);
        $message = str_replace("[+sname+]",$site_name,$message);
        $message = str_replace("[+semail+]",$emailsender,$message);
        $message = str_replace("[+surl+]",$site_url,$message);
        /*
        if (!ini_get('safe_mode')) $sent = mail($email, $emailsubject, $message, "Content-type: text/plain; charset=UTF-8"."\r\n"."From: ".$emailsender."\r\n"."X-Mailer: Content Manager - PHP/".phpversion(), "-f {$emailsender}");
        else $sent = mail($email, $emailsubject, $message, "Content-type: text/plain; charset=UTF-8"."\r\n"."From: ".$emailsender."\r\n"."X-Mailer: Content Manager - PHP/".phpversion());
        if (!$sent) webLoginAlert($langTXT[16]." $mailto",$alerttpl);
        */
        sendMail($emailsubject,$email,$message);
        
        return true;
    }
    
    function preserveUrl($docid = '', $alias = '', $array_values = array(), $suffix = false) {
		global $modx;
		$array_get = $_GET;
		$urlstring = array();
	
		unset($array_get["id"]);
		unset($array_get["q"]);
		unset($array_get["webloginmode"]);
	
		$array_url = array_merge($array_get, $array_values);
		foreach ($array_url as $name => $value) {
			if (!is_null($value)) {
				if(!is_array($value)){
				  $urlstring[] = urlencode($name) . '=' . urlencode($value);
				}else{
				  foreach ($value as $val){
            $urlstring[] = urlencode($name) . '[]=' . urlencode($val);
          }
          unset($key,$val);
				}
        
			}
		}
	
		$url = join('&',$urlstring);
		if ($suffix) {
			if (empty($url)) {
				$url = "?";
			} else {
				$url .= "&";
			}
		}
		return $modx->makeUrl($docid, $alias, $url);
	}

?>