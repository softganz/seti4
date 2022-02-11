<?php
function saveup_member_modify($self,$mid) {
	if ($_POST['cancel']) location('saveup/member/view/'.$mid);

	$member=saveup_model::get_user_detail($mid);

	if ($member->_empty) return message('error',$this->theme->title='Member id '.$mid.' not found.');

	R::View('saveup.toolbar',$self,$rs->mid.' : '.$rs->firstname.' '.$rs->lastname.' - แก้ไขรายละเอียด','member',$member);

	$error=null;
	$post=(object)post('member',_TRIM+_STRIPTAG);
	if (post('member')) {
		if (empty($post->firstname)) $error[]='field <em>ฃื่อ</em> require';
		if (empty($post->lastname)) $error[]='field <em>นามสกุล</em> require';

		// start save new item
		$simulate=debug('simulate');
		if (!$error) {
			if (empty($post->contact_id)) $post->contact_id='func.NULL';
			$post->date_regist = sg_date($post->date_regist,'Y-m-d');
			$post->date_approve=($post->date_approve['year'] && $post->date_approve['month'] && $post->date_approve['date'])?$post->date_approve['year'].'-'.$post->date_approve['month'].'-'.$post->date_approve['date']:'func.NULL';
			$post->birth=($post->birth['year'] && $post->birth['month'] && $post->birth['date']) ? $post->birth['year'].'-'.$post->birth['month'].'-'.$post->birth['date']:'func.NULL';

			//if (empty($post->userid)) $post->userid='func.NULL';

			mydb::query(mydb::create_update_cmd('%saveup_member%',$post,'mid="'.$mid.'" LIMIT 1'),$post);
			//$ret .= mydb()->_query;

			// process line
			$ret.=__saveup_member_modify_line($mid,$member->line_id,$post->line_id);

			if ($simulate) {
				$ret.= '<p><strong>Member sql :</strong> '.db_query_cmd().'</p>';
			} else {
				model::watch_log('saveup','SaveUp Member Modify','<a href="'.url('saveup/member/view/'.$member->mid).'">member : '.$member->firstname.' '.$member->lastname.'</a> was modified');
				location('saveup/member/view/'.$mid);
				return $ret;
			}
		}
	} else {
		$post=clone($member);
		$post->date_approve = array();
		list($post->date_approve['year'],$post->date_approve['month'],$post->date_approve['date'])=explode('-',$member->date_approve);
		$post->birth = array();
		list($post->birth['year'],$post->birth['month'],$post->birth['date'])=explode('-',$member->birth);
	}
	if ($error) $ret.=message('error',$error);




	$form = new Form([
		'variable' => 'member',
		'action' => url(q()),
		'id' => 'edit-member',
		'class' => 'edit-member -sg-flex',
	]);

	$form->addField(
					'date_regist',
					array(
						'type' => 'text',
						'label' => 'วันที่สมัคร',
						'require' => true,
						'class' => 'sg-datepicker',
						'autocomplete' => 'off',
						'value' => $post->date_regist ? sg_date($post->date_regist, 'd/m/Y') : '',
						'container' => '{class: " -full"}'
					)
				);

	$form->addText('<fieldset id="personal" class="personal"><legend>ข้อมูลส่วนบุคคล</legend>');

	$form->addField(
					'idno',
					array(
						'type' => 'text',
						'label' => 'หมายเลขบัตรประชาชน(13 หลัก)',
						'maxlength' => 13,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->idno),
					)
				);

	$form->addField(
					'prename',
					array(
						'type' => 'text',
						'label' => 'คำนำหน้าชื่อ',
						'maxlength' => 15,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->prename),
					)
				);

	$form->addField(
					'firstname',
					array(
						'type' => 'text',
						'label' => 'ชื่อ',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->firstname),
					)
				);

	$form->addField(
					'lastname',
					array(
						'type' => 'text',
						'label' => 'นามสกุล',
						'maxlength' => 30,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->lastname),
					)
				);

	$form->addField(
					'nickname',
					array(
						'type' => 'text',
						'label' => 'ชื่อเล่น',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->nickname),
					)
				);

	$form->addField(
					'birth',
					array(
						'type' => 'date',
						'label' => 'วัน-เดือน-ปีเกิด',
						'require' => true,
						'year' => (object) array('range' => '-90,91', 'type' => 'BC'),
						'value' => (object)array('date' => $post->birth['date'], 'month' => $post->birth['month'], 'year' => $post->birth['year']),
					)
				);

	$form->addField(
					'address',
					array(
						'type' => 'text',
						'label' => 'ที่อยู่ (ตามทะเบียนบ้าน)',
						'maxlength' => 200,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->address),
					)
				);

	$form->addField(
					'amphure',
					array(
						'type' => 'text',
						'label' => 'อำเภอ',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->amphure),
					)
				);

	$form->addField(
					'province',
					array(
						'type' => 'text',
						'label' => 'จังหวัด',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->province),
					)
				);

	$form->addField(
					'zip',
					array(
						'type' => 'text',
						'label' => 'รหัสไปรษณีย์',
						'maxlength' => 5,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->zip)
					)
				);

	$form->addField(
					'caddress',
					array(
						'type' => 'text',
						'label' => 'ที่อยู่ (ที่ติดต่อได้)',
						'maxlength' => 200,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->caddress),
					)
				);

	$form->addField(
					'camphure',
					array(
						'type' => 'text',
						'label' => 'อำเภอ',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->camphure),
					)
				);

	$form->addField(
					'cprovince',
					array(
						'type' => 'text',
						'label' => 'จังหวัด',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->cprovince),
					)
				);

	$form->addField(
					'czip',
					array(
						'type' => 'text',
						'label' => 'รหัสไปรษณีย์',
						'maxlength' => 50,
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->czip),
					)
				);

	$form->addField(
					'phone',
					array(
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'maxlength' => 50,
						'class' => '-fill',
						'value' => htmlspecialchars($post->phone),
					)
				);

	$form->addText('</fieldset>');

	$form->addText('<fieldset id ="otherinfo" class="otherinfo"><legend>ข้อมูลอื่น ๆ</legend>');

	$form->addField(
					'occupa',
					array(
						'type' => 'text',
						'label' => 'อาชีพ',
						'maxlength' => 50,
						'class' => '-fill',
						'value' => htmlspecialchars($post->occupa),
					)
				);

	$form->addField(
					'mtype',
					array(
						'type' => 'radio',
						'label' => 'ความสัมพันธ์',
						'options' => array('1'=>'นักพัฒนา','2'=>'เพื่อนนักพัฒนา','3'=>'ญาติพี่น้อง'),
						'value' => $post->mtype,
					)
				);

	/*
	$form->addField(
					'userid',
					array(
						'type' => 'text',
						'label' => 'รหัสสมาชิกในระบบ',
						'maxlength' => 6,
						'value' => htmlspecialchars($post->userid),
					)
				);
				*/

	$lineOptions = array('' => '--ไม่มีสายสัมพันธ์--', -1 => '--สร้างสายสัมพันธ์--');
	foreach (saveup_model::get_lines()->items as $line)
		$lineOptions[$line->lid] = $line->name;
	$form->addField(
					'line_id',
					array(
						'type' => 'select',
						'label' => 'สายสัมพันธ์',
						'options' => $lineOptions,
						'value' => htmlspecialchars($post->line_id),
					)
				);

	$form->addField(
					'beneficiary_name',
					array(
						'type' => 'text',
						'label' => 'ผู้รับผลประโยชน์',
						'maxlength' => 50,
						'class' => '-fill',
						'value' => htmlspecialchars($post->beneficiary_name),
					)
				);

	$form->addField(
					'beneficiary_addr',
					array(
						'type' => 'text',
						'label' => 'ที่อยู่ (ผู้รับผลประโยชน์)',
						'maxlength' => 255,
						'class' => '-fill',
						'value' => htmlspecialchars($post->beneficiary_addr),
					)
				);

	$form->addField(
					'contact_name',
					array(
						'type' => 'text',
						'label' => 'ชื่อบุคคลที่ติดต่อได้',
						'maxlength' => 50,
						'class' => '-fill',
						'value' => htmlspecialchars($post->contact_name),
					)
				);

	$form->addField(
					'contact_id',
					array(
						'type' => 'text',
						'label' => 'หมายเลขสมาชิกของบุคคลที่ติดต่อได้ (กรณีเป็นสมาชิกของกลุ่ม)',
						'maxlength' => 6,
						'value' => htmlspecialchars($post->contact_id),
					)
				);

	$form->addField(
					'date_approve',
					array(
						'type' => 'date',
						'label' => 'วันที่อนุมัติ',
						'require' => true,
						'year' => (object) array('range' => (1989-date('Y')).','.(date('Y')-1989+1), 'type' => 'BC'),
						'value' => (object) array(
													'date' => $post->date_approve['date'],
													'month' => $post->date_approve['month'],
													'year' => $post->date_approve['year'],
												),
					)
				);

	$form->addField(
					'savepayperiod',
					array(
						'type' => 'select',
						'label' => 'ชำระเงินสัจจะราย:',
						'options' => [1 => 1, 6 => 6, 12 => 12],
						'value' => htmlspecialchars($post->savepayperiod),
						'posttext' => ' เดือน',
					)
				);

	$form->addField(
					'email',
					array(
						'type' => 'text',
						'label' => 'E-Mail',
						'class' => '-fill',
						'value' => htmlspecialchars($post->email),
						'placeholder' => 'Ex. name@example.com',
					)
				);

	$form->addField(
					'facebook',
					array(
						'type' => 'text',
						'label' => 'Facebook',
						'class' => '-fill',
						'value' => htmlspecialchars($post->facebook),
						'placeholder' => 'Ex. https://facebook.com/name',
					)
				);

	$form->addField(
					'status',
					array(
						'type' => 'radio',
						'label' => 'สถานภาพ',
						'options' => array('active' => 'เป็นสมาชิก','inactive' => 'ลาออกจากการเป็นสมาชิก'),
						'value' => $post->status,
					)
				);
	//		$ret. => print_o($post,'$post');

	$form->addField(
					'remark',
					array(
						'type' => 'textarea',
						'label' => 'หมายเหตุ',
						'class' => '-fill',
						'value' => htmlspecialchars($post->remark),
					)
				);

	$form->addText('</fieldset>');

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
						'pretext' => '<a class="btn -link -cancel" href="'.url('saveup/member/view/'.$post->mid).'"><i class="icon -cancel -gray"></i><span>CANCEL</span></a>',
						'container' => '{class: "-sg-text-right -full"}'
						)
					);

	$ret .= $form->build();

	return $ret;
}

function __saveup_member_modify_line($mid,$old_line,$new_line) {
	if ($new_line==-1) {
		// make new line
		$ret.='Create new line';
		mydb::query('DELETE FROM %saveup_line% WHERE `mid`=:mid LIMIT 1',':mid',$mid ); // remove old line
		mydb::query('INSERT INTO %saveup_line% (mid, lid, parent) VALUES (:mid, :mid, 0)',':mid',$mid); // make new line
		mydb::query('UPDATE %saveup_line% SET lid=:mid WHERE parent=:mid',':mid',$mid);
	} else if ($old_line && empty($new_line)) {
		// remove line
		$ret.='Remove line';
		mydb::query('DELETE FROM %saveup_line% WHERE `mid`=:mid LIMIT 1',':mid',$mid);
	} else if (empty($old_line) && $new_line) {
		// add to line
		$ret.='Add to line '.$new_line;
		mydb::query('INSERT INTO %saveup_line% (mid, lid, parent) VALUES (:mid, :new_line, :new_line)',':mid',$mid,':new_line',$new_line);
	} else if ($old_line && $new_line!=$old_line) {
		// change to line
		$ret.='Move to line '.$new_line;
		mydb::query('UPDATE %saveup_line% SET lid=:new_line, parent=:new_line WHERE mid=:mid LIMIT 1',':mid',$mid,':new_line',$new_line);
		mydb::query('UPDATE %saveup_line% SET lid=:new_line WHERE lid=:mid','mid',$mid,':new_line',$new_line);
	} else {
		$ret.='Line not change';
	}
	return $ret;
}
?>