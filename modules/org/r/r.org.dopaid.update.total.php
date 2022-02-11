<?php
/**
* Project Join Update Total
*
* @param Object $data
* @return Object $options
*/

function r_org_dopaid_update_total($dopid, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'success' => false,
		'query' => [],
	];

	if (empty($dopid)) return $result;

	// Update DoPaid Master Total
	$stmt = 'UPDATE %org_dopaid% d
				LEFT JOIN
					(
					SELECT `dopid`, SUM(`amt`) `totalAmt`
					FROM %org_dopaidtr% tr
					WHERE tr.`dopid` = :dopid
					) b ON b.`dopid` = d.`dopid`
				SET d.`total` = b.`totalAmt`
				WHERE d.`dopid` = :dopid';
	mydb::query($stmt, ':dopid', $dopid);
	if ($debug) $result->query[] = mydb()->_query;

	$result->success = true;

	return $result;
}
?>