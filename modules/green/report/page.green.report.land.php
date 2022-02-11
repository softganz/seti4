<?php
/**
* Green :: Report Map Land
* Created 2020-11-25
* Modify  2021-01-14
*
* @param Object $self
* @return String
*
* @usage green/report/land
*/

$debug = true;

function green_report_land($self) {
	// Data Model
	$ret = '';

	// Get All Land
	$stmt = 'SELECT
		l.*
		, o.`name` `orgName`
		, AsText(l.`location`) location, X(l.`location`) lat, Y(l.`location`) lng
		FROM %ibuy_farmland% l
			LEFT JOIN %db_org% o ON o.`orgid` = l.`orgid`
		WHERE l.`location` IS NOT NULL';

	$allLandList = mydb::select($stmt);

	//$ret .= print_o($allLandList, '$allLandList');

	$map = array(
		'center' => array('lat' => 12.5, 'lng' => 101.5),
		'zoom' => 6,
	);


	// View Model
	
	//if (R()->appAgent) R::Option('fullpage', true);
	page_class('-page-fill');


	foreach ($allLandList->items as $rs) {
		if ($rs->standard) {
			$iconColor = '02A500';
		} else {
			$iconColor = 'CCCCCC';
		}
		$map['markers'][] = array(
			'lat' => $rs->lat,
			'lng' => $rs->lng,
			'title' => $rs->landname,
			'content' => '<h4><a class="sg-action" href="'.url('green/land/'.$rs->landid).'" target="_blank" data-webview="'.$rs->landname.'">'.$rs->orgName.'</a></h3>'
				. ($rs->standard ? '<p>มาตรฐาน '.($rs->stdextend ? $rs->stdextend.' ' : '').$rs->standard.'</p>' : '')
				. $rs->detail,
			'icon' => 'https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$iconColor.'|FFFFFF',
		);
	}


	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่แปลงผลิต</h3></header>';


	$ret .= '<div id="green-land-map" class="page -map">'._NL
		. '<nav class="nav -map-canvas">'
		. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		. '</nav>'
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