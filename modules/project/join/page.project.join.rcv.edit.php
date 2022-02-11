<?php
/**
* Project Join Recieve
* Created 2019-02-20
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param Int $dopid
* @return String
*/

$debug = true;

function project_join_rcv_edit($self,  $projectInfo, $dopid = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit) return message('error', 'Access Denied');

	$dopaidInfo = R::Model('org.dopaid.doc.get', $dopid);

	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>ใบสำคัญรับเงิน</h3></header>';

	$form = new Form('rcv', url('project/join/'.$tpid.'/'.$calId.'/rcv.save/'.$psnid), NULL, 'sg-form');
	//$form->addData('complete', 'closebox');
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load:.box-page');

	$form->addField('dopid', array('type' => 'hidden', 'value' => $dopid));
	$form->addField('doid', array('type' => 'hidden', 'value' => $dopaidInfo->doid));
	$form->addField('agrno', array('type' => 'hidden', 'value' => $dopaidInfo->agrno));

	$form->addField(
		'paiddate',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'class' => 'sg-datepicker -fill',
			'value' => sg_date($dopaidInfo->paiddate,'d/m/Y'),
		)
	);

	$form->addField(
		'paidname',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้รับเงิน',
			'class' => '-fill',
			'value' => htmlspecialchars($dopaidInfo->paidname),
		)
	);

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'เลขประจำบัตรประชาชน',
			'class' => '-fill',
			'readonly' => true,
			'value' => htmlspecialchars($dopaidInfo->cid),
			)
	);

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class' => '-fill',
			'value' => htmlspecialchars($dopaidInfo->address),
		)
	);

	$rcvFormList = array('' => 'Default');
	foreach ($dopaidInfo->options->rcvForms as $key => $value) {
		$rcvFormList[$key] = $value->title;
	}

	if (count($rcvFormList) > 1) {
		$form->addField(
			'formid',
			array(
				'type' => 'select',
				'label' => 'แบบฟอร์ม:',
				'class' => '-fill',
				'options' => $rcvFormList,
				'value' => $dopaidInfo->formid,
			)
		);
	}


	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="sg-action btn -link" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$dopid).'" data-rel="back"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></a>',
			'containerclass' => '-sg-text-right',
		)
	);

	$ret .= $form->build();

	$ret .= '<p>หมายเหตุ : การแก้ไขข้อมูลใบสำคัญรับเงิน จะมีผลเฉพาะใบสำคัญรับเงินฉบับนี้เท่านั้น โดยจะไม่มีผลต่อข้อมูลการลงทะเบียน</p>';


	//$ret .= print_o($dopaidInfo, '$dopaidInfo');

	$ret.='<style type="text/css">
	.module-project .box {background-color: #fff;}
	.module-project .box h3 {text-align: center; background-color: transparent; color:#333;}
	.bill-trdetail {color:#666; font-size: 0.9em;}

	@media print {
		.module-project .box {margin:0; padding:0; box-shadow:none; border:none;}
		.module-project .box h3 {color:#000; background-color:#fff;}
		.module-project .-billsign {position:absolute; bottom:0.5cm;}
		.module-project .-footermsg {margin-bottom:1cm;}
		.module-project .bill-trdetail {color:#000; font-size: 0.9em;}
	}
	</style>';
	return $ret;
}
?>