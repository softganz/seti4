<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function saveup_app_transfer($self) {
	saveup_model::init_app_mainpage($self);
	unset($self->theme->toolbar);

	$ret = '<h2>บริการแจ้งโอนเงิน</h3>';

	if (!i()->ok && post('act') == 'signform') {
		$ret .= message('error','access denied');
	} else if (post('payment')) {
		$ret .= _update();
	} else {
		$ret .= _form();
		//$ret.=R::Page('project.app.activity.form');
		//$ret.=R::Page('project.app.activity.show');
		//$ret.=print_o(post(),'post()');
	}

	if (i()->ok)
		$ret .= R::Page('saveup.payment.trans', $self, i()->uid);

	return $ret;
}

/**
 * Show payment form
 *
 * @param Object $post
 * @return String
 */
function _form($post = null) {
	$form = new Form([
		'variable' => 'payment',
		'action' => url('saveup/app/transfer'),
		'id' => 'saveup-confirm',
		'class' => 'sg-form',
		'enctype' => 'multipart/form-data',
		'checkValid' => true,
	]);

	$form->addField('payfor',
					array(
						'type' => 'select',
						'label' => 'โอนเงินเป็นค่า :',
						'options' => array(
													'ค่าออมทรัพย์' => 'ค่าสัจจะ-เงินกู้-ค่าบำรุง-ค่าปรับ',
													'ค่ากองทุนบำนาญ' => 'ค่ากองทุนบำนาญ',
													'อื่น ๆ' => 'อื่น ๆ',
												),
						'require' => true,
						'class' => '-fill',
					)
				);

	$payaccOptions = array();
	foreach (cfg('saveup.payment.account') as $key=>$item)
		$payaccOptions[$item] = $item;
	$form->addField('payacc',
					array(
						'type' => 'select',
						'label' => 'โอนเงินโดยวิธี :',
						'require' => true,
						'class' => '-fill',
						'options' => $payaccOptions,
						'value' => $post->payacc,
					)
				);

	$form->addField('date',
					array(
						'type' => 'date',
						'label' => 'วันที่โอน :',
						'require' => true,
						'year' => (object) array('range' => '-1,2', 'type' => 'BC'),
						'value' => (object) array('date' => date('d'), 'month' => date('m'), 'year' => date('Y'))
					)
				);

	$form->addField('time',
					array(
						'type' => 'hour',
						'label' => 'เวลา (ประมาณ)',
						'require' => true,
						'value' => (object) array('hour' => SG\getFirst($post->hour,date('H')), 'min' => SG\getFirst($post->hour,date('i')))
					)
				);

	$form->addField('amt',
					array(
						'type' => 'text',
						'label' => 'จำนวนเงินที่โอน (บาท) :',
						'maxlength' => 10,
						'require' => true,
						'value' => htmlspecialchars($post->amt),
						'class' => '-fill',
					)
				);

	$form->addField('poster',
					array(
						'type' => 'text',
						'label' => 'ชื่อผู้โอน',
						'class' => '-fill',
						'require' => true,
						'value' => htmlspecialchars($post->poster),
					)
				);

	$form->addField('remark',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียด',
						'rows' => 5,
						'class' => '-fill',
						'value' => htmlspecialchars($post->remark),
						'description' => 'กรุณาป้อนรายละเอียดในการโอนเงินว่าเป็นค่าอะไรบ้าง โดยป้อน 1 รายการต่อ 1 บรรทัด',
					)
				);

	// FIXME : เขียน App ให้รองรับการถ่ายภาพก่อน
	/*
	if (i()->ok) {
		$form->addField('photo',
						array(
							'type' => 'file',
							'name' => 'photo',
							'label' => '<i class="icon -camera"></i><span>ถ่ายภาพใบเสร็จรับเงิน</span>',
							'container' => array('class' => 'btn -upload'),
						)
					);
	} else {
		$form->addText('<p class="notify"><a class="sg-action" href="'.url('user').'" data-rel="box">เข้าสู่ระบบสมาชิกเพื่อส่งภาพถ่ายใบโอนเงิน</a></p>');
	}
	*/

	$form->addField('save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>แจ้งการโอนเงิน</span>'
					)
				);
	$ret .= $form->build();
	return $ret;
}

/**
 * Process payment
 *
 * @return String
 */
function _update() {
	//if (empty($_POST)) return _form(array());
	$post=(object)post('payment');
	if (empty($post->payacc)) $error[] = 'โปรดระบุโอนเงินโดยวิธี';
	if (empty($post->time)) $error[] = 'โปรดระบุเวลาในการโอนเงิน';
	if (empty($post->amt)) $error[] = 'โปรดระบุจำนวนเงินที่โอน';
	if (empty($post->poster)) $error[] = 'โปรดระบุชื่อผู้โอน';

	if ($error) {
		$ret .= message('error', $error);
		return _form($post);
	}

	$post->keyword = 'TRANSFER';
	$post->kid = 2;
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

	//$ret.=print_o($result,'$result');

	// Send mail
	/*
	if (cfg('saveup.email')) {
		$subject='แจ้งการโอนเงินจาก '.$post->poster.' เมื่อ '.$post->date['date'].'-'.$post->date['month'].'-'.$post->date['year'].' เวลา '.$post->time['hour'].':'.$post->time['min'].' น.';
		$strHeader='Content-type: text/html; charset='.strtoupper(cfg('client.characterset'))."\n";
		$strHeader.='From: noreply@'.cfg('domain.short')."\n";
		@mail(cfg('saveup.email'),$subject,str_replace(_NL,'<br />',$log),$strHeader);
	}
	*/
	return $ret;
}
?>