<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_proposal_info_plan_rawdata($self, $proposalInfo, $tranId) {
	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;

	$ret = '<header class="header"><h3>Plan Data</h3></header>';

	if ($isAdmin) {

		$rs = $proposalInfo->activity[$tranId];

		$rs->created = sg_date($rs->created,'Y-m-d H:i:s');
		if ($rs->modified) $rs->modified = sg_date($rs->modified,'Y-m-d H:i:s');

		$iTable = new Table();
		foreach ($rs as $key => $value) $iTable->rows[]=array($key,$value);
		$ret .= $iTable->build();
	}

	return $ret;
}
?>