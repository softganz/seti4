<?php
function project_org_member_create($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$isWebAdmin = i()->admin;
	$isAdmin = $orgInfo->info->isAdmin || (i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN')));
	$isOwner = $orgInfo->info->isOwner;
	$isTrainer = i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN','TRAINER'));

	$isEditable = $isAdmin || $isOwner || $isTrainer;
	$isEditTrainer = $isEdit || in_array('coretrainer', i()->roles) ;

	R::view('project.toolbar',$self,$orgInfo->name,'org',$orgInfo);

	if (!$isEditable) return $ret.'<p class="notify">{tr:Access denied}</p>';

	$data=(object)post();
	$preUsername = SG\getFirst($orgInfo->info->shortname,$orgInfo->orgid);
	$stmt='SELECT `username`, `name` FROM %users% WHERE `username` LIKE :funduser ORDER BY `uid` DESC LIMIT 1';
	$rs=mydb::select($stmt,':funduser',$preUsername.'-%');
	//$ret.=print_o($rs,'$rs');

	list($username,$lastid)=explode('-',$rs->username);
	if (empty($username)) $username = $preUsername;
	$data->addusername=$username.'-'.sprintf('%04d',$lastid+1);
	$data->addpassword=substr(md5(uniqid()), 0, 8);
	$data->name=$data->addusername;
	
	$ret.='<h2>เพิ่มสมาชิกใหม่</h2>';


	$form=new Form(NULL,url('project/org/'.$orgId.'/member.save'),NULL,'sg-form');
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
							'pretext'=>' <a class="btn -link -cancel" href="'.url('project/org/'.$orgInfo->orgid.'/member').'">ยกเลิก</a>',
							'container' => array('class'=>'-sg-text-right'),
						)
					);
	$ret.=$form->build();
	return $ret;
}
?>