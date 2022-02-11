<?php
/**
* Green Rubber : Main Page
* Created 2020-09-28
* Modify  2020-09-28
*
* @param Object $self
* @return String
*
* @usage green/rubber/{$Id}/method
*/

$debug = true;

function green_rubber($self, $customerId = NULL, $action = NULL, $tranId = NULL) {
	$ret = '';

	if (!is_numeric($customerId)) {$action = $customerId; unset($customerId);} // Action as customerId and clear

	switch ($action) {

		default:
			if (empty($action) && empty($customerId)) $action = 'home';
			if (empty($action) && $customerId) $action = 'view';
			if (empty($customerInfo)) $customerInfo = $customerId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$customerId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'green.rubber.'.$action,
				$self,
				$customerInfo,
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