<?php
/**
* LMS :: View Student Information
* Created 2020-07-11
* Modify  2020-07-11
*
* @param Object $self
* @param Object $studentInfo
* @return String
*/

$debug = true;

function lms_student_edit($self, $studentInfo) {
	if (!($studentId = $studentInfo->studentId)) return message('error', 'PROCESS ERROR');

	R::View('toolbar', $self, 'นักศึกษา/'.$studentInfo->name, 'lms', $studentInfo, '{searchform: false}');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');
	$isEditStudent = $isAdmin || $isTeacher || user_access('edit lms student');

	$isViewDetail = $isAdmin || $isTeacher || (i()->ok && i()->uid == $studentInfo->uid);
	$isEdit = $isEditStudent || (i()->ok && i()->uid == $studentInfo->uid);

	if (!$isEdit) return message('error', 'Access Denied');

	$headerUi = new Ui();
	$headerUi->addConfig('nav', '{class: "nav"}');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$studentInfo->name.'</h3></header>';;


	$provinceOptions = array();
	$ampurOptions = array();
	$tambonOptions = array();

	$stmt = 'SELECT
		*
		, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
		FROM %co_province%
		ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC, CONVERT(`provname` USING tis620) ASC';
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;


	$ret .= '<div class="header">'
		. '<img class="profile-photo -sg-64" src="'.model::user_photo($studentInfo->info->username).'" width="64" height="64" />'
		. '<span class="profile">'
		. '<span class="poster-name">'
		. '<b>'.$studentInfo->name.'</b>'
		. ($studentInfo->info->enname ? ' ('.$studentInfo->info->enprename.$studentInfo->info->enname.' '.$studentInfo->info->enlname.')' : '')
		. '</span>'
		. '</span>'
		. '</div>';

	$form = new Form('data', url('lms/'.$studentInfo->courseId.'/info/student.save/'.$studentId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load->replace:.lms-my-home');

	$form->addField(
		'prename',
		array(
			'type' => 'text',
			'label' => 'คำนำหน้านาม',
			'class' => '-fill',
			'require'=>true,
			'value' => $studentInfo->info->prename,
		)
	);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อ',
			'class' => '-fill',
			'require'=>true,
			'value' => $studentInfo->info->name,
		)
	);

	$form->addField(
		'lname',
		array(
			'type' => 'text',
			'label' => 'นามสกุล',
			'class' => '-fill',
			'require'=>true,
			'value' => $studentInfo->info->lname,
		)
	);

	$form->addField(
		'idcard',
		array(
			'type' => 'text',
			'label' => 'หมายเลขบัตรประชาชน (13 หลัก)',
			'class' => '-fill',
			'require'=>true,
			'maxlength' => 13,
			'value' => $studentInfo->info->idcard,
		)
	);


	$form->addField(
		'enprename',
		array(
			'type' => 'text',
			'label' => 'Prename (English)',
			'class' => '-fill',
			'value' => $studentInfo->info->enprename,
		)
	);

	$form->addField(
		'enname',
		array(
			'type' => 'text',
			'label' => 'First Name (English)',
			'class' => '-fill',
			'value' => $studentInfo->info->enname,
		)
	);

	$form->addField(
		'enlname',
		array(
			'type' => 'text',
			'label' => 'Last Name (English)',
			'class' => '-fill',
			'value' => $studentInfo->info->enlname,
		)
	);

	if ($isAdmin) {
		$form->addField(
			'scode',
			array(
				'type' => 'text',
				'label' => 'รหัสนักศึกษา',
				'value' => $studentInfo->info->scode,
			)
		);

		$form->addField(
			'serno',
			array(
				'type' => 'text',
				'label' => 'รุ่น',
				'value' => $studentInfo->info->serno,
			)
		);

		$form->addField(
			'coursetype',
			array(
				'type' => 'checkbox',
				'options' => array('ONLINE' => 'เรียนออนไลน์'),
				'value' => $studentInfo->info->coursetype,
			)
		);

	} else {
		$form->addField('scode', array('type' => 'hidden', 'value' => $studentInfo->info->scode));
		$form->addField('serno', array('type' => 'hidden', 'value' => $studentInfo->info->serno));
		$form->addField('coursetype', array('type' => 'hidden', 'value' => $studentInfo->info->coursetype));
	}

	$form->addField(
		'email',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'require'=>true,
			'value' => $studentInfo->info->email,
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'require'=>true,
			'value' => $studentInfo->info->phone,
		)
	);

	$form->addField('areacode', array('type' => 'hidden', 'id' => 'edit-areacode', 'label' => 'ที่อยู่/ตำบล/อำเภอ/จังหวัด', 'require'=>true, 'value' => $studentInfo->info->areacode));

	$form->addField(
		'house',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class'=>'sg-address -fill',
			'maxlength'=>100,
			'require'=>true,
			'attr'=>array('data-altfld' => 'edit-areacode'),
			'value' => $studentInfo->info->address,
		)
	);

	$form->addField(
		'changwat',
		array(
		//	'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $studentInfo->info->changwat,
		)
	);

	$form->addField(
		'ampur',
		array(
		//	'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'options' => array('' => '== เลือกอำเภอ ==') + $ampurOptions,
			'value' => $data->ampur,
		)
	);

	$form->addField(
		'tambon',
		array(
		//	'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'options' => array('' => '== เลือกตำบล ==') + $tambonOptions,
			'value' => $data->tambon,
			'attr' => array('data-altfld' => '#edit-areacode'),
		)
	);

	$form->addField(
		'zip',
		array(
			'type' => 'text',
			'label' => 'รหัสไปรษณีย์',
			'value' => $studentInfo->info->zip,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($studentInfo, '$studentInfo');

	$ret .= '<style tyle="text/css">
	.box-page .form-item.-edit-data-save {position: absolute; top: -50px; z-index: 1000; right: 40px;}
	</style>';

	$ret.='<script type="text/javascript">
		$("#edit-data-tambon").change(function() {
			if ($(this).val() == "") {
				$("#edit-areacode").val("")
				return
			}
			var areaCode = $("#edit-data-changwat").val()+$("#edit-data-ampur").val()+$("#edit-data-tambon").val()
			console.log(areaCode);
			$("#edit-areacode").val(areaCode)
		})
	</script>';

	return $ret;
}
?>