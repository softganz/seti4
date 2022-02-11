<?php
/**
 * Flood Camera Information
 *
 * @param Object $self
 * @param String $camid
 * @return String
 */
function flood_cam_info_hot($self,$camids='9,3,1') {
	$self->theme->title='ระดับน้ำลุ่มน้ำคลองอู่ตะเภา @'.sg_date('ว ดด ปป H:i น.');
	$ret.='<div class="cam-card container">';
	$ret.='<div class="row -flex">';
	foreach (explode(',', $camids) as $camid) {
		$ret.='<div class="cam-card-item col -md-4">';
		$ret.=R::Page('flood.cam.info',NULL,$camid);
		$ret.='</div>';
	 }
	$ret.='</div>';
	$ret.='</div>';
	$ret.='<style type="text/css">
	h2.title {text-align:center; background:transparent; border:none;}
	.cam-card {display:flex; margin-bottom:32px;}
	.cam-card-item {margin:0 1.66%; position: relative;}
	.cctv-info {height:70%;}
	.cctv-info-photo {height:100%;}
	</style>';
	return $ret;
}
?>