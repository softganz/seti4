<?php
/**
 * Get saveup member information by member id
 *
 * @param String $mid
 * @return Object
 */
function r_saveup_member_changeid($srcid,$destid) {
	$debug=false;
	$result = NULL;

	if (empty($srcid) || empty($destid)) return NULL;

	if ($debug) debugMsg('Change saveup id from '.($srcid).' to '.$destid);

	$srcMember=R::Model('saveup.member.get',$srcid);
	$destMember=R::Model('saveup.member.get',$destid);

	//debugMsg($srcMember, '$srcMember');
	//debugMsg($destMember, '$destMember');
	//debugMsg(post(),'post()');

	if (!$srcMember || $destMember) return NULL;

	// Change mid in table
	// - saveup_balance
	// - saveup_loan
	// - saveup_memcard
	// - saveup_rcvtr
	// - saveup_treat
	// - saveup_line(mid,lid,parent)
	// - saveup_member

	$stmt='UPDATE %saveup_balance% SET `mid`=:destid WHERE `mid`=:srcid LIMIT 1';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_loan% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_memcard% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_rcvtr% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_treat% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_line% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_line% SET `lid`=:destid WHERE `lid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_line% SET `parent`=:destid WHERE `parent`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	$stmt='UPDATE %saveup_member% SET `mid`=:destid WHERE `mid`=:srcid';
	mydb::query($stmt,':srcid',$srcid, ':destid',$destid);
	$querys[] =mydb()->_query;

	if ($debug) debugMsg($srcMember,'$srcMember');
	if ($debug) debugMsg($destMember,'$destMember');


	$result = R::Model('saveup.member.get',$destid);
	$result->_query = $querys;

	return $result;
}
?>