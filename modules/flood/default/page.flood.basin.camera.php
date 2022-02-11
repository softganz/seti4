<?php
function flood_basin_camera($self,$camid=NULL) {
	$camid=SG\getFirst(post('c'),$camid);
	$cams=array(3=>'bangsala',10=>'r1', 2=>'janvirot', 1=>'hatyainai', 5=>'gamling2', 4=>'gamling1');
	$ret.='<img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/'.$cams[$camid].'/lastphoto.jpg" />';
	return $ret;
}
?>