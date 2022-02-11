<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_ah($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self,'เขตสุขภาพ', 'ah', $orgInfo);
	$ret = '';

	switch ($action) {
		case 'name.add':
			$ret .= 'ADD NAME';
			break;

		default:
			$argIndex = 3; // Start argument

			//$ret .= 'PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'org.ah.'.$action,
								$self,
								$orgInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);
			if (is_string($ret) && trim($ret) == '') $ret = 'ERROR : PAGE NOT FOUND';
			break;

	}
	//$ret .= print_o($orgInfo,'$orgInfo');
	return $ret;
}
?>