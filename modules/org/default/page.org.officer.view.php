<?php
/**
* Organization Officer
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_officer_view($self, $orgId, $officerId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	return new Scaffold([
		'children' => [
			'<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>สมาชิกองค์กร</h3></header>',
			R::Page('profile.view', $officerId),
		], // children
	]);
}
?>