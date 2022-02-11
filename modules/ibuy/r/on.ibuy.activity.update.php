<?php
/**
* On iBuy Activity Update
* Created 2020-06-07
* Modify  2020-06-27
*
* @param Int $tranId
* @return Boolean
*/

$debug = true;

function on_ibuy_activity_update($tranId) {
	// Firebase Update Message
	$firebaseCfg = cfg('firebase');
	$firebase = new Firebase('sg-imed', $firebaseCfg['msg']);
	$dataFB = array(
		'refDb' => $firebaseCfg['msg'],
		'seq' => intval($tranId),
		'token' => $firebaseCfg['token'],
		'changed' => 'modify',
		'time' => array('.sv' => 'timestamp'),
	);
	$resultFB = $firebase->functions('visitUpdate',$dataFB);
debugMsg($dataFB,'$dataFB');
	return $resultFB;
}
?>