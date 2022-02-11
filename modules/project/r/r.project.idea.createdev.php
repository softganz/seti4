<?php
function r_project_idea_createdev($info) {
	if (empty($info->tpid)) return;
	if (is_array($data)) $data=(object)$data;

	$stmt='INSERT INTO %project_dev%
				(`tpid`,`pryear`)
				VALUES
				(:tpid,:ideayear)
				ON DUPLICATE KEY
				UPDATE `tpid`=:tpid';
	mydb::query($stmt,$info);
	return $info->tpid;
}
?>