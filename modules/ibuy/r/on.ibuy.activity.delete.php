<?php
/**
* On iBuy Activity Delete
* Created 2020-06-07
* Modify  2020-06-27
*
* @param Int $tranId
* @return Boolean
*/

$debug = true;

function on_ibuy_activity_delete($tranId) {
	// Firebase Update Message
	$firebaseCfg = cfg('firebase');
	$firebase = new Firebase('sg-imed', $firebaseCfg['msg']);
	$dataFB = array(
		'refDb' => $firebaseCfg['msg'],
		'seq' => intval($tranId),
		'token' => $firebaseCfg['token'],
		'changed' => 'delete',
		'time' => array('.sv' => 'timestamp'),
	);
	$resultFB = $firebase->functions('visitUpdate',$dataFB);

	return $resultFB;
}
?>