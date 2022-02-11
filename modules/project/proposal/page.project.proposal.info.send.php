<?php
/**
* Module Method
*
* @param Object $self
* @param Object $proposalInfo
* @return String
*/

$debug = true;

function project_proposal_info_send($self, $proposalInfo) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$ret = 'Status';

	//$ret .= print_o($proposalInfo,'$proposalInfo');
	return $ret;
}
?>