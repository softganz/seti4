<?php
function project_org_member($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$isWebAdmin = i()->admin;
	$isAdmin = $orgInfo->info->isAdmin || (i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN','MANAGER')));
	$isOwner = $orgInfo->info->isOwner;
	$isTrainer = i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN','TRAINER'));

	$isEditable = $isAdmin || $isOwner || $isTrainer;
	$isEditTrainer = $isEdit || in_array('coretrainer', i()->roles) ;

	R::view('project.toolbar',$self,$orgInfo->name,'org',$orgInfo);

	//if (!$isEditable) return $ret.'<p class="notify">{tr:Access denied}</p>';

	
	$ret .= '<h2>เจ้าหน้าที่/สมาชิกองค์กร</h2>';

	if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/org/'.$orgId.'/member.create').'" data-rel="#main"><i class="icon -person-add -white"></i></a></div>';
	}


	$stmt = 'SELECT
					u.`uid`, u.`username`, u.`name`, u.`email`
					, UPPER(tu.`membership`) `membership`, u.`datein`
					FROM %org_officer% tu
						LEFT JOIN %users% u USING(`uid`)
						WHERE tu.`orgid`=:orgid AND u.`status`="enable"
						ORDER BY FIELD(tu.`membership`,"ADMIN","MANAGER","OWNER","OFFICER","TRAINER","MEMBER","REGULAR MEMBER") ASC, CONVERT(`name` USING tis620) ASC
						';
	$member = mydb::select($stmt,':orgid',$orgInfo->orgid);
	//$ret.=print_o($member,'$member');

	$tables = new Table();
	$tables->addId('project-develop-member-list');
	$tables->addClass('project-develop-member-list');
	$tables->thead = array('photo -center'=>'', 'name x-fill' => 'ชื่อสมาชิก','ship -center -hover-parent'=>'กลุ่ม');


	$isViewMemberProfile=$isEditable;
	//$ret.='<ul class="project__member">'._NL;
	foreach ($member->items as $rs) {
		$menu='';
		if ($isEditable) {
			$menu='<a class="sg-action" href="'.url('project/org/member/'.$orgInfo->orgid.'/remove/'.$rs->uid).'" title="ลบ" data-rel="none" data-removeparent="tr" data-title="ลบเจ้าหน้าที่/สมาชิกองค์กร" data-confirm="ต้องการลบเจ้าหน้าที่/สมาชิกออกจากองค์กร กรุณายืนยัน?"><i class="icon -cancel"></i></a> ';
		}
		/*
		 else if (in_array($rs->membership,array('OWNER','MEMBER'))) {
			//
		} else if (in_array($rs->membership,array('ADMIN','OFFICER','MANAGER','TRAINER'))) {
			if ($isAdmin || $isOwner) {
				$menu='<a class="sg-action" href="'.url('project/org/'.$orgInfo->orgid.'/removemember/'.$rs->uid).'" title="ลบ" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?"><i class="icon -cancel"></i></a> ';
			}
		} else {
			$menu=$isEditTrainer?'<a class="sg-action" href="'.url('project/org/'.$orgInfo->orgid.'/removemember/'.$rs->uid).'" title="ลบ" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?"><i class="icon -cancel"></i></a> ':'';
		}
		*/

		$ui = new Ui();
		if ($isEditable) {
			$ui->add('<a class="sg-action" href="'.url('profile/'.$rs->uid).'" data-rel="box" data-width="480"><i class="icon -material">find_in_page</i></a>');
		}
		if ($isEditable && $rs->topicUid != $rs->uid) {
			$ui->add('<a class="sg-action" href="'.url('project/org/'.$orgId.'/member.remove/'.$rs->uid).'" data-rel="notify" data-removeparent="tr" data-title="ลบชื่อออกจากองค์กร"  data-confirm="ต้องการลบชื่อออกจากองค์กร กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>');
		}
		$menu = $ui->count() ? '<nav class="nav -icons -hover">'.$ui->build().'</nav>' : '';

		$tables->rows[] = array(
			'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username).'" width="29" height="29" alt="'.htmlspecialchars($rs->name).'" title="'.htmlspecialchars($rs->name).'" /></a>',
			$rs->name
			.($rs->uid == i()->uid ? ' (is you)' : '')
			.($isEditable ? '<br /><span class="email">'.$rs->email.' ('.$rs->username.')</span>' : '')
			.'</a>',
			($orgInfo->info->uid == $rs->uid ? 'Is ' : '').$rs->membership
			.$menu,
		);

	}
	$ret.=$tables->build();
	//$ret.='</form>';

	if ($isEditable) {
		$ret .= 'Add people:';
		$form = new Form(NULL, url('project/org/'.$orgId.'/member.add'),'add-owner', 'sg-form project-member-form');
		$form->addData('rel','main');
		$form->addData('ret',url('project/org/'.$orgId.'/member'));
		$form->addData('width', 512);

		$form->addField('uid',array('type'=>'hidden','name'=>'uid', 'id'=>'uid'));

		$form->addField(
						'name',
						array(
							'type'=>'text',
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($name),
							'placeholder'=>'ระบุ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการเพิ่มเป็นเจ้าหน้าที่/สมาชิกองค์กร',
							'attr'=>array(
												'data-query'=>url('api/user',array('org'=>$orgId)),
												//'data-callback' => 'submit',
												'data-altfld' => 'uid',
											),
						)
					);
		$form->addField(
						'membership',
						array(
							'type' => 'select',
							'options' => array('ADMIN'=>'ADMIN','MANAGER'=>'MANAGER','TRAINER'=>'TRAINER','OFFICER'=>'OFFICER','REGULAR MEMBER'=>'REGULAR MEMBER'),
							'value' => 'REGULAR MEMBER',
						)
					);

		$form->addField(
						'button',
						array(
							'type' => 'button',
							'value' => '<i class="icon -save -white"></i>',
						)
					);

		$ret .= $form->build();
	}

	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '<style type="text/css">
	.project-develop-member-list .email {font-size: 0.8em; color: gray;}
	.project-develop-member-list td:nth-child(1) {width: 29px;}
	.project-develop-member-list td:nth-child(3) {white-space: nowrap;}
	.project-develop-member-list td:nth-child(4) {width: 29px;}
	.project-member-form {display: flex; justify-content: space-between;}
	.project-member-form .form-item {margin-right: 4px;}
	.project-member-form .form-item.-edit-name {width:100%;}
	</style>';

	return $ret;
}


function __project_fund_member_form($orgInfo,$data) {
	$ret.='<h2>เพิ่มสมาชิกใหม่</h2>';


	$form=new Form(NULL,url('project/org/member/'.$orgInfo->orgid.'/save'),NULL,'sg-form');
	$form->addData('rel','#main');
	$form->addField(
						'addusername',
						array(
							'type'=>'text',
							'label'=>'Username',
							'class'=>'-fill',
							'require'=>true,
							'readonly'=>true,
							'value'=>$data->addusername,
						)
					);
	$form->addField(
						'addpassword',
						array(
							'type'=>'text',
							'label'=>'Password',
							'class'=>'-fill',
							'require'=>true,
							'value'=>$data->addpassword,
						)
					);
	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'ชื่อ-นามสกุล',
							'class'=>'-fill',
							'value'=>$data->name,
						)
					);
	$form->addField(
						'email',
						array(
							'type'=>'text',
							'label'=>'อีเมล์',
							'class'=>'-fill',
							'value'=>$data->email,
							'placeholder'=>'name@example.com',
						)
					);
	$form->addField(
						'phone',
						array(
							'type'=>'text',
							'label'=>'โทรศัพท์',
							'class'=>'-fill',
							'value'=>$data->phone,
						)
					);
	$form->addField(
						'save',
						array(
							'type'=>'button',
							'value'=>'<i class="icon -save -white"></i><span>สร้างสมาชิกใหม่</span>',
							'posttext'=>' <a href="'.url('project/org/member/'.$orgInfo->orgid).'">ยกเลิก</a>',
						)
					);
	$ret.=$form->build();
	return $ret;
}
?>