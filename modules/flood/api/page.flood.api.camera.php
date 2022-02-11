<?php
function flood_api_camera($self,$camid) {
	if (is_numeric($camid)) {
		$stmt='SELECT * FROM %flood_cam% WHERE `camid`=:camid LIMIT 1';
	} else {
		$stmt='SELECT * FROM %flood_cam% WHERE `name`=:camid LIMIT 1';
	}
	$rs=mydb::select($stmt,':camid',$camid);
	$imgFile='file/fl/'.$rs->name.'/lastphoto.jpg';

	header('Content-type: image/jpeg');
	readfile($imgFile);

	die($ret);
}
?>