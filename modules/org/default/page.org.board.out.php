<?php
/**
* Project :: Fund Board Out
* Created 2020-06-10
* Modify  2020-06-10
*
* @param Object $self
* @param Object $orgInfo
* @param Int $brdid
* @return String
*
* @call project/fund/$orgId/board.out/$brdid
*/

$debug = true;

function org_board_out($self, $orgInfo, $brdid) {
	// Data Model
	if (!($orgId = $orgInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $orgInfo->is->editable)) return message('error', 'Access Denied');

	// Check board is exists and is org
	$brdRs = mydb::select(
		'SELECT * FROM %org_board% WHERE `brdid` = :brdid AND `orgid` = :orgid LIMIT 1',
		':brdid', $brdid,
		':orgid', $orgId
	);

	if ($brdRs->_empty) return message('error','ไม่มีข้อมูลตามที่ระบุ');


	// View Model
	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.$brdRs->name.' ออกจากการเป็นกรรมการ</h3></header>';

	$outCondList = model::get_category('board:status','catid');
	unset($outCondList[1]);

	// Show board out form
	$form = new Form([
		'action' => url('org/info/api/'.$orgId.'/board.out/'.$brdid),
		'id' => 'org-board-out',
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'close | load',
		'children' => [
			'dateout' => [
				'label' => 'วันที่ออก',
				'type' => 'text',
				'class' => 'sg-datepicker',
				'require' => true,
				'value' => sg_date('d/m/Y'),
			],
			'outcond' => [
				'label' => 'เงื่อนไขในการออกจากการเป็นกรรมการ:',
				'type' => 'radio',
				'require' => true,
				'options' => $outCondList,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกออกจากการเป็นกรรมการ</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/fund/'.$orgId.'/board').'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class' => '-sg-text-right'),
			],
		], // children
	]); // Form

	$ret .= $form->build();

	return $ret;
}
?>