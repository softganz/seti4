<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function green_land_map($self, $landInfo = NULL) {
	$landId = $landInfo->landId;

	if (!$landId) return message('error', 'PROCESS ERROR');

	$orgInfo = $landInfo->orgInfo;
	$map = array(
		'zoom' => 12,
		'dropPin' => false,
		'drag' => false,
		'locationText' => '<h5>ตำแหน่งปัจจุบัน</h5>',
	);

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
		$map['pin'] = array(
			'lat' => floatval($currentLat),
			'lng' => floatval($currentLnt),
			//'title' => $landInfo->landName,
			'content' => '<h4>'.$landInfo->landName.'</h4><p>พื้นที่ : '.$areaName.'</p>'
		);
		$map['center'] = array('lat' => $map['pin']['lat'], 'lng' => $map['pin']['lng']);
	} else if ($orgInfo->info->location) {
		list($centerLat, $centerLnt) = explode(',', $orgInfo->info->location);
		$map['center'] = array('lat' => floatval($centerLat), 'lng' => floatval($centerLnt));
	} else {
		$location = '';
		foreach ($allLandList->items as $rs) {
			if ($rs->location) {
				$location = $rs->lat.','.$rs->lnt;
				break;
			}
		}
		if (!$location) $location = '13.2000,100.0000';
		list($centerLat, $centerLnt) = explode(',', $location);
		$map['center'] = array('lat' => floatval($centerLat), 'lng' => floatval($centerLnt));
	}
	if ($currentLnt) $currentLatLnt = $currentLat.','.$currentLnt;

	foreach ($allLandList->items as $item) {
		//$address = SG\implode_address($item, 'short');

		if ($item->location) {
			$map['markers'][] = array(
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
	}


	//new Toolbar($self, $orgInfo->name.' @Green Smile','my.shop');

	page_class('-app-hide-toolbar');
	
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE || $landInfo->uid == i()->uid;

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่ '.$landInfo->landName.'</h3></header>';

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