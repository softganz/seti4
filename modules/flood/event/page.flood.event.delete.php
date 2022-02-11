<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_event_delete($self,$eid) {
	$ret['msg']='ลบข้อความ '.$eid;
	$ret['error']=NULL;
	$ret['html']=NULL;

	$rs=mydb::select('SELECT * FROM %flood_event% WHERE `eid`=:eid LIMIT 1',':eid',$eid);
	if (!user_access('administrator floods','edit own flood content',$rs->uid)) {
		$ret['msg']='Access denied';
		$ret['error']=true;
		return $ret;
	}

	$stmt='DELETE FROM %flood_event% WHERE `eid`=:eid OR `parent`=:eid';
	mydb::query($stmt,':eid', $eid);
	if ($rs->photo) {
		$folder=_FLOOD_UPLOAD_FOLDER.'photo/';
		$photo=$folder.$rs->photo;
		unlink($photo);
	}
	$ret['msg']='ลบรายการเรียบร้อย';
	return $ret;
}
?>