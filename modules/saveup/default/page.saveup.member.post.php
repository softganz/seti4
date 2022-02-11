<?php
function saveup_member_post($self) {
	if ($_POST['cancel']) location('saveup/member/list');

	R::View('saveup.toolbar',$self,'เพิ่มสมาชิกใหม่','member');

	$error=null;
	if (post('member')) {
		$post=(object)post('member',_TRIM+_STRIPTAG);
		if (empty($post->mid)) $error[]='field <em>หมายเลขสมาชิก </em> require';
		if (empty($post->prename)) $error[]='field <em>คำนำหน้าชื่อ </em> require';
		if (empty($post->firstname)) $error[]='field <em>ชื่อ Model</em> require';
		if (empty($post->lastname)) $error[]='field <em>นามสกุล</em> require';
		if ($post->mid && mydb::select('SELECT `mid` FROM %saveup_member% WHERE mid = :mid LIMIT 1', ':mid', $post->mid)->mid) $error[]='หมายเลขสมาชิกซ้ำกับผู้อื่น:มีหมายเลขสมาชิกนี้อยู่แล้วในฐานข้อมูล กรุณาตรวจสอบความถูกต้องหรือใช้หมายเลขอื่น';
		// start save new item
		$simulate=debug('simulate');
		if (!$error) {
			if ($post->caddress_like=='address') {
				$post->caddress=$post->address;
				$post->camphure=$post->amphure;
				$post->cprovince=$post->province;
				$post->czip=$post->zip;
			}
			if (empty($post->contact_id)) $post->contact_id='func.NULL';

			$post->date_regist = sg_date($post->date_regist,'Y-m-d');
			$post->date_approve=($post->date_approve['year'] && $post->date_approve['month'] && $post->date_approve['date'])?$post->date_approve['year'].'-'.$post->date_approve['month'].'-'.$post->date_approve['date']:'func.NULL';
			$post->birth=($post->birth['year'] && $post->birth['month'] && $post->birth['date']) ? $post->birth['year'].'-'.$post->birth['month'].'-'.$post->birth['date']:'func.NULL';

			mydb::query(mydb::create_insert_cmd('%saveup_member%',$post,' '),$post);

			if ($simulate) {
				$ret.= '<p><strong>Member sql :</strong> '.db_query_cmd().'</p>';
				$ret.=print_o($post,'$post');
			} else {
				model::watch_log('saveup','saveup Member Create','<a href="'.url('saveup/member/view/'.$post->mid).'">member : '.$member->firstname.' '.$member->lastname.'</a> was created');
					location('saveup/member/view/'.$post->mid);
				return $ret;
			}
		}
	} else $post=null;

	if ($error) $ret.=message('error',$error);

	$ret.='<div class="container">';

	$form = new Form([
		'variable' => 'member',
		'action' => url(q()),
		'id' => 'edit-member',
		'class' => 'sg-form edit-member -sg-flex',
		'checkValid' => true,
		'children' => [
			'date_regist' => [
				'type' => 'text',
				'label' => 'วันที่สมัคร',
				'require' => true,
				'class' => 'sg-datepicker',
				'autocomplete' => 'off',
				'value' => SG\getFirst($post->date_regist, date('d/m/Y')),
				'container' => '{class: "-full"}',
			],
			'mid' => [
				'type' => 'text',
				'label' => 'หมายเลขสมาชิก',
				'maxlength' => 6,
				'require' => true,
				'value' => $post->mid,
				'placeholder' => '00-000',
				'container' => '{class: "-full"}',
			],
			'<fieldset id="personal" class="personal"><legend>ข้อมูลส่วนบุคคล</legend>',
			'idno' => [
				'type' => 'text',
				'label' => 'หมายเลขบัตรประชาชน(13 หลัก)',
				'maxlength' => 13,
				'class' => '-fill',
				'require' => true,
				'value' => $post->idno,
			],
			'prename' => [
				'type' => 'text',
				'label' => 'คำนำหน้าชื่อ',
				'maxlength' => 15,
				'class' => '-fill',
				'require' => true,
				'value' => $post->prename,
			],
			'firstname' => [
				'type' => 'text',
				'label' => 'ชื่อ',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $post->firstname,
			],
			'lastname' => [
				'type' => 'text',
				'label' => 'นามสกุล',
				'maxlength' => 30,
				'class' => '-fill',
				'require' => true,
				'value' => $post->lastname,
			],
			'nickname' => [
				'type' => 'text',
				'label' => 'ชื่อเล่น',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $post->nickname,
			],
			'birth' => [
				'type' => 'date',
				'label' => 'วัน-เดือน-ปีเกิด',
				'require' => true,
				'year' => (Object) [
					'range' => '1927,'.(date('Y')-1927+1),
					'type' => 'BC',
				],
				'value' => (Object) [
					'date' => $post->birth['date'],
					'month' => $post->birth['month'],
					'year' => $post->birth['year'],
				],
			],
			'address' => [
				'type' => 'text',
				'label' => 'ที่อยู่ (ตามทะเบียนบ้าน)',
				'maxlength' => 200,
				'class' => '-fill',
				'require' => true,
				'value' => $post->address,
			],
			'amphure' => [
				'type' => 'text',
				'label' => 'อำเภอ',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $post->amphure,
			],
			'province' => [
				'type' => 'text',
				'label' => 'จังหวัด',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $post->province,
			],
			'zip' => [
				'type' => 'text',
				'label' => 'รหัสไปรษณีย์',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $post->zip,
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->phone,
			],
			'caddress_like' => [
				'type' => 'checkbox',
				'options' => ['address' => 'เหมือนตามทะเบียนบ้าน'],
				'value' => SG\getFirst($post->caddress_like,'address'),
			],
			'caddress' => [
				'type' => 'text',
				'label' => 'ที่อยู่ (ที่ติดต่อได้)',
				'maxlength' => 200,
				'class' => '-fill',
				'value' => $post->caddress,
			],
			'camphure' => [
				'type' => 'text',
				'label' => 'อำเภอ',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->camphure,
			],
			'cprovince' => [
				'type' => 'text',
				'label' => 'จังหวัด',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->cprovince,
			],
			'czip' => [
				'type' => 'text',
				'label' => 'รหัสไปรษณีย์',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->czip,
			],
			'</fieldset>',
			'<fieldset id="otherinfo" class="otherinfo"><legend>ข้อมูลอื่น ๆ</legend>',
			'mtype' => [
				'type' => 'radio',
				'label' => 'ความสัมพันธ์',
				'options' => ['1'=>'นักพัฒนา','2'=>'เพื่อนนักพัฒนา','3'=>'ญาติพี่น้อง'],
				'value' => $post->mtype,
			],
			'beneficiary_name' => [
				'type' => 'text',
				'label' => 'ผู้รับผลประโยชน์',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->beneficiary_name,
			],
			'beneficiary_addr' => [
				'type' => 'text',
				'label' => 'ที่อยู่ (ผู้รับผลประโยชน์)',
				'maxlength' => 255,
				'class' => '-fill',
				'value' => $post->beneficiary_addr,
			],
			'contact_name' => [
				'type' => 'text',
				'label' => 'บุคคลที่ติดต่อได้',
				'maxlength' => 50,
				'class' => '-fill',
				'value' => $post->contact_name,
			],
			'contact_id' => [
				'type' => 'text',
				'label' => 'หมายเลขสมาชิก',
				'maxlength' => 6,
				'class' => '-fill',
				'value' => $post->contact_id,
			],
			'date_approve' => [
				'type' => 'date',
				'label' => 'วันที่อนุมัติ',
				'require' => true,
				'year' => (Object)['range' => '-3,4', 'type' => 'BC',],
				'value' => (Object)[
					'date' => $post->date_approve['date'],
					'month' => $post->date_approve['month'],
					'year' => $post->date_approve['year'],
				],
			],
			'savepayperiod' => [
				'type' => 'select',
				'label' => 'ชำระเงินสัจจะราย:',
				'options' => [1 => 1, 6 => 6, 12 => 12],
				'value' => $post->savepayperiod,
				'posttext' => ' เดือน',
			],
			'email' => [
				'type' => 'text',
				'label' => 'E-Mail',
				'class' => '-fill',
				'value' => htmlspecialchars($post->email),
				'placeholder' => 'Ex. name@example.com',
			],
			'facebook' => [
				'type' => 'text',
				'label' => 'Facebook',
				'class' => '-fill',
				'value' => htmlspecialchars($post->facebook),
				'placeholder' => 'Ex. https://facebook.com/name',
			],
			'remark' => [
				'type' => 'textarea',
				'label' => 'หมายเหตุ',
				'class' => '-fill',
				'value' => $post->remark,
			],
			'</fieldset>',
			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('saveup/member').'"><i class="icon -cancel -gray"></i><span>CANCEL</span></a>',
				'container' => '{class: "-sg-text-right -full"}',
			]
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>