<?php
/**
* Get hot camera
*/
function r_flood_camera_list($cameraIdList = NULL) {
	if (empty($cameraIdList)) $cameraIdList = cfg('flood.camera')->HOME->camera;

	$stmt = 'SELECT
			c.*
			, c.`last_photo` `photo`
			, c.`last_updated` `atdate`
			, r.`sponsor_name` `replaceSponsorName`
			, r.`sponsor_logo` `replaceSponsorLogo`
			, r.`sponsor_url` `replaceSponsorUrl`
			, r.`sponsor_text` `replaceSponsorText`
			FROM %flood_cam% c
				LEFT JOIN %flood_cam% r ON r.`camid` = c.`replaceid`
			WHERE c.`camid` IN ('.$cameraIdList.') AND c.`show` = 1;
			-- {key:"camid"}';

	$dbs = mydb::select($stmt);

	//debugMsg($dbs, '$dbs');

	foreach (explode(',',$cameraIdList) as $hotcid) {
		if (!array_key_exists($hotcid, $dbs->items)) continue;
		$cams[$hotcid]=$dbs->items[$hotcid];
	}
	//foreach ($dbs->items as $rs) $cams[$rs->camid]=$rs;
	return $cams;
}
?>