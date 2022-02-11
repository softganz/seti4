<?php
/**
* Project : Fund Board Edit
* Created 2020-06-10
* Modify  2020-06-10
*
* @param Object $self
* @param Object $fundInfo
* @param Int $tranId
* @return String
*
* @call project/fund/$orgId/board.edit/$tranId
*/

$debug = true;

function project_fund_board_edit($self, $fundInfo, $tranId) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');

	$ret = R::Page('project.fund.board.new', $self, $fundInfo, $tranId);

	return $ret;
}
?>