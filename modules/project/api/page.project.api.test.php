<?php
/**
* Project API : Activity
*
* @param Object $self
* @return String
*/
function project_api_test($self, $tpid = NULL) {
	$projectSet = post('prset');
	$limit = SG\getFirst(post('limit',_ADDSLASHES),5);
	$photoWidth = 240;
	$photoHeight = 144;
	$dateformat = SG\getFirst(post('dateformat'),cfg('dateformat'));

	$info = getapi('https://happynetwork.org/project/api/activity');

	$ret .= '<div class="home--activity">'.$info['result']->html.'</div><br clear="all" />';

	// $ret.=print_o($info,'$info');

	return $ret;
}
?>