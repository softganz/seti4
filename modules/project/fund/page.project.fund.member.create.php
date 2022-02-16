<?php
/**
* Project :: Create Fund Member
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/member.create
*/

$debug = true;

function project_fund_member_create($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$fundId = $fundInfo->fundid;

	$isCreateMember = $fundInfo->right->createMember;

	if (!$isCreateMember) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$ret = '<header class="header"><h3>เพิ่มสมาชิกใหม่</h3></header>';

	$data = (object)post();
	$stmt = 'SELECT MAX(`username`) `username` FROM %users% WHERE `username` LIKE :funduser AND LENGTH(`username`) = 10 LIMIT 1';
	$rs = mydb::select($stmt,':funduser',$fundId.'-%');
	// if (i()->username == 'softganz') $ret.=print_o($rs,'$rs');

	list($username,$lastid) = explode('-',$rs->username);
	if (empty($username)) $username = $fundId;
	$data->addusername = $username.'-'.sprintf('%04d',$lastid+1);
	$data->addpassword = substr(md5(uniqid()), 0, 8);
	$data->name = $data->addusername;

	$form=new Form(NULL,url('project/fund/'.$orgId.'/info/member.save'),NULL,'sg-form');
	$form->addData('rel','#main');
	//$form->adddata('done', 'load');

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
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>สร้างสมาชิกใหม่</span>',
			'pretext' => ' <a class="btn -link -cancel" href="'.url('project/fund/'.$orgId.'/info.member').'"><i class="icon -material">cancel</i>{tr:CANCEL}</a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>