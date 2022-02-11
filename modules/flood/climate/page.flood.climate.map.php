<?php
/**
* Flood City Climate - Map
* Created 2019-08-22
* Modify  2019-08-22
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_climate_map($self) {
	R::View('toolbar', $self, 'City Climate', 'flood.climate');
	$ret = '';

	page_class('-page-fill');

	$show=post('show');
	mydb::where('`status` = 1');
	if ($show!='all') mydb::where('`staffflag` IS NOT NULL');

	$stmt = 'SELECT s.* FROM %flood_station% s %WHERE%';
	$dbs = mydb::select($stmt);

	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('<script type="text/javascript" src="/flood/js.flood.markerwithlabel.js"></script>');
	//head('<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.5/src/markerwithlabel.js"></script>');
	
	page_class('-page-fill');

	$ret.='<div class="map-output" style="width: 100%; height:100%; min-height: 600px;">'._NL;
	$ret.='<div class="map-canvas" id="map-canvas" style="width: 100%; height: 100%;">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>'._NL;

	$iconImg['Green']='/library/img/geo/circle-green.png';
	$iconImg['Yellow']='/library/img/geo/circle-yellow.png';
	$iconImg['Red']='/library/img/geo/circle-red.png';
	$iconImg['None']='/library/img/geo/circle-gray.png';

	$statusMsg['Green']='ปกติ';
	$statusMsg['Yellow']='เฝ้าระวัง';
	$statusMsg['Red']='วิกฤติ';
	$statusMsg['None']='ยังไม่มีข้อมูลแจ้งข่าว';

	$gis['center']=SG\getFirst($self->property['map.center'],'6.8000,100.45000');
	$gis['zoom']=intval(SG\getFirst($self->property['map.zoom'],10));


	foreach ($dbs->items as $rs) {
		if ($rs->latlng) {
			list($lat,$lng)=explode(',', $rs->latlng);
			$status='None';
			if ($rs->staffflag) $status=$rs->staffflag;
			$pin=$iconImg[$status];
			$gis['markers'][]=array('latitude'=>$lat,
				'longitude'=>$lng,
				'title'=>$rs->title,
				'icon'=>$pin,
				'status'=>$status,
				'waterupdate'=>$rs->waterupdate?sg_date($rs->waterupdate,'ว ดดด ปปปป H:i').' น.':'-',
				'content'=>'<div class="project-map-info"><h4>สถานี '.$rs->title.'</h4>'
				//.'<p>สถานที่ : '.$rs->description.'</p>'
				.'<p class="flood-monitor-status-'.$status.'">สถานภาพ : '.$statusMsg[$status].'</p>'
				.'<p>ผู้รับผิดชอบ : '.$rs->peoplename.' โทร '.$rs->peoplephone.'</p>'
				.'<p>'.$rs->gaugetype.($rs->buildorg?' ของ '.$rs->buildorg:'').($rs->canelname?' '.$rs->canelname:'').' ลุ่มน้ำ '.$rs->basin.'</p>'
				.($rs->last_photo?'<img class="photo" src="'._url.'file/fl/photo/'.$rs->last_photo.'" width="200" />':'')
				//.print_o($rs,'$rs')
				.'<!-- <p><a href="'.url('flood/monitor/status',array('s'=>$rs->station)).'" class="sg-action" data-rel="box" data-width="400">ดูรายละเอียด</a> | <a class="sg-action" href="'.url('flood/event/send','s='.$rs->station).'">บันทึก</a></p>--></div>'
				);
		}
	}


	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var imgSize = new google.maps.Size(16, 16);
		var gis='.json_encode($gis).';
		var is_point=false;
		var $map=$("#map-canvas");
		$map.gmap({
				center: gis.center,
				zoom: gis.zoom,
				scrollwheel: true
			})
			.bind("init", function(event, map) {
				if (gis.markers) {
					$.each( gis.markers, function(i, marker) {
						//alert(marker.latitude+","+marker.longitude)
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
							draggable: false,
							marker: MarkerWithLabel,
							labelContent: marker.title,
							labelClass: "labels "+marker.status,
						}).click(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						}).mouseover(function() {
							//$map.gmap("openInfoWindow", { "content": marker.content }, this);
						});
					});
				}
			})
	});
	--></script>';

	$ret .= '<script type="text/javascript"><!--
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {refresh: false}
			return options
		}
	</script>';

	return $ret;
}
?>