<?php
/**
* Update Recieve Total
* Created 2019-05-19
* Modify  2019-05-19
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_saveup_rcv_update_total($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$id = $conditions->id;

	$result['title'] = '<b>Update Recieve Total of '.$id.'</b>';

	$stmt = 'UPDATE %saveup_rcvmast% m
						LEFT JOIN (SELECT `rcvid`, SUM(`amt`) `sum_total` FROM %saveup_rcvtr% WHERE `rcvid` = :rcvid) t USING(`rcvid`)
						SET m.`total` = t.`sum_total`
					WHERE m.`rcvid` = :rcvid';
	mydb::query($stmt, ':rcvid', $id);

	$result['query'][] = mydb()->_query;
	return $result;
}
?>