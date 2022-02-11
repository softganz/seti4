<?php
/**
* Project :: Fund Board Out
* Created 2020-06-10
* Modify  2020-06-10
*
* @param Object $self
* @param Object $fundInfo
* @param Int $brdid
* @return String
*
* @call project/fund/$orgId/board.out/$brdid
*/

$debug = true;

function project_fund_board_out($self, $fundInfo, $brdid) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');



	// Check board is exists and is org
	$stmt = 'SELECT * FROM %org_board% WHERE `brdid` = :brdid AND `orgid` = :orgid LIMIT 1';
	$brdRs = mydb::select($stmt,':brdid',$brdid, ':orgid',$orgId);

	if ($brdRs->_empty) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.$brdRs->name.' ออกจากการเป็นกรรมการ</h3></header>';

	// Show board out form
	$form = new Form(NULL, url('project/fund/'.$orgId.'/info/board.out/'.$brdid), 'project-board-out', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$outCondList = model::get_category('boardstatus','catid');
	unset($outCondList[1]);

	$form->addField(
		'outcond',
		array(
			'label' => 'เงื่อนไขในการออกจากการเป็นกรรมการ:',
			'type' => 'radio',
			'require' => true,
			'options' => $outCondList,
		)
	);

	$form->addField(
		'dateout',
		array(
			'label' => 'วันที่ออก',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'require' => true,
			'value' => sg_date('d/m/Y'),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกออกจากการเป็นกรรมการ</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/fund/'.$orgId.'/board').'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>