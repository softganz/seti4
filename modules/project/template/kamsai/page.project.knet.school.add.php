<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_knet_school_add($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER')));


	$ret .= '<h3 class="title -box">เพิ่มโรงเรียนเครือข่าย</h3>';

	$isCreatable = $isEdit;

	if (!$isCreatable) return message('error', 'Access Denied');

	//$ret .= print_o($data,'$data');

	if ($data->_error) $ret .= message('error',$data->_error);
	$form = new Form('data',url('org/new'),'org-add-org', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel','refresh');
	$form->addData('complete','closebox');

	$form->addField('callback', ['type' => 'hidden','name' => 'callback','value' => 'project.knet.child.add']);
	$form->addField('allowdup', ['type' => 'hidden','name' => 'allowdup','value' => 'yes']);
	//$form->addField('srcpage', ['type' => 'hidden','name' => 'srcpage','value' => 'org.new']);
	$form->addField('parent', ['type' => 'hidden', 'value' => $orgId]);
	$form->addField('orgid', ['type' => 'hidden', 'value' => $data->orgid]);
	$form->addField('officer', ['type' => 'hidden', 'value' => 'ADMIN']);
	$form->addField('areacode', ['type' => 'hidden']);
	$form->addField('networktype', ['type' => 'hidden', 'value' => 2]);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อโรงเรียนเครือข่าย',
			'class' => 'x-sg-autocomplete -fill',
			'require' => true,
			'value' => htmlspecialchars($data->name),
			//'description' => 'กรุณาป้อนชื่อหน่วยงานของท่าน หากหน่วยงานของท่านมีในรายการแล้ว กรุณาเลือกจากรายการที่แสดง',
			'placeholder' => 'ระบุชื่อโรงเรียนเครือข่าย',
			'attr' => array(
				'data-altfld'=>'edit-org-orgid',
				'data-query'=>url('org/api/org')
			),
		)
	);


	$sectorList = R::Model('category.get','sector','catid');

	$form->addField(
		'sector',
		array(
			'type' => 'hidden',
			'label' => 'ประเภทองค์กร:',
			'require' => true,
			'display' => 'inline',
			'options' => $sectorList,
			'value' => 2,
		)
	);

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class' => 'sg-address -fill',
			'attr' => array('data-altfld' => 'edit-org-areacode'),
			'value' => htmlspecialchars($data->address),
			'placeholder' => 'เช่น 0 ม.0 ต.ตัวอย่าง (แล้วเลือกจากรายการแสดงด้านล่าง)',
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'value' => htmlspecialchars($data->phone),
			'placeholder' => '0000000000',
		)
	);

	$form->addField(
		'email',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'value' => htmlspecialchars($data->email),
			'placeholder' => 'name@example.com',
		)
	);


	$form->addField(
		'studentamt',
		array(
			'type' => 'text',
			'label' => 'จำนวนนักเรียน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->studentamt),
			'placeholder' => '0',
		)
	);

	$form->addField(
		'classlevel',
		array(
			'type' => 'checkbox',
			'label' => 'ช่วงชั้น',
			'multiple' => true,
			'options' => array('ปฐมวัย'=>'ปฐมวัย','อนุบาล'=>'อนุบาล','ประถม'=>'ประถม','มัธยมต้น'=>'มัธยมต้น'),
			'value' => explode(',',$data->classlevel),
			'placeholder' => '0',
		)
	);

	$form->addField(
		'managername',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้อำนวยการโรงเรียน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->managername),
			'placeholder' => 'ระบุชื่อผู้อำนวยการโรงเรียน',
		)
	);

	$form->addField(
		'contactname',
		array(
			'type' => 'text',
			'label' => 'ชื่อครูผู้รับผิดชอบ',
			'class' => '-fill',
			'value' => htmlspecialchars($data->contactname),
			'placeholder' => 'ระบุชื่อครูผู้รับผิดชอบ',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกชื่อโรงเรียนเครือข่าย</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($projectInfo,'$projectInfo');


	return $ret;
}
?>
