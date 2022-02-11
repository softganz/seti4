<?php
/**
* On iBuy Activity Add
* Created 2020-06-07
* Modify  2020-06-27
*
* @param Int $tranId
* @return Boolean
*/

$debug = true;

function on_ibuy_activity_create($tranId) {
	// Firebase Create New Message
	$firebaseCfg = cfg('firebase');
	$firebase = new Firebase('sg-imed', $firebaseCfg['msg']);
	$dataFB = array(
		'refDb' => $firebaseCfg['msg'],
		'seq' => intval($tranId),
		'uid' => i()->uid,
		'token' => $firebaseCfg['token'],
		'changed' => 'new',
		'time' => array('.sv' => 'timestamp'),
	);
	$resultFB = $firebase->functions('visitAdd',$dataFB);

	return $resultFB;
}
?>