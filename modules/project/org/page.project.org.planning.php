<?php
/**
* Project Org Planning
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_org_planning($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId, '{initTemplate:true}');
	$orgId = $orgInfo->orgid;

	R::view('project.toolbar',$self,'แผนงาน @'.$orgInfo->name,'org',$orgInfo);

	$ret = '';

	return $ret;
}
?>