<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_ah_home($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self,'เขตสุขภาพ', 'ah', $orgInfo);
	$ret = '';

	$ret .= '<a href="'.url('org/306/ah.view/1').'">เกษตรและอาหารเพื่อสุขภาพ</a>';


	$ret .= '<nav class="nav -page"><a class="btn" href="'.url('org/'.$orgId.'/ah.operation.add').'"><i class="icon -material">add</i><span>Add</span></a></nav>';
	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>