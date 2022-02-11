<?php
function school_register($self) {
	R::View('school.toolbar',$self,'School Kids Registration');
	$ret.='Welcome to School Kids Registration';

	if (!user_access('create school content')) {
		if (i()->ok) {
			$ret.=message('error','access denied');
		} else {
			$ret.=R::Model('signform');
		}
		return $ret;
	}

	$mySchool=R::Model('school.getmy',i()->uid);

	if (empty($mySchool)) {
		$ret.=R::View('school.register.form');
	} else {
		location('school/my');
	}
	//$ret.=print_o($mySchool,'$mySchool');
	return $ret;
}
?>