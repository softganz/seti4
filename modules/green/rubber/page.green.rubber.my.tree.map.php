<?php
/**
* Green :: Tree Map
* Created 2020-11-10
* Modify  2020-11-30
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/rubber/my/tree/map/{id}
*/

$debug = true;

function green_rubber_my_tree_map($self, $plantId) {
	$plantInfo = R::Model('green.plant.get', $plantId, '{data: "orgInfo"}');

	if (!$plantInfo) return 'ไม่มีรายการ';

	$orgInfo = $plantInfo->orgInfo;
	$landInfo = R::Model('green.land.get', $plantInfo->landId);

	$map = [
		'zoom' => 8,
		'dropPin' => true,
		'drag' => 'map',
		'updateUrl' => url('green/my/info/location.save/'.$plantId),
		'updatePara' => ['mapTable' => 'plant'],
		'updateIcon' => '#green-plant-'.$plantId.' .-land-map',
		'pin' => [],
	];

	$ret = '';

	//$areaName = $orgInfo->info->address;

	// Get All Land
	$stmt = 'SELECT
		l.*
		, AsText(l.`location`) location, X(l.`location`) lat, Y(l.`location`) lnt
		FROM %ibuy_farmplant% l
		WHERE l.`landid` = :landid AND l.`plantid` != :plantid';
	$allLandList = mydb::select($stmt,':plantid',$plantInfo->plantId, ':landid', $plantInfo->landId);
	//$ret .= print_o($allLandList, '$allLandList');


	if ($plantInfo->info->location) {
		list($currentLat, $currentLnt) = explode(',', $plantInfo->info->location);
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

	/*
	foreach ($allLandList->items as $item) {
		//$address = SG\implode_address($item, 'short');

		if ($item->location) {
			$gis['all'][] = array(
				'lat' => $item->lat,
				'lng' => $item->lnt,
				'title' => $item->landname,
				'content' => '<p>พื้นที่ : '
					. ($item->arearai ? $item->arearai.' ไร่ ' : '')
					. ($item->areahan ? $item->areahan.' งาน ' : '')
					. ($item->areawa ? $item->areawa.' ตารางวา' : '')
					. '</p>'
			);
		}
		//if ($address) $gis['address'][] = $address;
	}
	*/


	//new Toolbar($self, $orgInfo->name.' @Green Smile','my.shop');

	page_class('-app-hide-toolbar -page-fill');

	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE || $plantInfo->uid == i()->uid;


	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่ '.$plantInfo->productName.($plantInfo->info->productcode ? ' #'.$plantInfo->info->productcode : '').'</h3></header>';

	//$ret.=print_o($orgInfo,'$orgInfo');
	//$ret .= print_o($plantInfo, '$plantInfo');

	$navMap = '<nav class="nav -map-canvas">'
		. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Map</span></a>')
		. '</nav>';

	$ret .= '<div id="green-rubber-my-tree-map" class="page -map">'._NL
		. $navMap
		. '<div id="map-canvas" class="map-canvas"></div>'._NL
		. '</div>'._NL;


	// Section :: Script
	$ret .= '<script type="text/javascript"><!--
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


		--></script>';

	return $ret;
}
?>