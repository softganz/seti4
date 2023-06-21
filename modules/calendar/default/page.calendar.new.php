<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_new($self, $calId = NULL) {
	$calInfo = is_object($calId) ? $calId : R::Model('calendar.get',$calId, '{initTemplate: true}');
	$calId = $calInfo->calId;
	// debugMsg($calInfo,'$calInfo');

	$post = (Object) post();

	if ($calId && $post->module) {
		$isEdit = R::On($post->module.'.calendar.isadd',$calInfo);
		if (!$isEdit) return error('Access denied');
	}

	if (!(Array) $calInfo) {
		$calInfo->from_date = $calInfo->to_date = \SG\getFirst($post->d, date('Y-m-d'));
		$calInfo->tpid = $post->tpid;
	}

	$calInfo->from_date = sg_date($calInfo->from_date,'d/m/Y');
	$calInfo->to_date = sg_date($calInfo->to_date,'d/m/Y');

	$ret .= R::View('calendar.form', $calInfo, $post);

	// debugMsg($post,'$post');
	// debugMsg($calInfo,'$calInfo');

	return $ret;
}
?>