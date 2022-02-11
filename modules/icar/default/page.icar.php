<?php
/**
 * Car home page
 *
 * @return String
 */
function icar($self, $carId = NULL, $action = NULL, $tranId = NULL) {
	if (!is_numeric($carId)) {$action = $carId; unset($carId);} // Action as carId and clear

	if (empty($action) && empty($carId)) return R::Page('icar.home',$self);
	if (empty($action) && $carId) return R::Page('icar.view',$self,$carId);


	if ($carId) {
		$carInfo = R::Model('icar.get',$carId, '{initTemplate: true}');
		$isEdit = $carInfo->RIGHT & _IS_EDITABLE;
	}

	$isCreatable = user_access('create icar content');
	$isDeletable = $carInfo->RIGHT & _IS_DELETABLE;

	//$ret .= 'Action = '.$action. ' Is Creatable = '.($isCreatable ? 'YES' : 'NO').'<br />';

	switch ($action) {
		case 'create':
			$data = (Object) post('icar');
			$shop = icar_model::get_my_shop();
			if ($isCreatable && $shop->shopid && $data->plate) {
				$data->shopid = $shop->shopid;
				//$ret .= print_o($data,'$dataPost');
				$result = R::Model('icar.create', $data);
				//$ret .= print_o($result,'$result');
				if ($result->tpid) {
					location('icar/'.$result->tpid);
				} else {
					$ret .= message('error', 'Error on create buy');
				}
			} else {
				$ret .= message('error', 'Call create icar but invalid Data');
			}
			//$ret .= print_o($data,'$data').print_o($result,'$result');
			break;
		
		case 'delete':
			if ($isDeletable && SG\confirm()) {
				$result = R::Model('icar.delete', $carId);
				//$ret .= 'DELETE';
				//$ret .= print_o($result,'$result');
				if ($result->error) {
					$ret .= 'ERROR : '.$result->error;
				} else {
					location('icar/my');
				}
			} else {
				$ret .= message('error','ACCESS DENIED');
			}
			break;


		default:
			/*
			// Bug on action/action/action
			$funcName = array();
			foreach (array_slice(func_get_args(),2) as $value) {
				if (is_numeric($value)) break;
				else if (is_string($value)) {
					$funcName[] = $value;
				}
			}
			$argIndex = count($funcName)+2; // Start argument
			*/

			$argIndex = 3; // Start argument

			//$ret .= 'PAGE PROJECT Topic = '.$carId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'icar.'.$action,
				$self,
				$projectInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			if (is_string($ret) && trim($ret) == '') $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= R::Page('project.'.$action, $self, $carId);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}


	//$ret .= print_o($carInfo,'$carInfo');

	return $ret;
}
?>