<?php
/**
* Org Create New Docs
*
* @param Object $self
* @param Int $
* @return String
*/

import('model:org.php');

function org_docs_new($self) {
	$ret = 'Create new docs';

	$myOrg = OrgModel::my();

	// debugMsg('myOrg = '.$myOrg);

	// $orgInfo = OrgModel::get(1, '{debug:false}');

	// $ret .= print_o($orgInfo, '$orgInfo');

	// $orgList = OrgModel::items(['orgId' => $myOrg], '{debug: true, resultType: "list"}');
	// debugMsg($orgList, '$orgList');

	return $ret;
}
?>