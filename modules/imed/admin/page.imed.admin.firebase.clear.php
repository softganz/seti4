<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function imed_admin_firebase_clear($self) {
	$ret = '<section><header class="header"><h3>CLEAR FIREBASE</h3></header>';

	// Update Google Firebase
	$firebaseCfg = cfg('firebase');
	$firebase = new Firebase($firebaseCfg['projectId'], 'logs');

	/*
	$data = array(
		'camid' => intval($camid),
		'name' => $camname,
		'photo' => $post->photo,
		'url' => _DOMAIN.$post->url,
		'thumb' => _DOMAIN.$post->url,
		'date' => sg_date($post->created,'ว ดด ปป'),
		'time' => sg_date($post->created,'H:i'),
		'timestamp' => array('.sv' => 'timestamp')
	);

	//$fbresult = $firebase->post(NULL);

	$ret .= print_o($firebaseCfg, '$firebaseCfg');
	//$ret .= print_o($fbresult, '$fbresult');
	*/



	if (SG\Confirm()) {
		$url = 'https://'.$firebaseCfg['projectId'].'.firebaseio.com/logs.json';
		$data_string = '{}';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_POSTFIELDS, NULL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: 0')
		);
		debugMsg('Firebase URL : '.$url);

		$ret = curl_exec($ch);
		return $ret;
	} else {
		$ret .= '<a class="sg-action btn -danger" href="'.url('imed/admin/firebase/clear').'" data-rel="#result" data-title="ลบข้อมูล" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?">ลบข้อมุล iMed Logs</a>';
	}

	$ret .= '<div id="result"></div>';

	$ret .= '</section>';



	/*

	$url = 'https://'.$firebaseCfg['projectId'].'.firebaseio.com/logs.json';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);
	debugMsg($url);

	$ret = curl_exec($ch);
	*/

	return $ret;
}
?>