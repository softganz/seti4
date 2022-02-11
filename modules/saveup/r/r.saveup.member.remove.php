<?php
/**
 * Get saveup member information by member id
 *
 * @param String $mid
 * @return Object
 */
function r_saveup_member_remove($mid) {
	$debug=false;
	if (empty($mid)) return NULL;

	if ($debug) debugMsg('Remove saveup id '.$mid);

	$memberInfo=R::Model('saveup.member.get',$mid);

	if (!$memberInfo) return NULL;

	// Remove mid in table
	// - saveup_balance
	// - saveup_loan
	// - saveup_memcard
	// - saveup_rcvtr
	// - saveup_treat
	// - saveup_line(mid,lid,parent)
	// - saveup_member

	$stmt='DELETE FROM %saveup_balance% WHERE `mid`=:mid LIMIT 1';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_loan% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_memcard% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_rcvtr% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_treat% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_line% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	$stmt='DELETE FROM %saveup_member% WHERE `mid`=:mid';
	mydb::query($stmt,':mid',$mid);
	if ($debug) debugMsg(mydb()->_query);

	if ($debug) debugMsg($memberInfo,'$memberInfo');


	$rs=R::Model('saveup.member.get',$mid);
	return $rs;
}
?>