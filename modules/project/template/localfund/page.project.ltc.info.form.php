<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_ltc_info_form($self, $fundInfo) {
	$orgId = $fundInfo->orgid;

	if (!$fundInfo->right->edit) return message('error', 'Access Denied');

	$ret = '';

	$fundid = $fundInfo->fundid;


	$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "project.ltc" AND `keyid` = :orgId AND `fldname` = "info.contact" LIMIT 1';
	$rs = mydb::select($stmt, ':orgId', $orgId);
	$data = sg_json_decode($rs->flddata);
	//$ret .= print_o($data,'$data');

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>ข้อมูลกองุทน LTC</h3></header>';

	$form = new Form('data', url('project/ltc/'.$orgId.'/info/save/'.$rs->bigid), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load:.box-page');

	$form->fundname=array('type'=>'textfield','value'=>'<strong><big><em>ชื่อกองทุน LTC : '.$fundInfo->name.' อำเภอ'.$fundInfo->info->nameampur.' จังหวัด'.$fundInfo->info->namechangwat.'</em></big></strong>');

	$form->addField(
		'contactname',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้ประสานงาน',
			'require' => true,
			'class' => '-fill',
			'value' => $data->contactname,
		)
	);

	$form->addField(
		'orgphone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => $data->orgphone,
		)
	);

	$form->addField(
		'orgfax',
		array(
			'type' => 'text',
			'label' => 'โทรสาร',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => $data->orgfax,
		)
	);

	$form->addField(
		'orgemail',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => $data->orgemail,
		)
	);

	$openYears = array(-1 => '== เลือกปีงบประมาณ ==');
	for ($i = 2016; $i <= date('Y')+1; $i++) $openYears[$i]='พ.ศ. '.($i+543);

	$form->addField(
		'openyear',
		array(
			'type' => 'select',
			'label' => 'จัดตั้งเมื่อปี พ.ศ.:',
			'require' => true,
			'options' => $openYears,
			'value' => $data->openyear,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);


	$ret .= $form->build();

	return $ret;
}
?>