<?php
function school_summary_eat_add($self,$orgid , $action = NULL) {
	$action=SG\getFirst($action,post('action'));
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	if (post('checkdup')) {
		$r['isDup']=R::Model('school.summary.weight.duplicate',$tpid,NULL,post('year'),post('termperiod'));
		$r['msg']='OK';
		$r['para']=print_o(post(),'post');
		die(json_encode($r));
	}
	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$ret.='<h2>สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</h2>';

	if (post('eat')) {
		location('school/summary/eat/'.$orgid);
	}
	$ret.=R::View('school.summary.eat.form',$orgid);

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}

?>