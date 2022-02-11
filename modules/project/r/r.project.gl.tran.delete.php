<?php
function r_project_gl_tran_delete($refcode) {
	if (empty($refcode)) return false;
	$stmt='DELETE FROM %project_gl% WHERE `refcode`=:refcode';
	mydb::query($stmt,':refcode',$refcode);
	//debugMsg(mydb()->_query);
	return true;
}
?>