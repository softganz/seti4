<?php
function project_test() {
	$ret.='<h2>Project test in default folder</h2>';
	$ret.=print_o(func_get_args(),'func_get_args');
	return $ret;
}
?>