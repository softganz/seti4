<?php
/**
* Get Recieve Information
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_saveup_rcv_get($conditions, $options = '{}') {
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


	$stmt = 'SELECT
						r.*
					, l.`lid` `transferId`
					FROM %saveup_rcvmast% r
						LEFT JOIN %saveup_log% l ON l.`keyword` = "TRANSFER" AND l.`process` = r.`rcvid`
					WHERE rcvid = :rcvid LIMIT 1';
	$result = mydb::select($stmt, ':rcvid', $id);

	if ($result->_empty) return NULL;

	if (!$debug) mydb::clearprop($result);

	$stmt = 'SELECT tr.*, CONCAT(m.`firstname`," ",m.`lastname`) `name`, gc.`desc`
					FROM %saveup_rcvtr% tr
						LEFT JOIN %saveup_member% m USING(`mid`)
						LEFT JOIN %saveup_glcode% gc USING(`glcode`)
					 WHERE tr.`rcvid` = :rcvid
					 ORDER BY tr.`aid` ASC;
					 -- {key: "aid"}';

	$result->trans = mydb::select($stmt,':rcvid',$id)->items;

	return $result;
}
?>