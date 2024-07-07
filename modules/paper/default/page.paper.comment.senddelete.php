<?php
/**
* Paper   :: Send Delete Request Via Email
* Created :: 2024-07-07
* Modify  :: 2024-07-07
* Version :: 2
*
* @param String $commentId
* @return Widget
*
* @usage paper/comment/senddelete/{commentId}
*/

class PaperCommentSenddelete extends Page {
	var $commentId;

	function __construct($commentId = NULL) {
		parent::__construct([
			'commentId' => SG\getFirstInt($commentId)
		]);
	}

	function rightToBuild() {
		if (empty($this->commentId)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
		return true;
	}

	function build() {
		$post = (Object) post('contact');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แจ้งลบความคิดเห็นที่ไม่เหมาะสม',
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'contact',
						'action' => url('paper/comment/senddelete..send/'.$this->commentId),
						'class' => 'sg-form',
						'checkValid' => true,
						'rel' => 'none',
						'done' => 'close',
						'children' => [
							$error ? message('error',$error) : NULL,
							'detail' => [
								'type' => 'textarea',
								'label' => 'กรุณาระบุความไม่เหมาะสมของเนื้อหา',
								'class' => '-fill',
								'rows' => 2,
								'require' => true,
								'value' => $post->detail,
							],
							'sender' => [
								'type' => 'text',
								'label' => tr('Sender').(i()->ok ? ' (You are member)':''),
								'class' => '-fill',
								'require' => true,
								'value' => SG\getFirst($post->sender,i()->name),
							],
							'email' => [
								'type' => 'text',
								'label' => tr('E-mail'),
								'class' => '-fill',
								'value' => $post->email,
							],
							'daykey' => !i()->ok ? [
								'name' => 'daykey',
								'type' => 'text',
								'label' => tr('Anti-spam word'),
								'size' => 10,
								'require' => true,
								'value' => $_POST['daykey'],
								'posttext' => ' &laquo; <em class="spamword">'.Poison::getDayKey(5,true).'</em>',
								'description' => 'ป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
							] : NULL,
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>'.tr('SEND').'</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}

	function send() {
		$post = (Object) post('contact');

		if (empty($post->detail)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'กรุณาระบุความไม่เหมาะสมของเนื้อหา');
		if (empty($post->sender)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'กรุณาป้อนชื่อผู้ส่ง');
		if (!i()->ok && !sg_valid_daykey(5,post('daykey'))) apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'Invalid Anti-spam word');

		$commentInfo = R::Model('paper.comment.get', $this->commentId);

		if (load_lib('class.mail.php', 'lib')) {
			$mail = new Mail();

			$mail->FromName('noreply');
			$mail->FromEmail('noreply@'.cfg('domain.short'));

			$mailTo = cfg('email.delete_message');
			$mailTitle = 'แจ้งลบความเห็น : '.strip_tags($commentInfo->title);
			$mailMessage = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
		<html>
		<head>
		<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
		<title>'.$commentInfo->title.'</title>
		</head>
		<body>
		<h2><a href="'.cfg('domain').url('paper/'.$commentInfo->tpid,null,'comment-'.$commentInfo->cid).'" target=_blank><strong>'.$commentInfo->title.'</strong></a></h2>
		<p>Submit by <strong>'.\SG\getFirst($commentInfo->owner,$commentInfo->name).($commentInfo->uid?'('.$commentInfo->uid.')':'').'</strong> on <strong>'.$commentInfo->timestamp.'</strong> ip : '.GetEnv('REMOTE_ADDR').' | paper id : <strong><a href="'.cfg('domain').url('paper/'.$commentInfo->tpid).'" target=_blank>'.$commentInfo->tpid.'</a></strong> comment id : <strong><a href="'.cfg('domain').url('paper/'.$commentInfo->tpid,null,'comment-'.$commentInfo->cid).'" target=_blank>'.$commentInfo->cid.'</a></strong><p>
		<hr size=1>
		<h3>แจ้งโดย : '.$post->sender.' &lt;'.$post->email.'&gt;</h3>
		<h3>ความไม่เหมาะสมของเนื้อหา</h3><p>'.$post->detail.'<p><h3><strong>ข้อความ</strong></h3>'.
		$commentInfo->comment.'
		</body>
		</html>';


			if ( $mailTo ) {
				$mail_result = $mail->Send($mailTo, $mailTitle, $mailMessage, false, 'https://service.softganz.com');
				if ($mail_result) {
					//$ret .= 'Send mail complete';
				}
			}
		}


		R::Model('watchdog.log', 'paper', 'Send delete comment', 'Paper id : '.$commentInfo->tpid.' : Comment id '.$commentInfo->cid.' : '.$commentInfo->title.'<br />'.$post->detail);

		//BasicModel::sendmail($mail);
		//$ret .= $mailTitle.$mailMessage;
		//$ret .= print_o($mail,'$mail');

		return apiSuccess('ส่งข้อความแจ้งลบความคิดเห็นที่ไม่เหมาะสมเรียบร้อย');
	}
}
?>