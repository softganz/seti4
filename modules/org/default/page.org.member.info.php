<?php
/**
* Organization Member Information
*
* @param Object $self
* @param Int $psnid
* @return String
*/

function org_member_info($self, $psnid = NULL) {
	$personInfo = R::Model('org.person.get', $psnid);

	$info = $personInfo->info;

	//$ret .= print_o($personInfo, '$personInfo');

	$isAdmin = $personInfo->RIGHT & _IS_ADMIN;
	$isOwner = $personInfo->RIGHT & _IS_OWNER;
	$isAccess = $personInfo->RIGHT & _IS_ACCESS;
	$isEdit = $personInfo->RIGHT & _IS_EDITABLE;

	$myorg = org_model::get_my_org();
	$joins = org_model::get_memberjoin($psnid);
	$volunteer = org_model::get_volunteer($psnid);

	$self->theme->title=$fullname=SG\getFirst(trim($info->prename.' '.$info->name.' '.$info->lname),'????');

	if (!$personInfo) return $ret.message('error','ไม่มีข้อมูลสมาชิกรายการนี้ หรือไม่เคยเข้าร่วมกิจกรรมกับองค์กร');

	// ตรวขสอบสิทธิ์ในการแก้ไข หาก
	// - คนนี้เคยเข้าร่วมในกิจกรรมขององค์กร
	$isEdit = $isEdit;
	$showAll = $isAccess;

	$ret .= '<header class="header -box"><h3>รายละเอียดสมาชิก</h3></header>'._NL;

	if ($isEdit) {
		$inlineAttr['class']='sg-inline-edit';
		$inlineAttr['data-update-url']=url('org/edit/info/'.$psnid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="org-meeting-info" '.sg_implode_attr($inlineAttr).'>';

	$tables = new Table();
	$tables->addId('orgdb-member-info');
	$tables->colgroup=array('width="30%"','width="70%"');
	$tables->rows[]=array(
		'ชื่อ-สกุล',
		'<strong class="big">'
		.view::inlineedit(array('group'=>'person','fld'=>'prename','tr'=>$info->psnid, 'options'=>'{class: "w-1", placeholder: "คำนำหน้า"}'),$info->prename,$isEdit).' '
		.view::inlineedit(array('group'=>'person','fld'=>'name','tr'=>$info->psnid,'class'=>'w-5'),$info->name.' '.$info->lname,$isEdit).'</strong>'
	);
	$tables->rows[]=array(
		'ชื่อเล่น',
		view::inlineedit(array('group'=>'person','fld'=>'nickname','tr'=>$info->psnid,'class'=>'-fill'),$info->nickname,$isEdit)
	);
	if ($showAll) {
		$tables->rows[]=array(
			'หมายเลขบัตรประชาชน',
			view::inlineedit(array('group'=>'person','fld'=>'cid','tr'=>$info->psnid,'class'=>'-fill','options'=>'{maxlength:13}'),$info->cid,$isEdit)
		);
		$tables->rows[]=array(
			'วันเกิด',
			view::inlineedit(array('group'=>'person','fld'=>'birth','tr'=>$info->psnid,'ret'=>'date:ว ดด ปปปป','options'=>'{placeholder: "31/12/2510"}'),$info->birth,$isEdit,'datepicker')
		);
		$tables->rows[]=array(
			'ที่อยู่',
			view::inlineedit(
				array(
					'group'=>'person',
					'fld'=>'address',
					'tr'=>$info->psnid,
					'class'=>'-fill',
					'query'=>url('api/address'),
					'minlength'=>'5',
					'areacode'=>$info->changwat.$info->ampur.$info->tambon.str_pad($info->village,2,'0',STR_PAD_LEFT),
					'ret'=>'address',
					'options' => '{
							autocomplete: {
								minLength: 5,
								target: "areacode",
								query: "'.url('api/address').'"
							},
							placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
						}',
				),
				SG\implode_address($info),
				$isEdit,
				'autocomplete'
			)
		);
		$tables->rows[]=array(
			'รหัสไปรษณีย์',
			view::inlineedit(array('group'=>'person','fld'=>'zip','tr'=>$info->psnid,'class'=>'-fill'),$info->zip,$isEdit)
		);
		$tables->rows[]=array(
			'โทรศัพท์',
			'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'phone','tr'=>$info->psnid,'class'=>'-fill'),$info->phone,$isEdit).'</strong>'
		);
		$tables->rows[]=array(
			'อีเมล์',
			'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'email','tr'=>$info->psnid,'class'=>'-fill'),$info->email,$isEdit).'</strong>'
		);
	} else {
		unset($info->house);
		$tables->rows[]=array('ที่อยู่',SG\implode_address($info));

	}
	$tables->rows[]=array(
		'เว็บไซท์',
		view::inlineedit(array('group'=>'person','fld'=>'website','tr'=>$info->psnid,'class'=>'-fill'),$info->website,$isEdit)
	);
	$tables->rows[]=array(
		'เริ่มความสัมพันธ์เมื่อ',
		sg_date($info->joindate,'ว ดดด ปปปป')
	);
	$tables->rows[]=array('เข้าร่วมกิจกรรม','<strong>'.$joins->_num_rows.' ครั้ง</strong>');
	$ret .= $tables->build();

	if (cfg('org.install.volunteer') && $isEdit) {
		if ($volunteer) {
			$ret.='<a href="'.url('org/member/volunteer/remove/'.$psnid).'" class="button">ยกเลิกการเป็นอาสาสมัคร</a>';
		} else {
			$ret.='<a href="'.url('org/member/volunteer/add/'.$psnid).'" class="button">เพิ่มเป็นอาสาสมัคร</a>';
		}
	}

	// Volunteer information
	if ($volunteer) {
		$ret.='<h3>ข้อมูลอาสาสมัคร</h3>';

		$tables = new Table();
		$tables->addId('orgdb-member-volunteer');
		$tables->colgroup=array('width="30%"','width="70%"');
		$tables->rows[]=array(
			'หน่วยงานต้นสังกัด/คณะ',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'agency','tr'=>$volunteer['agency']->bigid,'class'=>'-fill'),$volunteer['agency']->flddata,$isEdit)
		);
		$tables->rows[]=array(
			'ชมรม/กลุ่ม',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'club','tr'=>$volunteer['club']->bigid,'class'=>'-fill'),$volunteer['club']->flddata,$isEdit)
		);
		$tables->rows[]=array(
			'บทบาทในคณะ/ชมรม/ต้นสังกัด',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'role','tr'=>$volunteer['role']->bigid,'class'=>'-fill'),$volunteer['role']->flddata,$isEdit)
		);
		$tables->rows[]=array(
			'กิจกรรมที่เคยเข้าร่วม',
			'ชื่อกิจกรรม<br />'.view::inlineedit(array('group'=>'bigdata:org','fld'=>'joinedactivity','tr'=>$volunteer['joinedactivity']->bigid,'class'=>'-fill'),$volunteer['joinedactivity']->flddata,$isEdit)
			.'<br />สถานที่<br />'.view::inlineedit(array('group'=>'bigdata:org','fld'=>'joinedlocation','tr'=>$volunteer['joinedlocation']->bigid,'class'=>'-fill'),$volunteer['joinedlocation']->flddata,$isEdit)
		);
		$tables->rows[]=array(
			'ประเภทงานอาสาสมัครที่มีความสามารถ/มีความชำนาญ หรือเป็นงานอดิเรก',
			view::inlineedit(array('group'=>'person','fld'=>'aptitude','tr'=>$psnid,'class'=>'-fill','ret'=>'html'),$info->aptitude,$isEdit,'textarea')
		);
		$tables->rows[]=array(
			'ประเภทงานอาสาสมัครที่สนใจ',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:teacher','tr'=>$volunteer['interest:teacher']->bigid,'value'=>$volunteer['interest:teacher']->flddata),'1:ครูอาสา',$isEdit,'checkbox').' ถนัดวิชา '.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:subject','tr'=>$volunteer['interest:subject']->bigid,'class'=>'w-5'),$volunteer['interest:subject']->flddata,$isEdit).'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:disaster','tr'=>$volunteer['interest:disaster']->bigid,'value'=>$volunteer['interest:disaster']->flddata),'2:ด้านการจัดการภัยพิบัติ',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:environment','tr'=>$volunteer['interest:environment']->bigid,'value'=>$volunteer['interest:environment']->flddata),'3:ด้านอนุรักษ์สิ่งแวดล้อม',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:swiming','tr'=>$volunteer['interest:swiming']->bigid,'value'=>$volunteer['interest:swiming']->flddata),'4:ด้านการเป็นครูฝึกการว่ายน้ำเพื่อเอาชีวิตรอด',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:child','tr'=>$volunteer['interest:child']->bigid,'value'=>$volunteer['interest:child']->flddata),'5:กิจกรรมกับเด็ก',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:community','tr'=>$volunteer['interest:community']->bigid,'value'=>$volunteer['interest:community']->flddata),'6:ด้านการพัฒนาชุมชน',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:healthcare','tr'=>$volunteer['interest:healthcare']->bigid,'value'=>$volunteer['interest:healthcare']->flddata),'7:เยี่ยมผู้ป่วย',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:other','tr'=>$volunteer['interest:other']->bigid,'value'=>$volunteer['interest:other']->flddata),'8:ด้านอื่น ๆ',$isEdit,'checkbox').' ระบุ '.view::inlineedit(array('group'=>'bigdata:org','fld'=>'interest:specify','tr'=>$volunteer['interest:specify']->bigid,'class'=>'w-5'),$volunteer['interest:specify']->flddata,$isEdit).'<br />'
		);
		$tables->rows[]=array(
			'ช่วงเวลาที่สะดวกในการทำกิจกรรม',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'time:morning','tr'=>$volunteer['time:morning']->bigid,'value'=>$volunteer['time:morning']->flddata),'morning:ช่วงเช้า 08.00-12.00',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'time:afternoon','tr'=>$volunteer['time:afternoon']->bigid,'value'=>$volunteer['time:afternoon']->flddata),'afternoon:ช่วงบ่าย 12.00-16.00',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'time:evening','tr'=>$volunteer['time:evening']->bigid,'value'=>$volunteer['time:evening']->flddata),'evening:ช่วงเย็น 1600-20.00',$isEdit,'checkbox').'<br />'
			.view::inlineedit(array('group'=>'bigdata:org','fld'=>'time:other','tr'=>$volunteer['time:other']->bigid,'value'=>$volunteer['time:other']->flddata),'other:ช่วงอื่น ๆ',$isEdit,'checkbox').' ระบุ '.view::inlineedit(array('group'=>'bigdata:org','fld'=>'time:specify','tr'=>$volunteer['time:specify']->bigid,'class'=>'w-5'),$volunteer['time:specify']->flddata,$isEdit).'<br />'
		);
		$tables->rows[]=array(
			'ประสบการณ์งานอาสาสมัคร',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'experience','tr'=>$volunteer['experience']->bigid,'class'=>'-fill','ret'=>'html'),$volunteer['experience']->flddata,$isEdit,'textarea')
		);
		$tables->rows[]=array(
			'แรงบันดาลใจของท่านต่องานอาสาสมัคร',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'inspiration','tr'=>$volunteer['inspiration']->bigid,'class'=>'-fill','ret'=>'html'),$volunteer['inspiration']->flddata,$isEdit,'textarea')
		);
		$tables->rows[]=array(
			'หน่วยงานอาสาสมัครในเครือข่ายที่รู้จัก',
			view::inlineedit(array('group'=>'bigdata:org','fld'=>'knownagency','tr'=>$volunteer['knownagency']->bigid,'class'=>'-fill','ret'=>'html'),$volunteer['knownagency']->flddata,$isEdit,'textarea')
		);

		$ret .= $tables->build();
	}
	$ret.='</div>';

	/*
	ชมรม/กลุ่มของท่าน
	บทบาทท่านในคณะ/ชมรม/ต้นสังกัด
	ท่านเคยเข้าร่วมกิจกรรม/ค่าย อาสาสมัครหรือไม่
		เคยเข้าร่วมในกิจกรรม ....................สถานที่.................................................
		ไม่เคย
	ประเภทงานอาสาสมัครที่ท่านมีความสามารถ/มีความชำนาญ หรือเป็นงานอดิเรกของท่าน
		1. ..................................................
		2. ..................................................
	ประเภทงานอาสาสมัครที่ท่านสนใจ (ตอบได้มากกว่า 1 ข้อ)
	ครูอาสา ถนัดวิชา...................................     		ด้านการจัดการภัยพิบัติ
	ด้านอนุรักษ์สิ่งแวดล้อม 				ด้านการเป็นครูฝึกการว่ายน้ำเพื่อเอาชีวิตรอด
	ทำกิจกรรมกับเด็ก 				ด้านการพัฒนาชุมชน
	เยี่ยมผู้ป่วย 					ด้านอื่นๆ ..................................................................
	ช่วงเวลาที่สะดวกในการทำกิจกรรม
		ช่วงเช้า 08.00-12.00
		ช่วงบ่าย 12.00-16.00
		ช่วงเย็น 1600-20.00
		ช่วงอื่นๆ ระบุ...........................................................
	ประสบการณ์งานอาสาสมัครของท่าน ....................................................................
	แรงบันดาลใจของท่านต่องานอาสาสมัคร ................................................................
	หน่วยงานอาสาสมัครในเครือข่ายที่ท่านรู้จัก .............................................................
	ท่านสนใจเข้าร่วมเป็นสมาชิกหน่วยหรือไม่
		เข้าร่วม
		ยังไม่สนใจ เพราะ...........................................
	*/


	$stmt='SELECT m.*, o.`name` FROM %org_morg% m LEFT JOIN %db_org% o USING(`orgid`) WHERE `psnid`=:psnid';
	$orgMember=mydb::select($stmt,':psnid',$personInfo->psnid);

	if ($orgMember->count()) {
		$tables = new Table();
		$tables->thead=array('องค์กร','ตำแหน่ง','แผนก','date'=>'เมื่อวันที่');
		foreach ($orgMember->items as $item) {
			$tables->rows[]=array(
				'<a href="'.url('org/'.$item->orgid).'">'.$item->name.'</a>',
				$item->position,
				$item->department,
				sg_date($item->joindate,'ว ดดด ปปปป'),
			);
		}
		$ret .= $tables->build();
	}


	if ($info->location) {
		$ret.='<iframe id="orgdb-member-info-map" width="400" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://softganz.com/gis/point/'.$info->location.'/type/map?title='.$info->name.' '.$info->lname.'"></iframe>';
	}

	$ret.=R::View('org.meeting.doings',$psnid);

	$ret .= '<p>Create by '.$info->created_by.' @'.sg_date($info->created_date,'ว ดด ปปปป').($personInfo->right->zone ? ' right '.$personInfo->right->zone->right.' in zone '.$personInfo->right->zone->zone : '').'</p>';
	//$ret.=print_o($info,'$info').print_o($joins,'$joins').print_o($volunteer,'$volunteer');
	return $ret;
}
?>