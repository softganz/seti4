<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

import('model:node.php');

function r_icar_delete($carId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NodeModel::delete($carId);


	$stmt = 'DELETE FROM %icar% WHERE `tpid` = :tpid LIMIT 1';
	mydb::query($stmt,':tpid',$carId);
	$result->process[]=mydb()->_query;

	$stmt = 'DELETE FROM %icarcost% WHERE `tpid` = :tpid';
	mydb::query($stmt,':tpid',$carId);
	$result->process[]=mydb()->_query;

	model::watch_log('icar','Car Remove','icar/'.$carId.' - '.$result->data->title.' was removed by '.i()->name.'('.i()->uid.')');


	return $result;
}
?>