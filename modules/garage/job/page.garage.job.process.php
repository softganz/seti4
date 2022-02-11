<?php
/**
* Job Process
* Created 2019-08-20
* Modify  2019-08-20
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function garage_job_process($self, $jobInfo) {
	$tpid = $jobInfo->tpid;
	$jobProcessList = GarageVar::$jobProcessList;

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$ret .= '<header class="header -box"><h3>บันทึกสถานะการซ่อม</h3></header>';

	$form = new Form(NULL, url('garage/job/'.$tpid.'/info/process.save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField(
		'status',
		array(
			'type' => 'radio',
			'label' => 'สถานะการซ่อม:',
			'options' => $jobProcessList,
			'value' => $jobInfo->jobprocess,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($jobInfo, '$jobInfo');
	return $ret;
}
?>