<?php
/**
* Green : Organic Main Page
* Created 2020-10-03
* Modify  2020-10-03
*
* @param Object $self
* @return String
*
* @usage green/organic
*/

$debug = true;

function green_organic($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';

	if (!is_numeric($orgId)) {$action = $orgId; unset($orgId);} // Action as orgId and clear

	switch ($action) {

		default:
			if (empty($action) && empty($customerId)) $action = 'home';
			if (empty($action) && $customerId) $action = 'view';
			if (empty($orgId)) $orgInfo = $orgId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$customerId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'green.organic.'.$action,
				$self,
				$orgInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	return $ret;
}
?>