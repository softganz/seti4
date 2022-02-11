<?php
/**
* Project :: Fund Board Beover
* Created 2020-06-10
* Modify  2020-06-10
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @call project/fund/$orgId/board.beover
*/

$debug = true;

function project_fund_board_beover($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');


	$ret = '<header class="header">'._HEADER_BACK.'<h3>บันทึกกรรมการชุดปัจจุบันหมดวาระ</h3></header>';

	$form = new Form(NULL, url('project/fund/'.$orgId.'/info/board.beover'), 'project-board-beover', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel','notify');
	$form->addData('done', 'back | load:.box-page');
	$form->addConfig('title', 'บันทึกกรรมการชุดปัจจุบันทุกคนออกจากการเป็นกรรมการเนื่องจากหมดวาระ');

	$form->addField(
		'dateout',
		array(
			'label' => 'วันที่หมดวาระ:',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'require' => true,
			'value' => SG\getFirst(post('dateout'),sg_date('d/m/Y')),
		)
	);

	$form->addField(
		'confirm',
		array(
			'type' => 'checkbox',
			'require' => true,
			'options' => array('yes' => '<strong>ใช่ ฉันต้องการบันทึกกรรมการทุกคนหมดวาระ</strong>'),
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'class' => '-danger',
			'value' => '<i class="icon -material">done_all</i><span>ยืนยันการหมดวาระ</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$form->addConfig('footer','<p style="color:red;">คำเตือน : หลังจากที่บันทึกให้กรรมการทุกคนออกจากการเป็นกรรมการเนื่องจากหมดวาระแล้ว รายชื่อกรรมการชุดปัจจุบันจะถูกย้ายไปเก็บไว้ในทำเนียบกรรมการต่อไป</p>');

	$ret .= $form->build();

	return $ret;
}
?>