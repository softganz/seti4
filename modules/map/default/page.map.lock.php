<?php
function map_lock($self,$mapid) {
	$rs=mydb::select('SELECT `status` FROM %map_networks% WHERE `mapid`=:mapid LIMIT 1',':mapid',$mapid);
	$newstatus=$rs->status=='lock' ? 'func.NULL' : 'lock';
	mydb::query('UPDATE %map_networks% SET `status`=:status WHERE `mapid`=:mapid LIMIT 1',':mapid',$mapid, ':status',$newstatus);
	$ret.=$newstatus=='lock'?'Locked':'Unlocked';
	return $ret;
}
?>