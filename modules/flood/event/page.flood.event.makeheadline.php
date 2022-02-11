<?php
function flood_event_makeheadline($self,$eventid) {
	$stmt='UPDATE %flood_event% SET `staffflag`="Headline" WHERE `eid`=:eid LIMIT 1';
	mydb::query($stmt,':eid',$eventid);

	$ret.=mydb()->_query;
	return $ret;
}
?>