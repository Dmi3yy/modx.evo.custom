<?php
/** author Bumkaka 
*
*   include_once('assets/libs/bmail.php');
	$mail = new bmail('bumkaka@yandex.ru,ruus@yandex.ru');
    $mail->Subject	= "Предзаказ";
    $mail->Body		= $body;
    $mail->send();
*
*
*/


	function bmail($mail_list){
		global $modx;
		include_once MODX_MANAGER_PATH."includes/controls/class.phpmailer.php";
		$mail = new PHPMailer();
		if ($modx->config['email_method'] == 'smtp') {
			$mail->IsSMTP();
			$mail->Host	 	= $modx->config['email_host']; 
			$mail->SMTPAuth = true;	
			$mail->Username = $modx->config['email_smtp_sender']; 
			$mail->Password = $modx->config['email_pass'];
			$mail->From		= $modx->config['email_smtp_sender'];
			//$mail->Port    = $smtpport;
		}else{
			$mail->IsMail();
			$mail->From		= $modx->config['emailsender'];
		}
		$mail->IsHTML( true );					
		$mail->FromName	= $modx->config['site_name'];
		
		$mails = explode(',',$mail_list);
		foreach($mails as $key=>$val) $mail->AddAddress( $val );
		return $mail;
	}

?>