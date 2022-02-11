<?php
/**
 * Camery delete
 *
 * @param String $camid
 * @return String
 */
function flood_camera_delete($self,$camid=NULL) {
	$rs=R::Model('flood.camera.get',$camid);
	R::View('flood.toolbar',$self,tr('Camera Delete','ลบกล้อง'),NULL,$rs);

	$ret.='<p class="notify">Unกer construction</p>';

	return $ret;
}
?>