<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/info.finance
*/

$debug = true;

function project_fund_info_finance($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	if (!$fundInfo->right->edit) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$data = $fundInfo->info;

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>ข้อมูลด้านบัญชี-การเงิน</h3></header>';

	$form = new Form('data', url('project/fund/'.$orgId.'/info/finance.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load:.box-page');

	$form->addField(
		'accbank',
		array(
			'type' => 'text',
			'label' => 'บัญชีธนาคารและสาขา',
			'require' => true,
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($data->accbank),
			'placeholder' => 'ชื่อธนาคาร',
		)
	);

	$form->addField(
		'accname',
		array(
			'type' => 'text',
			'label' => 'ชื่อบัญชี',
			'require' => true,
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($data->accname),
			'placeholder' => 'ชื่อบัญชี',
		)
	);

	$form->addField(
		'accno',
		array(
			'type' => 'text',
			'label' => 'เลขที่บัญชี',
			'require' => true,
			'maxlength' => 13,
			'value' => htmlspecialchars($data->accno),
			'placeholder' => 'เลขที่บัญชี',
		)
	);

	$form->addField(
		'opendate',
		array(
			'type' => 'text',
			'label' => 'วันที่ยกมา',
			'class' => 'sg-datepicker',
			'require' => true,
			'value' => $data->openbaldate ? sg_date($data->openbaldate, 'd/m/Y') : '',
			'placeholder' => '31/12/2560',
		)
	);

	$form->addField(
		'openbalance',
		array(
			'type' => 'text',
			'label' => 'ยอดเงินคงเหลือยกมา',
			'require' => true,
			'maxlength' => 20,
			'value' => $data->openbalance,
			'description' => '<span style="font-weight:bold;font-size:1.1em;color:#f60;">*** ป้อน "ยอดเงินคงเหลือยกมา" เพียงครั้งเดียวตอนเริ่มต้นใช้งานเท่านั้น!!!!!</span><br />ยอดเงินคงเหลือยกมา เป็นยอดเงินคงเหลือยกมา ณ วันที่ '.($data->openbaldate ? sg_date($data->openbaldate, 'ว ดดด ปปปป') : '').' เพื่อเป็นยอดเงินสำหรับนำมาใช้คำนวณในการยอดเงินคงเหลือในแต่ละรอบบัญชี',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
		)
	);

	$ret .= $form->build();
	return $ret;
}

?>