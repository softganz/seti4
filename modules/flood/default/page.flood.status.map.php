<?php
/**
* Flood :: Show Camera Map
* Created 2018-11-02
* Modify  2020-12-02
*
* @param Object $self
* @param Int $camId
* @return String
*
* @usage flood/status/map/{id}
*/

$debug = true;

function flood_status_map($self, $camId) {
	$rs = R::Model('flood.camera.get',$camId);

	list($x,$y)=explode(',',$rs->location);

	$gis['center']=$x.','.$y;
	$gis['centerX'] = floatval($x);
	$gis['centerY'] = floatval($y);
	$gis['zoom']=12;
	$gis['markers'][]=array('latitude'=>$x,
		'longitude'=>$y,
		'title'=>$rs->title,
		'icon'=>'/library/img/geo/webcam.jpg',
		'content'=>'<h4><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h4><a href="'.url('flood/cam/'.$rs->camid).'"><img id="'.$rs->camid.'" src="'.flood_model::thumb_url($rs->name,$rs->last_photo,$rs->last_updated).'" /></a><p id="'.$rs->camid.'-time">เมื่อ '.sg_date($rs->last_updated,'ว ดด ปป H:i').' น. </p><p><a class="water-level" href="'.url('flood/level/'.$rs->camid).'">ระดับน้ำ</a></p>');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$rs->title.'</h3></header>';
	$ret .= '<div id="map_canvas" width="100%" height="600" style="clear:both;">กำลังโหลดแผนที่</div>';

	$ret .= '<script type="text/javascript">
		var data='.json_encode($gis).';

	// your code here - init map ...
	function initMap() {
		$(".box-page").css({height: "100%"})

		var map;
		map = new google.maps.Map(document.getElementById("map_canvas"), {
			center: {lat: data.centerX, lng: data.centerY},
			zoom: data.zoom
		});

		var myLatLng = {lat: data.centerX, lng: data.centerY};
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			title: data.markers[0].title
		});
	}

	loadGoogleMaps("initMap")

</script>';

	return $ret;
}
?>