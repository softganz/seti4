<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_senddelete($self, $tpid) {
	$self->theme->title = 'แจ้งลบหัวข้อที่ไม่เหมาะสม';

	$ret .= '<header class="header -box -hidden"><h3>'.$self->theme->title.'</h3></header>';

	$post = (Object) post('contact');

	if ($post->detail) {
		if (empty($post->detail)) $error[] = 'กรุณาระบุความไม่เหมาะสมของเนื้อหา';
		if (empty($post->sender)) $error[] = 'กรุณาป้อนชื่อผู้ส่ง';
		if (!i()->ok && !sg_valid_daykey(5,post('daykey'))) $error[] = 'Invalid Anti-spam word';


		if (!$error) {
			$topicInfo = R::Model('paper.get', $tpid);

			if (load_lib('class.mail.php', 'lib')) {
				$mail = new Mail();

				$mail->FromName('noreply');
				$mail->FromEmail('noreply@'.cfg('domain.short'));

				$mailTo = cfg('email.delete_message');
				$mailTitle = 'แจ้งลบหัวข้อ : '.strip_tags($topicInfo->title);
				$mailMessage = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
				<html>
				<head>
				<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
				<title>'.$topicInfo->title.'</title>
				</head>
				<body>
				<h2><a href="'.cfg('domain').url('paper/'.$topicInfo->tpid).'" target=_blank><strong>'.$topicInfo->title.'</strong></a></h2>'
				. '<p>Submit by <strong>'.SG\getFirst($topicInfo->info->owner, $topicInfo->info->poster)
				. ($topicInfo->uid ? '('.$topicInfo->uid.')' : '')
				. '</strong> '
				. ' on <strong>'.$topicInfo->info->created.'</strong> | ip : '.GetEnv('REMOTE_ADDR')
				. ' | paper id : <strong><a href="'.cfg('domain').url('paper/'.$topicInfo->tpid).'" target=_blank>'.$topicInfo->tpid.'</a></strong><p>
				<hr size=1>
				<h3>แจ้งโดย : '.$post->sender.' &lt;'.$post->email.'&gt;</h3>
				<h3>ความไม่เหมาะสมของเนื้อหา</h3><p>'.$post->detail.'<p>
				<h3>ข้อความ</h3>'
				. $topicInfo->info->body
				.'
				</body>
				</html>';


				if ( $mailTo ) {
					$mail_result = $mail->Send($mailTo, $mailTitle, $mailMessage, false, 'https://service.softganz.com');
					if ($mail_result) {
						//$ret .= 'Send mail complete';
					}
				}
			}


			R::Model('watchdog.log', 'paper', 'Send delete paper', 'Paper : '.$tpid.' : '.$topicInfo->title.'<br />'.$post->detail);

			//BasicModel::sendmail($mail);
			//$ret .= print_o($mail,'$mail');

			$ret .= message('success','ส่งข้อความแจ้งลบหัวข้อที่ไม่เหมาะสมเรียบร้อย');
			return $ret;
		}
	}

	$form = new Form('contact', url(q()), 'edit-senddelete', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'replace');

	if ($error) $form->addText(message('error',$error));

	$form->addField('detail',
					array(
						'type' => 'textarea',
						'label' => 'กรุณาระบุความไม่เหมาะสมของเนื้อหา',
						'class' => '-fill',
						'rows' => 5,
						'require' => true,
						'value' => $post->detail,
					)
				);

	$form->addField('sender',
					array(
						'type' => 'text',
						'label' => tr('Sender').(i()->ok ? ' (you are member)':''),
						'class' => '-fill',
						'require' => true,
						'value' => SG\getFirst($post->sender,i()->name),
					)
				);

	$form->addField('email',
					array(
						'type' => 'text',
						'label' => tr('E-mail'),
						'class' => '-fill',
						'value' => $post->email,
					)
				);

	if (!i()->ok) {
		$form->addField('daykey',
						array(
							'name' => 'daykey',
							'type' => 'text',
							'label' => tr('Anti-spam word'),
							'size' => 10,
							'require' => true,
							'value' => $_POST['daykey'],
							'posttext' => ' &laquo; <em class="spamword">'.poison::get_daykey(5,true).'</em>',
							'description' => 'ป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
						)
					);
	}

	$form->addField('save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SEND}</span>',
						'container' => '{class: "-sg-text-right"}',
					)
				);

	$ret .= $form->build();

	return $ret;
}
?>