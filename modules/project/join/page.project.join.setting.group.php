<?php
/**
* Project Action Join Setting
* Created 2019-02-28
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calid
* @return String
*/

$debug = true;

function project_join_setting_group($self, $tpid, $calid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');
	$tpid = $projectInfo->tpid;


	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isAdmin) return message('error', 'access denied');



	$doingInfo = R::Model('org.doing.get', array('calid' => $calid), '{data: "info"}');


	$ret .= '<h3>การเบิกจ่าย ค่าใช้จ่ายในการเดินทางและที่พัก</h3>';

	$form = new Form(NULL, url('project/edit/tr/'.$tpid),NULL,'sg-form');
	$form->addData('rel','notify:ดำเนินการเรียบร้อย');
	$form->addField('group',array('type'=>'hidden','value'=>'doings'));
	$form->addField('tpid',array('type'=>'hidden','value'=>$tpid));
	$form->addField('tr',array('type'=>'hidden','value'=>$doingInfo->doid));
	$form->addField('fld',array('type'=>'hidden','value'=>'paidgroup'));
	$form->addField('action',array('type'=>'hidden','value'=>'save'));
	//$form->addField('debug',array('type'=>'hidden','value'=>'yes'));
	$form->addField('preservtab',array('type'=>'hidden','value'=>'yes'));

	$form->addText('<a class="btn" href="javascript:void(0)" onclick="projectJoinSettingImportFromDefault()">นำเข้าจากค่า Default</a> <a class="sg-action btn" href="'.url('project/'.$tpid.'/join.setting.group.import').'" data-rel="box">นำเข้าจากกิจกรรมอื่น</a>');
	$form->addField(
		'value',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 20,
			'value' => $doingInfo->paidgroup,
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i>{tr:SAVE}',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();


	// Example of join group
	$joinGroupJson = '{
	"เครือข่าย สช.":"เบิกจ่ายจาก สช.",
	"สปสช.เขต 11":"เบิกจ่ายจาก สปสช.เขต 11",
	"สปสช.เขต 12":"เบิกจ่ายจาก สปสช.เขต 12",
	"สปสช.ส่วนกลาง":"เบิกจ่ายจาก สปสช.ส่วนกลาง",
	"เครือข่ายสื่อ":"เบิกจ่ายจาก เครือข่ายสื่อ",
	"ต้นสังกัด":"เบิกจ่ายจาก ต้นสังกัด",
	"บุคคลทั่วไป":"ไม่เบิกจ่าย สำหรับบุคคลทั่วไป",
	"เบิกจ่ายจาก สจรส. ม.อ.": {
		"เครือข่ายเด็กและเยาวชน":"เครือข่ายเด็กและเยาวชน",
		"เครือข่ายท่องเที่ยว":"เครือข่ายท่องเที่ยว",
		"เครือข่ายชุมน่าอยู่":"เครือข่ายชุมน่าอยู่",
		"เครือข่ายอาหาร/เกษตร":"เครือข่ายอาหาร/เกษตร",
		"เครือข่ายภัยพิบัติ":"เครือข่ายภัยพิบัติ",
		"เครือข่ายปัจจัยเสี่ยง":"เครือข่ายปัจจัยเสี่ยง",
		"เครือข่ายห้องสุขภาพ":"เครือข่ายห้องสุขภาพ",
		"เครือข่ายแพทย์พหุวัฒนธรรม":"เครือข่ายแพทย์พหุวัฒนธรรม",
		"แผนงาน ศวสต.":"แผนงาน ศวสต.",
		"แผนอาหารจังหวัดสงขลา":"แผนอาหารจังหวัดสงขลา",
		"แผนงานพัฒนาศักยภาพภาคีเครือข่าย":"แผนงานพัฒนาศักยภาพภาคีเครือข่าย",
		"ผู้เข้าร่วมประชุมวิชาการ/นักวิชาการ":"ผู้เข้าร่วมประชุมวิชาการ/นักวิชาการ",
		"วิทยากร/คณะทำงาน":"วิทยากร/คณะทำงาน"
	}
}';

	/*
	$inlineAttr['class'] = 'sg-inline-edit';
	$inlineAttr['data-tpid'] = $tpid;
	$inlineAttr['data-update-url'] = url('project/edit/tr');
	if (1||post('debug')=='inline') $inlineAttr['data-debug'] = 'yes';

	$ret.='<div id="project-join-setting" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= view::inlineedit(
		array('group' => 'doings', 'fld' => 'paidgroup', 'key'=>'paidgroup', 'tr' => $doingInfo->doid, 'class' => '-fill', 'label' => 'กลุ่มสำหรับเบิกจ่าย', 'ret' => 'text', 'preservtab' => 'yes'),
		$doingInfo->paidgroup,
		$isEdit,
		'textarea'
	);

	$ret .= '<style type="text/css">
	.inline-edit-item>.inline-edit-label {font-weight: bold; display: block;}
	</style>';
	*/

	$ret .= '</div><!-- setting -->';

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo);
	$ret .= '<label>ค่า Default</label><textarea id="default" class="form-text -fill" rows="20">'.sg_json_encode(cfg('project.join.group')).'</textarea>';

	$ret .= '<script type="text/javascript">
	function projectJoinSettingImportFromDefault() {
		$("#edit-value").val($("#default").val())
	}
	</script>';
	return $ret;
}
?>