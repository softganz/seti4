<?php
/**
 * View recieve
 *
 * @param String $rcvno
 * @return String
 */
function saveup_loan_view($self, $loanNo) {
	$loanInfo = R::Model('saveup.loan.get', $loanNo);

	R::View('saveup.toolbar',$self,'ใบกู้เงิน '.$loanNo.' - '.$loanInfo->name,'loan',$loanInfo);

	if ( !$loanInfo ) return $ret.message('error','ไม่มีใบรับเงินตามที่ระบุ');

	$ret.=R::View('saveup.loan.show',$loanInfo);

	//$ret.=print_o($loanInfo,'$loanInfo');
	return $ret;
}
?>