<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_knet_school_edit($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);




	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER')));


	$ret .= '<h3 class="title -box">แก้ไขโรงเรียนเครือข่าย</h3>';

	$isCreatable = $isEdit;

	if (!$isCreatable) return message('error', 'Access Denied');

	//$ret .= print_o($data,'$data');

	if ($data->_error) $ret .= message('error',$data->_error);
	$form = new Form([
		'variable' => 'org',
		'action' => url('project/knet/'.$orgId.'/school.save'),
		'id' => 'org-add-org',
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'close | load',
		'children' => [
			'areacode' => ['type' => 'hidden', 'value' => $orgInfo->info->areacode],
			'name' => [
				'type' => 'text',
				'label' => 'ชื่อโรงเรียนเครือข่าย',
				'class' => 'x-sg-autocomplete -fill',
				'require' => true,
				'value' => htmlspecialchars($orgInfo->name),
				'placeholder' => 'ระบุชื่อโรงเรียนเครือข่าย',
			],
			'groupType' => [
				'type' => 'text',
				'label' => 'สังกัด',
				'class' => '-fill',
				'value' => $orgInfo->info->groupType,
			],
			'address' => [
				'type' => 'text',
				'label' => 'ที่อยู่',
				'class' => 'sg-address -fill',
				'attr' => array('data-altfld' => 'edit-org-areacode'),
				'value' => htmlspecialchars($orgInfo->info->address),
				'placeholder' => 'เช่น 0 ม.0 ต.ตัวอย่าง (แล้วเลือกจากรายการแสดงด้านล่าง)',
			],
			'zip' => [
				'type' => 'text',
				'label' => 'รหัสไปรษณีย์',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->zipcode),
				'placeholder' => '00000',
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->phone),
				'placeholder' => '0000000000',
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->email),
				'placeholder' => 'name@example.com',
			],
			'studentamt' => [
				'type' => 'text',
				'label' => 'จำนวนนักเรียน',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->studentamt),
				'placeholder' => '0',
			],
			'classlevel' => [
				'type' => 'checkbox',
				'label' => 'ช่วงชั้น',
				'multiple' => true,
				'options' => array('ปฐมวัย'=>'ปฐมวัย','อนุบาล'=>'อนุบาล','ประถม'=>'ประถม','มัธยมต้น'=>'มัธยมต้น'),
				'value' => explode(',',$orgInfo->info->classlevel),
				'placeholder' => '0',
			],
			'managername' => [
				'type' => 'text',
				'label' => 'ชื่อผู้อำนวยการโรงเรียน',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->managername),
				'placeholder' => 'ระบุชื่อผู้อำนวยการโรงเรียน',
			],
			'contactname' => [
				'type' => 'text',
				'label' => 'ชื่อครูผู้รับผิดชอบ',
				'class' => '-fill',
				'value' => htmlspecialchars($orgInfo->info->contactname),
				'placeholder' => 'ระบุชื่อครูผู้รับผิดชอบ',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกโรงเรียนเครือข่าย</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			],
		], // children
	]);
	$ret .= $form->build();

	//$ret .= print_o($orgInfo,'$orgInfo');


	return $ret;
}
?>
