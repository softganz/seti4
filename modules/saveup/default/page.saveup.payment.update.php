<?php
/**
 * Process payment
 *
 * @return String
 */
function saveup_payment_update($self) {
	$self->theme->title = 'แจ้งการโอนเงิน';
	$self->theme->option->title = true;
	if (empty($_POST)) return R::Page('saveup.payment.form', $self);

	$post = (object) post('payment');
	if (empty($post->payacc)) $error[] = 'โปรดระบุโอนเงินโดยวิธี';
	if (empty($post->time)) $error[] = 'โปรดระบุเวลาในการโอนเงิน';
	if (empty($post->amt)) $error[] = 'โปรดระบุจำนวนเงินที่โอน';
	if (empty($post->poster)) $error[] = 'โปรดระบุชื่อผู้โอน';

	if ($error) {
		$ret .= message('error', $error);
		$ret .= R::Page('saveup.payment.form', $self, $post);
		return $ret;
	}


	// Save data

	$post->keyword = 'TRANSFER';
	$post->kid = 1;
	$post->status = 20;

	$result = R::Model('saveup.transfer.add', $post, '{debug: false}');

	//'keyword=TRANSFER','status=20', 'amt='.$post->amt, 'poster='.$post->poster, 'detail',$log );

	if ($result->success && $result->data->lid && $_FILES['photo']['tmp_name']) {
		$photoData->tpid = NULL;
		$photoData->refid = $result->data->lid;
		$photoData->prename = 'saveup_transfer_';
		$photoData->tagname = 'saveup_transfer';
		$photoData->title = $data->poster;
		$uploadResult = R::Model('photo.upload',$_FILES['photo'],$photoData);
		//$ret .= print_o($uploadResult, '$uploadResult');
	}

	$ret .= message('success',
					'บันทึกการโอนเงินเรียบร้อย : '
					.'<p>รายละเอียดการแจ้งโอนเงิน<br />ผู้แจ้ง : '.$post->poster.'<br />'
					.'ข้อมูลการแจ้งโอนเงิน :<br />'
					.nl2br($result->log)
					.'</p>'
					.($uploadResult->link ? $uploadResult->link : '')
				);

	if (i()->ok) $ret .= R::Page('saveup.payment.trans', $self, i()->uid);

	return $ret;




	$log = 'ชื่อผู้โอนเงิน '.$post->poster._NL
				.'โอนเงินทาง '.$post->payacc._NL
				.'เมื่อวันที่ '.$post->date['date'].'-'.$post->date[month].'-'.$post->date['year']
				.' เวลา '.$post->time.' น.'._NL
				.'จำนวนเงิน '.$post->amt.' บาท'._NL
				.'รายละเอียด'._NL.$post->remark;


	$ret .= message('status','บันทึกการโอนเงินเรียบร้อย');
	$ret .= '<h3>รายละเอียดการแจ้งโอนเงินผ่านทางเว็บ</h3>'
				.'<p><strong>ผู้แจ้ง : '.$post->poster.'</strong></p>'
				.'<p><strong>ข้อมูลการแจ้งโอนเงิน :</strong></p><p>'.nl2br($log).'</p>';


	// Send mail
	if (cfg('saveup.email')) {
		$subject = 'แจ้งการโอนเงินจาก '.$post->poster.' เมื่อ '.$post->date['date'].'-'.$post->date[month].'-'.$post->date['year'].' เวลา '.$post->time.' น.';
		$strHeader = 'Content-type: text/html; charset='.strtoupper(cfg('client.characterset'))."\n";
		$strHeader .= 'From: noreply@'.cfg('domain.short')."\n";

		@mail(cfg('saveup.email'), $subject, str_replace(_NL, '<br />', $log), $strHeader);
	}
	return $ret;
}
?>