<?php
function imed_patient_photo($self,$psnId) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');


	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error',$psnInfo->error);

	$self->theme->title=$psnInfo->fullname;

	$photo=(object)$_FILES['photo'];
	if (is_uploaded_file($photo->tmp_name)) {
		$photo->name='profile-'.$psnId.'.jpg';
		$photo->overwrite=true;
		$result = R::Model('photo.save',$photo,'upload/imed/');
		// if ($result->complete && $result->save->_file) {}
		//$ret .= print_o($result,'$result');
		location('imed/patient/photo/'.$psnId);
		return;
	}


	$ret .= '<section style="width: 100%;">';

	$ret.='<img src="'.imed_model::patient_photo($psnId).'" id="disabled-edit-photo-show" style="width: 240px; height: 240px; display: block; margin: 32px auto; border-radius: 50%; float: none;" />';

	$form = new Form([
		'action' => url(q()),
		'id' => 'disabled-edit-photo',
		'enctype' => 'multipart/form-data',
		'children' => [
			'photo' => [
				'label' => 'เลือกไฟล์สำหรับอัพโหลด',
				'type' => 'file'
			],
			// $form->photo->size=30;
			'upload' => [
				'type' => 'button',
				'value' => '<i class="icon -material">upload</i><span>อัพโหลด</span>',
			],
			'<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg ขนาด <strong>300x300 พิกเซล</strong> ขนาดไฟล์ไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong></li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li></ul>',
		],
	]);
	$ret .= $form->build();
	$ret .= '<style>#disabled-edit-photo {width: 240px; margin:0 auto;}</style>';

	$ret .= '</section>';
	return $ret;
}
?>