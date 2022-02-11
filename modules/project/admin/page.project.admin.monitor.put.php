<?php
function project_admin_monitor_put($self) {
	$url = 'https://sg-project-man.firebaseio.com/Devices.json';
	$arr = array("iPhone8" =>array('model'=>'iPhone 8','price'=>900));  
	$data_string = json_encode($arr);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json',
	'Content-Length: ' . strlen($data_string))
	);
	$ret = curl_exec($ch);
	$ret.=print_o(json_decode($ret));
	return $ret;
}
?>