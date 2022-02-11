<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_icar_log($carId, $key, $msg, $fldName = NULL) {
	model::watch_log(
		'icar',
		$key,
		$msg,
		NULL,
		$carId,
		$fldName
	);
	return $result;
}
?>