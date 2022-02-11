<?php
/**
* flood_monitor_station
*
* @param Object $self
* @return String
*/
function flood_monitor_station($self) {
	R::View('flood.monitor.toolbar',$self);
	$station=post('s');
	$isAdmin=user_access('administrator floods');
	if (!$station) {
		$ret.='<ul class="flood--station--list"><li><a href="'.url('flood/monitor/station',array('s'=>'HY1')).'">สถานีม่วงก็อง</a></li><li><a href="'.url('flood/monitor/station',array('s'=>'HY2')).'">สถานีบางศาลา</a></li><li><a href="'.url('flood/monitor/station',array('s'=>'HY3')).'">สถานีอู่ตะเภา</a></li></ul>';
		return $ret;
	}
	$stmt='SELECT s.*, b.`name` basinName FROM %flood_station% s LEFT JOIN %flood_basin% b USING(`basin`) WHERE `station`=:station LIMIT 1';
	$rs=mydb::select($stmt,':station',$station);

	//$ret.=print_o($rs,'$rs');

	//$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="temperature" ORDER BY `sid` DESC LIMIT 1';
	//$temperature=mydb::select($stmt,':station',$rs->station);
	
	//$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="relativeHumidity" ORDER BY `sid` DESC LIMIT 1';
	//$humidity=mydb::select($stmt,':station',$rs->station);

	//$stmt='SELECT * FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" ORDER BY `sid` DESC LIMIT 10';
	//$rains=mydb::select($stmt,':station',$rs->station);

	list($rainAvg)=flood_model::rainavg($rs->basin);

	$ret.='<div class="flood__main">';
	$ret.='<div class="flood__box flood__box--station"><h3>ข้อมูลสถานี</h3>';

	$tables = new Table();
	$tables->rows[]=array('ชื่อสถานี',$rs->title);
	$tables->rows[]=array('ที่ตั้ง',$rs->description);
	$tables->rows[]=array('พิกัด',$rs->latlng);
	$tables->rows[]=array('ลำน้ำ',$rs->basinName);

	$ret .= $tables->build();

	$ret.='</div>';

	$ret.='<div class="flood__box flood__box--sensor"><h3>สถานะของอุปกรณ์ภายในสถานี เมื่อ '.sg_date($rs->waterupdate,'ว ดด ปป H:i').' น.</h3>';

	$tables = new Table();
	$tables->rows[]=array('ปริมาณน้ำฝน 15 นาที',number_format($rainAvg[$station]['15min'],1),'ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน 1 ชั่วโมง',number_format($rainAvg[$station]['1hr'],1),'ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน รายเดือน',number_format($rainAvg[$station]['thismonth'],1),'ม.ม.','');
	$tables->rows[]=array('ปริมาณน้ำฝน รายปี',number_format($rainAvg[$station]['thisyear'],1),'ม.ม.','');
	$tables->rows[]=array('ระดับน้ำ',$rs->waterlevel?number_format($rs->waterlevel,2):'-',$rs->levelref,flood_model::sensor_status($rs->waterupdate)=='normal'?'ปกติ':'ขัดข้อง');
	$tables->rows[]=array('อุณหภูมิ',number_format($rs->temperature,1),'ํC','');
	$tables->rows[]=array('ความชื้นสัมพัทธ์',number_format($rs->humidity,2),'%','');
	if ($isAdmin) {
		$tables->rows[]=array('แบตเตอรี่',$rs->batterylevel,'โวลท์',$rs->batterylevel>12?'ปกติ':'ต่ำ');
	}
	if ($rs->waterlevel && $rs->bankheightleft) {
		$diff=$rs->bankheightleft-$rs->waterlevel;
		$tables->rows[]=array('ค่าระดับน้ำ'.($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งซ้าย','<big><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></big>',' ม.');
	}
	if ($rs->waterlevel && $rs->bankheightright) {
		$diff=$rs->bankheightright-$rs->waterlevel;
		$tables->rows[]=array('ค่าระดับน้ำ'.($diff>0?'ต่ำ':'สูง').'กว่าตลิ่งขวา','<big><font color="'.($diff>0?'':'red').'">'.number_format(abs($diff),2).'</font></big>',' ม.');
	}

	$ret .= $tables->build();

	$ret.='</div>';

	$last_photo=$rs->last_photo?_DOMAIN._URL.'/upload/sensor/'.$rs->last_photo:url('file/flood/site/photonotyetupload.png');
	$ret.='<div class="flood__box flood__box--photo"><h3>ภาพล่าสุด เมื่อ '.sg_date($rs->last_updated,'ว ดด ปปปป H:i').' น.</h3><a class="sg-action" href="'.$last_photo.'" data-rel="img"><img src="'.$last_photo.'" width="100%" /></a><div class="toolbar"><ul><li><a href="'.url('flood/monitor/cctv',array('basin'=>post('basin'),'s'=>$station)).'">ภาพย้อนหลัง</a></li></ul></div></div>';
	$ret.='<div class="flood__box flood__box--crosssection"><h3>ภาพ Cross section</h3><a class="sg-action" href="'.url(
	'file/flood/site/'.$rs->station.'.crosssection.jpg').'" data-rel="img"><img src="'.url(
	'file/flood/site/'.$rs->station.'.crosssection.jpg').'" alt="Cross section" width="100%" /></a></div>';
	$ret.='<div class="flood__box flood__box--stationphoto"><h3>ภาพถ่ายสถานี</h3><a class="sg-action" href="'.url(
	'file/flood/site/'.$rs->station.'.site.jpg').'" data-rel="img"><img src="'.url(
	'file/flood/site/'.$rs->station.'.site.jpg').'" alt="Site photo" width="100%" /></a></div>';

	$ret.='<div id="map--canvas" class="map--canvas flood__box flood__box--map"><h3>แผนที่ตั้งสถานี</h3>';
	$ret.='<div class="map-canvas" id="map-canvas" width="600" height="400" style="width:100%;height:400px;margin:0;padding:0;">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>';

	$ret.='</div><!--flood__main-->';

	$status='green';
	$iconImg['green']='/library/img/geo/circle-green.png';
	$iconImg['yellow']='/library/img/geo/circle-yellow.png';
	$iconImg['red']='/library/img/geo/circle-red.png';
	$iconImg['stop']='/library/img/geo/circle-red.png';

	$statusMsg['green']='ปกติ';
	$statusMsg['yellow']='เฝ้าระวัง';
	$statusMsg['red']='วิกฤติ';
	$statusMsg['stop']='หยุดทำงาน';

	$gis['center']=SG\getFirst($rs->latlng,'6.9000,100.4000');
	$gis['zoom']=intval(10);
	list($lat,$lng)=explode(',', $rs->latlng);
	$gis['markers'][]=array(
		'latitude'=>$lat,
		'longitude'=>$lng,
		'title'=>$rs->title,
		'icon'=>$iconImg[$status],
		'status'=>$status,
		'waterupdate'=>$rs->waterupdate?sg_date($rs->waterupdate,'ว ดดด ปปปป H:i').' น.':'-',
		'content'=>'<div class="project-map-info"><h4>สถานี '.$rs->title.'</h4><p>สถานที่ : '.$rs->description.'</p><p class="flood-monitor-status-'.$status.'">สถานภาพ : '.$statusMsg[$status].' ('.($rs->waterupdate?sg_date($rs->waterupdate,'ว ดด ปปปป H:i').' น.':'ยังไม่ได้รับข้อมูล').')</p><p><a href="'.url('flood/monitor/status/'.$rs->station).'" class="sg-action" data-rel="box">ดูรายละเอียด</a> | <a class="sg-action" href="'.url('sensor/log','s='.$rs->station).'" data-rel="box">บันทึก</a></p></div>'
	);

	head('jquery.ui.map','<script type="text/javascript" src="/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('markerwithlabel','<script type="text/javascript" src="/flood/js.flood.markerwithlabel.js"></script>');
	//head('<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.5/src/markerwithlabel.js"></script>');

	$ret.='<script type="text/javascript"><!--
		$(document).ready(function() {
			var imgSize = new google.maps.Size(16, 16);
			var gis='.json_encode($gis).';
			var is_point=false;
			var $map=$("#map-canvas");
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: false,
				})
				.bind("init", function(event, map) {
					if (gis.markers) {
						$.each( gis.markers, function(i, marker) {
							//alert(marker.latitude+","+marker.longitude)
							$map.gmap("addMarker", {
								position: new google.maps.LatLng(marker.latitude, marker.longitude),
								// icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
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
	//$ret.=print_o($gis,'$gis').print_o($rs);
	return $ret;
}
?>