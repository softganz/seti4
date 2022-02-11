<?php
/**
 * Edit recieve
 *
 * @param String $rcvno
 * @param Array $_POST
 * @return String
 */
function saveup_loan_edit($self,$loanNo) {
	$loanInfo = R::Model('saveup.loan.get', $loanNo);

	R::View('saveup.toolbar',$self,'แก้ไขใบกู้เงิน '.$loanNo.' - '.$loanInfo->name,'loan',$loanInfo);

	if ( !$loanInfo ) return $ret.message('error','ไม่มีใบกู้เงินตามที่ระบุ');

	$ret.=message('error','กำลังอยู่ในระหว่างการตัดสินใจว่าจะให้สามารถแก้ไขได้หรือไม่?');

	$ret.=R::View('saveup.loan.show',$loanInfo);

	return $ret;
}
?>