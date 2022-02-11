<?php
function project_test() {
	$ret.='<h2>Project test in test folder</h2>';
	$ret.=print_o(func_get_args(),'func_get_args');
	$ret.=print_o(get_caller(__FUNCTION__),'Caller');
	return $ret;
}
?>