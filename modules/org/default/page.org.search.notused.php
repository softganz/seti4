<?php
function org_search($self,$qn) {
	$ret='<h3>Organization Search</h3>';
	$qn=SG\getFirst(post('qn'),$qn);
	if ($qn) {
		$ret.='<p>Search for <b>'.$qn.'</b></p>';
	}
	return $ret;
}
?>