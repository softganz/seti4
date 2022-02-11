<?php
/**
* Model Name
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_project_nhso_obt_update($fundInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$orgId = $fundInfo->orgId;

	$areaList = array(12);

	$result = NULL;

	//debugMsg($fundInfo->areaId);

	if (!in_array($fundInfo->areaId, $areaList)) return $result;

	//debugMsg('NHSO UPDATE ');

	$targetHost = 'https://localfund.happynetwork.org/localfund_update_org_balance.php?org='.$orgId;
	//debugMsg($targetHost);

	$targetCh = curl_init($targetHost);
	curl_setopt($targetCh, CURLOPT_RETURNTRANSFER, false);

	// execute!
	$response = curl_exec($targetCh);

	//debugMsg('RESPONSE<hr /><div style="border: 2px #ccc solid; padding: 8px;">'.$response.'</div>');

	// close the connection, release resources used
	curl_close($targetCh);

	return $result;
}
?>