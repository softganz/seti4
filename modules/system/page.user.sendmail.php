<?php
/**
* User    :: Send mail
* Modify  :: 2025-06-16
* Version :: 2
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function user_sendmail($self) {
	if (!load_lib('class.mail.php', 'lib')) return 'Mail module error';

	$post = post();

	$logMsg = 'Send mail request mailto:'.$post['mailto'].' title:'.$post['title'];
	LogModel::save([
		'module' => 'user',
		'keyword' => 'sendmail',
		'message' => $logMsg
	]);


	// send mail
	$mail = new Mail();

	$mailto=$post['mailto'];
	$title = $post['title'];
	$from = 'noreply@'.cfg('domain.short');
	$from_name = 'noreply';
	$message = $post['message'];
	$mail->FromName($from_name);
	$mail->FromEmail($from);
	if ( $mailto ) {
		$mail_result = $mail->Send($mailto,$title,$message);
		$ret .= $mail_result ? message('status','ได้ส่งข้อมูลไปให้ท่านที่อีเมล์ <strong>'.$get_by_email.'</strong> เรียบร้อยแล้ว') : message('error','Sending email error');
	} else $ret .= message('error','Invalid email address.');
	//return $ret;

	return $mail_result;
}
?>