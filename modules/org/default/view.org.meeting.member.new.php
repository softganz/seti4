<?php
function view_org_meeting_member_new($name) {
	$post=(object)post('person');
	if (empty($post->fullname)) $post->fullname=$name;

	$form=new Form([
		'variable' => 'person',
		'action' => url(q()),
		'id' => 'org-add-person',
		'class' => 'sg-form box',
		'title' => 'เพิ่มรายชื่อใหม่',
		'rel' => '#org-join-list',
		'children' => [
			'areacode' => ['type' => 'hidden', 'value' => $post->areacode,],
			'orgid' => ['type' => 'hidden', 'value' => $post->orgid],
			'prename' => [
				'type' => 'text',
				'label' => 'คำนำหน้านาม',
				'class' => '-fill',
				'value' => $post->prename,
			],
			'fullname' => [
				'type' => 'text',
				'label' => 'ชื่อ - นามสกุล',
				'require' => true,
				'class' => '-fill',
				'value' => $post->fullname,
			],
			'cid' => [
				'type' => 'text',
				'label' => 'เลขประจำตัวประชาชน(13 หลัก)',
				'maxlength' => 13,
				'class' => '-fill',
				'value' => $post->cid,
			],
			'address' => [
				'type' => 'text',
				'label' => 'ที่อยู่',
				'class' => 'sg-address -fill',
				'attr' => 'data-altfld="edit-person-areacode"',
				'value' => $post->address,
			],
			'orgname' => [
				'type' => 'text',
				'label' => 'องค์กร',
				'class' => 'sg-autocomplete -fill',
				'value' => $post->orgname,
				'placeholder' => 'ระบุชื่อองค์กรของผู้เข้าร่วมกิจกรรม (ไม่ใช่ตำแหน่ง) กรณีไม่มีองค์กรให้เว้นว่างไว้ ',
				'attr' => [
					'data-altfld'=>'edit-person-orgid',
					'data-query'=>url('api/org'),
				],
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์',
				'class' => '-fill',
				'value' => $post->phone,
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์',
				'class' => '-fill',
				'value' => $post->email,
			],
			'submit' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>บันทึกรายชื่อใหม่</span>',
				'pretext'=>'<a class="btn -link -cancel" href="javascript:void(0)" onClick=\'$(this).closest("form").remove()\'><i class="icon -cancel -gray"></i><span>CANCEL</span></a>',
				'posttext'=>'<br />บันทึกรายชื่อใหม่และเพิ่มรายชื่อในกิจกรรม',
				'container' => array('class'=>'-sg-text-right'),
			],
		], // children
	]);

	$ret .= $form->build();
	return $ret;
}
?>