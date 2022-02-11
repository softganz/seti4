<?php
/**
* Green :: Land Map
* Created 2020-11-16
* Modify  2020-11-30
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/my/land/map/{id}
*/

$debug = true;

function green_my_land_map($self, $landId) {
	// Data Model
	$landId = ($landInfo = R::Model('green.land.get',$landId, '{data: "orgInfo"}'))->landId;

	if (!$landId) return message('error', 'PROCESS ERROR');

	$orgInfo = $landInfo->orgInfo;
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE || $landInfo->uid == i()->uid;

	$map = [
		'zoom' => 8,
		'dropPin' => true,
		'drag' => 'map',
		'debug' => true,
		'updateUrl' => url('green/my/info/location.save/'.$landId),
		'updatePara' => ['mapTable' => 'land'],
		'updateIcon' => '#green-land-'.$landId.' .-land-map',
		'pin' => [],
	];

	$ret = '';

	//$areaName = $orgInfo->info->address;

	// Get All Land
	$stmt = 'SELECT
		l.*
		, AsText(l.`location`) location, X(l.`location`) lat, Y(l.`location`) lnt
		FROM %ibuy_farmland% l
		WHERE l.`orgid` = :orgid AND l.`landid` != :landid';
	$allLandList = mydb::select($stmt,':orgid',$landInfo->info->orgid, ':landid', $landId);
	//$ret .= print_o($allLandList, '$allLandList');


	if ($landInfo->info->location) {
		list($currentLat, $currentLnt) = explode(',', $landInfo->info->location);
		$map['zoom'] = 16;
		$map['pin']['lat'] = floatval($currentLat);
		$map['pin']['lng'] = floatval($currentLnt);
		$map['center'] = ['lat' => $map['pin']['lat'], 'lng' => $map['pin']['lng']];
	} else if ($orgInfo->info->location) {
		list($centerLat, $centerLnt) = explode(',', $orgInfo->info->location);
		$map['center'] = ['lat' => floatval($centerLat), 'lng' => floatval($centerLnt)];
		$map['zoom'] = 16;
	} else {
		$location = '';
		foreach ($allLandList->items as $rs) {
			if ($rs->location) {
				$location = $rs->lat.','.$rs->lnt;
				$map['zoom'] = 16;
				break;
			}
		}
		if (!$location) $location = '13.2000,100.0000';
		list($centerLat, $centerLnt) = explode(',', $location);
		$map['center'] = ['lat' => floatval($centerLat), 'lng' => floatval($centerLnt)];
	}
	if ($currentLnt) $currentLatLnt = $currentLat.','.$currentLnt;

	foreach ($allLandList->items as $item) {
		if ($item->location) {
			$map['markers'][] = [
				'lat' => $item->lat,
				'lng' => $item->lnt,
				'content' => '<h5>'.$item->landname.'</h5>',
			];
		}
	}



	// View Model
	page_class('-app-hide-toolbar');


	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่ '.$landInfo->landName.'</h3></header>';

	//$ret.=print_o($map,'$map');
	//$ret.=print_o($orgInfo,'$orgInfo');
	//$ret .= print_o($landInfo, '$landInfo');


	$mapNav = '<nav class="nav -map-canvas">'
		. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Map</span></a>')
		. '</nav>';

	$ret .= '<div id="green-land-map" class="page -map">'._NL
		. $mapNav
		. '<div id="map-canvas" class="map-canvas"></div>'._NL
		. '</div>'._NL;

	// Section :: Script
	$ret .= '<script type="text/javascript">
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {refresh: false, permission: "ACCESS_FINE_LOCATION"}
			return options
		}

		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})

		var landMap

		function initMap() {
			landMap = new sgDrawMap("landMap",'.json_encode($map).');
		}
	</script>';

	return $ret;
}
?>