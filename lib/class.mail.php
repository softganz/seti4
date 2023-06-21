<?php
//-----------------------------------------------------------------------------------------
/**
* SOFTGANZ :: class mail
*
* Copyright (c) 2000-2002 The SoftGanz Group By Panumas Nontapun
* Authors: Panumas Nontapun <webmaster@softganz.com>
* http://www.softganz.com
* ============================================
* This module is to send mail
*
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*
--- Created 2007-01-16
--- Modify   2007-01-16
*/
class mail {
var $varFromName=NULL;
var $varFromEmail=NULL;
var $varReplyToEmail=NULL;
var $varCC=NULL;
var $varBCC=NULL;
var $varXSender=NULL;
var $varReturnPath=NULL;

var $send_message=NULL;

function mail() { }

function FromName($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varFromName = $newValue;
	return $this->varFromName;
}
function FromEmail($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varFromEmail = $newValue;
	return $this->varFromEmail;
}
function ReplyToEmail($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varReplyToEmail = $newValue;
	return $this->varReplyToEmail;
}
function CC($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varCC = $newValue;
	return $this->varCC;
}
function BCC($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varBCC = $newValue;
	return $this->varBCC;
}
function XSender($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varXSender = $newValue;
	return $this->varXSender;
}
function ReturnPath($newValue=NULL) {
	if ( IsSet($newValue) ) $this->varReturnPath = $newValue;
	return $this->varReturnPath;
}
function IsValidEmail($mail=NULL) {
	$email_regex = "|([\xA1-\xFEa-z0-9_\.\-]+)@([\xA1-\xFEa-z0-9_\-]+\.[\xA1-\xFEa-z0-9\-\._\-]+[\.]*[a-z0-9]\??[\xA1-\xFEa-z0-9=]*)|";
	if ( preg_match("|[\s].*|",$mail) ) return false;
	elseif ( preg_match($email_regex,$mail,$out) ) return true;
	else return false;
}

function Test() {
	$fullname = "¹ÒÂËÁÕ";
	$email = "webmaster@softganz.com";
	$mailto = "softganz@hotmail.com";
	$headers .= "From: ".$fullname."<".$email.">\r\n";
	$headers .= "Cc: ".$email."\r\n";
	$headers .= "X-Sender: <order@findinghost.com>\r\n";
	$headers .= "X-Mailer: PHP\r\n"; //mailer
	$headers .= "X-Priority: 3\r\n"; //1 UrgentMessage, 3 Normal
	$headers .= "Return-Path: <support@findinghost.com>\r\n";
	$result = mail($mailto,"·´ÊÍº¡ÒÃÊè§ÍÕ-àÁÅì", "¢éÍ¤ÇÒÁ¨Ò¡¡ÒÃ·´ÊÍºÊè§ ÍÕ-àÁÅì",$headers);
	return;

	$mailto = "softganz@hotmail.com";
	$fullname = "¹ÒÂËÁÕ";
	$email = "webmaster@softganz.com";
	$headers .= "From: ".$fullname."<".$email.">\r\n";
	$headers .= "Cc: softganz@hotmail.com\r\n";
	//	$headers .= "Cc: webmaster@softganz.com\r\n";
	$headers .= "X-Sender: <webmaster@nokkrob.org>\r\n";
	$headers .= "X-Mailer: PHP\r\n"; //mailer
	$headers .= "X-Priority: 3\r\n"; //1 UrgentMessage, 3 Normal
	$headers .= "Return-Path: <webmaster@nokkrob.org>\r\n";
	$mailto = "softganz@yahoo.com,softganz@hotmail.com\r\n";
	$result = mail($mailto,"·´ÊÍº¡ÒÃÊè§ÍÕ-àÁÅì", "¢éÍ¤ÇÒÁ¨Ò¡¡ÒÃ·´ÊÍºÊè§ ÍÕ-àÁÅì",$headers);
	echo "mailto : $mailto<br>title:$title<br>message:$message<br>headers:$headers<br>";
	echo "mail result :".$result;
	return $result;
}

function Send($mailto,$title,$message,$emulate=false,$module=NULL) {
	switch (strtoupper($module)) {
		case 'PHPMAILER' :  $mail_result=$this->Send_by_PHPMailer($mailto,$title,$message,$emulate); break;
		default :
			if (substr($module,0,8)=='https://') {
				$url = $module.'/user/sendmail';
				$data=array('user/sendmail'=>'','mailto'=>$mailto,'title'=>$title,'message'=>$message);
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 3);
				$ret = curl_exec($ch);
				$mail_result=$ret;
			} else {
				$mail_result=$this->Send_by_SMTP($mailto,$title,$message,$emulate);
			}
			break;
	}
	return $mail_result;
}

function Send_by_PHPMailer($mailto,$title,$message,$emulate=false) {
	include_once 'modules/phpmail/class.phpmailer.php';
	$mail = new PHPMailer(); // สร้าง object class ครับ
	$mail->IsHTML(true);

	// Send from Yahoo
	$mail->IsSMTP(); // กำหนดว่าเป็น SMTP นะ
	$mail->SMTPSecure='ssl';
	$mail->Host = 'smtp.mail.yahoo.com'; // กำหนดค่าเป็นที่ mail server ได้เลยครับ
	$mail->Port = 465; // กำหนด port เป็น 465 ตามที่ mail server บอกครับ
	$mail->SMTPAuth = true; // กำหนดให้มีการตรวจสอบสิทธิ์การใช้งาน
	$mail->Username = 'softganznoreply@yahoo.com'; // ต้องมีเมล์ของ mail server ที่สมัครไว้ด้วยนะครับ
	$mail->Password = 'sgnz2010'; // ใส่ password ที่เราจะใช้เข้าไปเช็คเมล์ที่ mail server ล่ะครับ
	$mail->SetFrom('softganznoreply@yahoo.com','Softgnz Supporter');
	$mail->AddReplyTo('support@softganz.com','Softgnz Supporter');
	
	$mail->FromName = 'Softganz Support'; // ชื่อผู้ส่งสักนิดครับ
	$mail->Subject  = $title; // กำหนด subject ครับ
	$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
	$mail->MsgHTML =  $message; // ใส่ข้อความเข้าไปครับ
	$mail->Body = $message;
	$mail->AddAddress($mailto); // ส่งไปที่ใครดีครับ
  
	if(!$mail->Send()) {
		return false;
	} else {
		return true;
	}
}

function Send_by_SMTP($mailto,$title,$message,$emulate=false) {
	$eol="\r\n";
	$date = date( 'r' ,time() + (19 * 60 * 60));
	$date = date( 'r' ,time());
	$phpversion = phpversion();
	$boundary = md5( time() );
	$encoding=strtoupper(\SG\getFirst($this->encoding,cfg('client.characterset')));
	$title=strip_tags($title);

	$headers = 'From: "'.$this->FromName().'" <'.$this->FromEMail().'>'."\r\n";
	if ( $this->CC() != '' ) $headers .= 'cc: '.$this->CC()."\r\n";
	if ( $this->BCC() != '' ) $headers .= 'bcc: '.$this->BCC()."\r\n";
	if ( $this->ReturnPath() != '' ) $headers .= 'Return-Path: '.$this->ReturnPath()."\r\n";
	$headers .= 'Content-Type: text/html; charset='.$encoding.$eol;
	$headers .= 'Date: '.$date.$eol;
	$headers .= 'X-Mailer: PHP v'.$phpversion.$eol;
	$headers .= 'MIME-Version: 1.0'.$eol;

	if ( $emulate ) {
		$result = true;
	} else {
		$old_error=error_reporting(0);
		$result = mail($mailto,$title, $message,$headers);
		error_reporting($old_error);
	}
	$this->send_message='<p>mailto : '.htmlspecialchars($mailto).'</p><p>hearder : '.htmlspecialchars($headers).'</p><p>title : '.htmlspecialchars($title).'</p></p>message : '.htmlspecialchars($message).'</p>';

	return $result;
}

function sendHTMLemail3($HTML,$from,$to,$subject) {
	$strTo = $to;
	$strSubject = $subject;
	$strHeader = "Content-type: text/html; charset=UTF-8\n";
	$strHeader .= $from."\n";
	$strMessage = $HTML;
     @mail($strTo,$strSubject,$strMessage,$strHeader);
}

function sendHTMLemail($to,$subject,$HTML,$from) {
	$from = SG\getFirst($form,'"นายหมี" <alert@softganz.com>');
	$from = 'From: '.$from;
	$strHeader = "Content-type: text/html; charset=UTF-8\n";
	//	$strHeader = 'From: "'.$this->FromName().'" <'.$this->FromEMail().'>'."\r\n";
	$strHeader .= $from."\n";
    @mail($to,$subject,$HTML,$strHeader);
	//	print_o(array(htmlspecialchars($to),htmlspecialchars($subject),htmlspecialchars($HTML),htmlspecialchars($strHeader)),1);die;
}

} //--- End Of Class mail
?>