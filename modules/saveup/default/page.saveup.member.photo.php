<?php
/**
 * Show info
 *
 * @param Integer $mid
 * @return String and die
 */
function saveup_member_photo($self,$mid) {
	$mid=SG\getFirst($mid,$_GET['id']);
	$rs=saveup_model::get_user_detail($mid);

	if ($rs->_empty) return message('error','ไม่มีข้อมูลของรายการที่ท่านต้องการอยู่ในฐานข้อมูล');

	R::View('saveup.toolbar',$self,$rs->mid.' : '.$rs->firstname.' '.$rs->lastname.' - ภาพถ่าย','member',$rs);

	$photo = (object)$_FILES['photo'];
	if (is_uploaded_file($photo->tmp_name)) {
		$photo->name='profile-'.$rs->mid.'.jpg';
		$photo->overwrite=true;
		$result = R::Model('photo.save', $photo, 'upload/saveup/');
		// if ($result->complete && $result->save->_file) {}
		// $ret .= print_o($result,'$result');
		location('saveup/member/view/'.$rs->mid);
	} else {
		$form = new Form([
			'action' => url(q()),
			'id' => 'saveup-edit-photo',
			'enctype' => 'multipart/form-data',
			'children' => [
				'photo' => [
					'type' => 'file',
					'label' => 'เลือกไฟล์สำหรับอัพโหลด',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">upload</i><span>อัพโหลด</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				'<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg ขนาด <strong>300x300 พิกเซล</strong> ขนาดไฟล์ไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong></li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li></ul>',
			],
		]);

		$ret .= $form->build();

		$ret.='<img src="'.saveup_model::member_photo($mid).'" id="saveup-edit-photo-show" />';
	}

	return $ret;
}
?>