<?php
/**
 * Delete photo
 *
 * @param Integer $fid - file id
 * @return String
 */
function map_delphoto($self,$fid) {
	$ret = R::Model('map.photo.delete',$fid);
	return $ret;
}
?>