<?php
/**
* Rain Forcast Average
*
* @param Object $self
* @return String
*
* Download rain forcast file from http://tiservice.hii.or.th/wrf-roms/ascii/
*/
require_once('class.pointlocation.php');

function flood_forcast_hr3_map() {
	//$dateForcast, $folder, $basinCode, $shapeType, $shpPoint = 20, $options = '{}') {

	$dateForcast = post('d');
	$esriFolder = post('f');
	$basinCode = post('shp');

	set_time_limit(0);
	$timer = -microtime(true);
	$result = array();



	$polygon = array();


	$basinList = cfg('basin');
	$basinName = $basinList[$basinCode];
	$forcastFolder = cfg('flood.forcast.folder');


	// Get shape
	$shapeInfo->coordinates[0]->path = array();
	$shapeFile = $forcastFolder.'/shape.'.$basinCode.'.json';
	$shapeLines = file($shapeFile);
	$shapeInfo = sg_json_decode($shapeLines[0]);

	//$ret .= print_o($shapeLines,'$shapeLines');


	//unset($shapeInfo->coordinates[1]);
	//$result['shapeInfo'] = $shapeInfo;
	//return $result;

	//$result['table'] .= 'BASIN '.$basinCode;


	// Generate boundery coordinates by box or step for rain area calculate
	$xmin = $shapeInfo->coordinates[0]->bounding_box->xmin;
	$ymin = $shapeInfo->coordinates[0]->bounding_box->ymin;
	$xmax = $shapeInfo->coordinates[0]->bounding_box->xmax;
	$ymax = $shapeInfo->coordinates[0]->bounding_box->ymax;


	$gis = array();
	$gis['paths'] = array();

	$shapeBox = (object) array('xmin'=>$xmin,'ymin'=>$ymin,'xmax'=>$xmax,'ymax'=>$ymax);


	$step = $shpPoint > 0 && $shpPoint <= count($shapeInfo->coordinates[0]->path) ? round(count($shapeInfo->coordinates[0]->path)/$shpPoint) : 1;
	if ($step < 1) $step = 1;
	foreach ($shapeInfo->coordinates[0]->path as $i => $coor) {

		$x = $coor[0];
		$y = $coor[1];

		$gis['paths'][]=array('lat'=>$x,
													'lng'=>$y,
													);
	}

	$ui = new Ui();
	foreach ($basinList as $key => $value) {
		$ui->add('<a href="'.url('flood/forcast/hr3/map',array('shp'=>$key)).'">'.$value.'</a>');
	}

	$ret.='<div class="map-output">'._NL;
	$ret .= '<div class="map-nav">';
	$ret .= $ui->build();
	$ret .= 'COUNT of coordinates = '.count($shapeInfo->coordinates).'<br />';
	$ret .= '</div>';
	$ret.='<div class="map-canvas" id="map-canvas" width="600" height="800" style="width:100%;height:100%;">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>'._NL;

	// Load js
	//head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	//head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');

	$ret .= '<script async defer
    src="https://maps.googleapis.com/maps/api/js?key='.cfg('gmapkey').'&callback=initMap">
    </script>';

		$ret.='<script type="text/javascript">

		function initMap() {
			var gis='.json_encode($gis).';

        var map = new google.maps.Map(document.getElementById("map-canvas"), {
          zoom: 7,
          center: {lat: 9.5000, lng: 99.4000},
          mapTypeId: "terrain"
        });

        // Construct the polygon.
        var basinPath = new google.maps.Polygon({
          paths: gis.paths,
          strokeColor: "#FF0000",
          strokeOpacity: 0.8,
          strokeWeight: 1,
          fillColor: "#FF0000",
          fillOpacity: 0.35
        });
        basinPath.setMap(map);
      }
	</script>';

	$ret.='<style type="text/css">
	.map-output {width:100%; position: relative; height:780px;}
	.map-nav {position: absolute; top: 64px; left: 0; z-index: 1; padding: 8px;}

	</style>';


	//$ret .= print_o($shapeInfo,'$shapeInfo');
	return $ret;
}
?>