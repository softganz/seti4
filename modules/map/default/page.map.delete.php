<?php
/**
* Delete pin
* @param Integer $id
* @param String
*/
function map_delete($self,$id) {
	$mapid=SG\getFirst($id,post('id'));

	$rs=mydb::select('SELECT * FROM %map_networks% WHERE `mapid`=:mapid LIMIT 1',':mapid',$mapid);
	if (empty($rs->mapid)) return 'Error';

	$is_edit=user_access('administer maps','edit own maps content',$rs->uid);
	if (!$is_edit) return 'ไม่มีสิทธิ์';

	$ret='ลบ';

	//$ret.=print_o($rs,'$rs');
	mydb::query('DELETE FROM %map_history% WHERE `mapid`=:mapid',':mapid',$mapid);

	// Delete photo
	if ($rs->gallery) {
		$photoDbs=mydb::select('SELECT * FROM %topic_files% WHERE `gallery`=:gallery',':gallery',$rs->gallery);
		foreach ($photoDbs->items as $photoRs) {
			$this->_delphoto($photoRs->fid);
		}
		//$ret.=print_o($photoDbs,'$photoDbs');
	}

	mydb::query('DELETE FROM %map_networks% WHERE `mapid`=:mapid',':mapid',$mapid);
	return $ret;
}
?>