<?php
function school_summary_learn_add($self,$orgid,$action) {
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

	$ret.='<h2>ผลสัมฤทธิ์ทางการเรียน</h2>';

	if (post('learn')) {
		location('school/summary/learn/'.$orgid);
	}
	$ret.=R::View('school.summary.learn.form',$orgid);

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}

?>