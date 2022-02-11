<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar($self, $calId = NULL, $action = NULL, $tranId = NULL) {
	if (!module_install('calendar')) return message('error', 'Calendar Not Install');

	if (!is_numeric($calId)) {$action = $calId; unset($calId);} // Action as calId and clear

	if (empty($action) && empty($calId)) return R::Page('calendar.home',$self);
	if (empty($action) && $calId) return R::Page('calendar.view',$self,$calId);


	if ($calId) {
		$calInfo = R::Model('calendar.get',$calId);

		$isEdit = $calInfo->RIGHT & _IS_EDITABLE;
	}

	//R::View('calendar.toolbar',$self, 'calendar', NULL, $calInfo);

	$isCreatable = user_access('create calendar content');

	//$ret .= 'Action = '.$action. ' Is create = '.($isCreatable ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($orgInfo, '$orgInfo');

	if (substr($action,0,1) == '*') {$get = $action; $action = '*';}

	switch ($action) {
		case 'update':
		case 'create':
			$data = (object) post('calendar');
			if ($data->module) {
				$isModuleAddable = R::On($data->module.'.calendar.isadd',$data);
			}

			if (($isCreatable || $isModuleAddable) && post('calendar')) {

				$result = R::Model('calendar.create', $data, '{debug: false}');
				// debugMsg($data,'$data');
				// debugMsg($result, '$result');
			}
			// debugMsg(post(), 'post()');
			break;

		case 'edit':
			$ret .= R::Page('calendar.new',$self, $calInfo);
			break;

		case '*':
			$ret .= R::Page('calendar.home', $self, $get);
			break;

		default:
			$argIndex = 3; // Start argument

			//debugMsg('PAGE CALENDAR calId = '.$calId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'calendar.'.$action,
				$self,
				$calInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	return $ret;
}
?>