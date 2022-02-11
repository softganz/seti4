<?php
/**
 * Show trainer follow form
 *
 * @param Integer $tpid
 * @param Integer $period
 * @return String
 */
function project_member_show_follow_form($self,$tpid,$period=1) {
	$ret.=R::Page('project.form.follow_form',$tpid,$period);
	return $ret;
}
?>