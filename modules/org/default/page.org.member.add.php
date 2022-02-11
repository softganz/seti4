<?php
function org_member_add($self) {
	$self->theme->title='เพิ่มสมาชิกใหม่';
	$myorg=org_model::get_my_org();
	if (!$myorg) return message('error','ท่านยังไม่ได้กำหนดองค์กร');
	$post=(object)post('person');

	// Add person
	if ($post->orgid && $post->fullname) {
		list($name,$lname)=sg::explode_name(' ',$post->fullname);
		$post->uid=SG\getFirst(i()->uid,NULL);
		$isDupName=mydb::select('SELECT `psnid` FROM %db_person% WHERE `name`=:name AND `lname`=:lname LIMIT 1',':name',$name, ':lname',$lname)->psnid;
		if ($isDupName) $post->psnid=$isDupName;
		else {
			$post->name=$name;
			$post->lname=$lname;
			$post->birth=$post->birth?sg_date($post->birth,'Y-m-d'):NULL;
			$post->created=date('U');
			$addr=SG\explode_address($post->address);
			$post->house=$addr['house'];
			$post->village=$addr['village'];
			$post->zip=$addr['zip'];
			$post->tambon=substr($post->areacode,4,2);
			$post->ampur=substr($post->areacode,2,2);
			$post->changwat=substr($post->areacode,0,2);

			if (empty($post->village)) $post->village='';
			if (empty($post->tambon)) $post->tambon='';
			if (empty($post->ampur)) $post->ampur='';
			if (empty($post->changwat)) $post->changwat='';

			$stmt='INSERT INTO %db_person%
								(	`uid`, `prename`, `name`, `lname`, `nickname`,
									`birth`,
									`house`, `village`, `tambon`, `ampur`, `changwat`, `zip`,
									`phone`, `email`, `website`, `created`
								)
							VALUES
								(	:uid, :prename, :name, :lname, :nickname,
									:birth,
									:house, :village, :tambon, :ampur, :changwat, :zip,
									:phone, :email, :website, :created
								)';
			mydb::query($stmt, $post);
			if (!mydb()->_error) {
				$post->psnid=mydb()->insert_id;
				$msg[]='เพิ่มชื่อบุคคใหม่ "'.$post->prename.' '.$post->name.' '.$post->lname.'"';
			}
		}
		// Add person to join organization
		if ($post->psnid) {
			$post->joindate=date('Y-m-d');
			$post->created=date('Y-m-d H:i:s');
			$stmt='INSERT INTO %org_mjoin%
								(`orgid`, `psnid`, `uid`, `joindate`, `created`)
							VALUES
								(:orgid, :psnid, :uid, :joindate, :created)
							ON DUPLICATE KEY UPDATE `psnid`=:psnid';
			mydb::query($stmt,$post);
			//$ret.='Query='.mydb()->_query;
		}
		if ($post->psnid && $post->volunteer) {
			bigdata::addField('volunteer',1,'int','org',$post->psnid);
		}
		if ($post->psnid) location('org/member/info/'.$post->psnid);
	}

	if (empty($post->fullname)) $post->fullname=$name;

	$form = new Form('person', url(q()), 'org-add-person');

	$form->addConfig('title', 'เพิ่มรายชื่อใหม่');
	//		$form->config->class='sg-form';
	//		$form->config->attr='data-rel="#org-join-list"';

	$form->addField('areacode', array('type' => 'hidden', 'value' => htmlspecialchars($post->areacode)));

	$optionsOrg = array();
	$orgdbs=mydb::select('SELECT `orgid`, `name` FROM %db_org% WHERE `orgid` IN (:orgid)',':orgid','SET-STRING:'.$myorg);
	foreach ($orgdbs->items as $item) $optionsOrg[$item->orgid]=$item->name;

	$form->addField(
					'orgid',
					array(
						'type' => 'select',
						'class' => '-fill',
						'label' => 'รายชื่อใหม่สำหรับองค์กร',
						'options' => $optionsOrg,
						'value' => htmlspecialchars($post->orgid),
					)
				);

	$form->addField(
					'prename',
					array(
						'type' => 'text',
						'label' => 'คำนำหน้านาม',
						'class' => '-fill',
						'value' => htmlspecialchars($post->prename),
					)
				);

	$form->addField(
					'fullname',
					array(
						'type' => 'text',
						'label' => 'ชื่อ - นามสกุล',
						'require' => true,
						'class' => '-fill',
						'value' => htmlspecialchars($post->fullname),
					)
				);

	$form->addField(
					'nickname',
					array(
						'type' => 'text',
						'label' => 'ชื่อเล่น',
						'class' => '-fill',
						'value' => htmlspecialchars($post->nickname),
					)
				);

	$form->addField(
					'birth',
					array(
						'type' => 'text',
						'label' => 'วันเกิด',
						'class' => 'sg-datepicker -fill',
						'value' => htmlspecialchars($post->birth),
					)
				);

	$form->addField(
					'address',
					array(
						'type' => 'text',
						'label' => 'ที่อยู่',
						'class' => 'sg-address -fill',
						'attr' => 'data-altfld="edit-person-areacode"',
						'value' => htmlspecialchars($post->address),
						'placeholder' => 'เช่น 23/1 ม.4 ต.คลองแห แล้วเลือกจากรายการที่แสดง',
					)
				);

	$form->addField(
					'phone',
					array(
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'value' => htmlspecialchars($post->phone),
					)
				);

	$form->addField(
					'email',
					array(
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'value' => htmlspecialchars($post->email),
					)
				);

	$form->addField(
					'website',
					array(
						'type' => 'text',
						'label' => 'เฟซบุ๊ค',
						'class' => '-fill',
						'value' => htmlspecialchars($post->website),
					)
				);

	if (cfg('org.install.volunteer')) {
		$form->addField(
						'volunteer',
						array(
							'type' => 'checkbox',
							'options' => array('yes' => '<strong>ลงทะเบียนเป็นอาสาสมัคร</strong>'),
							'value' => htmlspecialchars($post->volunteer),
						)
					);
	}

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>บันทึกรายชื่อใหม่</span>',
						'pretext' => '<a class="btn -link -cancel" href="'.url('org/member').'"><i class="icon -cancel -gray"></i><span>CANCEL</span></a> ',
						'containerclass' => '-sg-text-right',
					)
				);

	$ret .= $form->build();
	//$ret.=print_o($post,'$post');
	return $ret;
}
?>