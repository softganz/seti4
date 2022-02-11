<?php
/**
 * Get saveup member information by member id
 *
 * @param String $mid
 * @return Object
 */
function r_saveup_member_getbyuserid($uid) {
	$stmt='SELECT m.`mid` FROM %saveup_member% m WHERE `userid`=:uid LIMIT 1';
	$mid=mydb::select($stmt,':uid',$uid)->mid;
	$rs=R::Model('saveup.member.get',$mid);
	return $rs;
}
?>