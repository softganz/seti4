<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_monitor_water($self) {
	$self->theme->title.=' : โทรมาตร';
	$stmt='SELECT s.* FROM %flood_station% s ';
	$dbs=mydb::select($stmt);

	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('<script type="text/javascript" src="/flood/js.flood.markerwithlabel.js"></script>');
	//	head('<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.5/src/markerwithlabel.js"></script>');
	

	$ret.='<div class="map-output">'._NL;
	$ret.='<div class="map-canvas" id="map-canvas" width="600" height="400" style="width:100%;height:100%;">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>'._NL;

		$iconImg['green']='/library/img/geo/circle-green.png';
		$iconImg['yellow']='/library/img/geo/circle-yellow.png';
		$iconImg['red']='/library/img/geo/circle-red.png';
		$iconImg['stop']='/library/img/geo/circle-red.png';

		$statusMsg['green']='ปกติ';
		$statusMsg['yellow']='เฝ้าระวัง';
		$statusMsg['red']='วิกฤติ';
		$statusMsg['stop']='หยุดทำงาน';

		$gis['center']=SG\getFirst($self->property['map.center'],'6.9000,100.4000');
		$gis['zoom']=intval(SG\getFirst($self->property['map.zoom'],10));


		foreach ($dbs->items as $rs) {
			if ($rs->latlng) {
				list($lat,$lng)=explode(',', $rs->latlng);
				$status='stop';
				if (!$rs->waterupdate) {
					$status='stop';
				} else if (sg_date($rs->waterupdate,'U')<date('U')-60*60) {
					$status='stop';
				} else {
					$status='green';
				}
				/*
					else if ($rs->waterlevel && $rs->levelyellow && $rs->waterlevel<$rs->levelyellow) {
					$status='green';
				} else if ($rs->waterlevel && $rs->levelred && $rs->waterlevel<$rs->levelred) {
					$status='yellow';
				} else if ($rs->waterlevel && $rs->levelred && $rs->waterlevel>=$rs->levelred) {
					$status='red';
				}
				*/
				$gis['markers'][]=array('latitude'=>$lat,
															'longitude'=>$lng,
															'title'=>$rs->title,
															'icon'=>$iconImg[$status],
															'status'=>$status,
															'waterupdate'=>$rs->waterupdate?sg_date($rs->waterupdate,'ว ดดด ปปปป H:i').' น.':'-',
															'content'=>'<div class="project-map-info"><h4>สถานี '.$rs->title.'</h4><p>สถานที่ : '.$rs->description.'</p><p class="flood-monitor-status-'.$status.'">สถานภาพ : '.$statusMsg[$status].' ('.($rs->waterupdate?sg_date($rs->waterupdate,'ว ดด ปปปป H:i').' น.':'ยังไม่ได้รับข้อมูล').')</p><p><a href="'.url('flood/monitor/status',array('s'=>$rs->station)).'" class="sg-action" data-rel="box" data-width="400">ดูรายละเอียด</a> | <a class="sg-action href="'.url('flood/monitor/log','s='.$rs->station).'" data-rel="box">บันทึก</a></p></div>'
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

	//$ret.=print_o($gis,'$gis');
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>