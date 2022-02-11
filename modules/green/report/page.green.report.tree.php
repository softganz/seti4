<?php
/**
* Green :: Report Map Tree
* Created 2020-11-25
* Modify  2020-11-25
*
* @param Object $self
* @return String
*
* @usage green/report/tree
*/

$debug = true;

function green_report_tree($self) {
	$ret = '';

	// Get All Land
	$stmt = 'SELECT
		p.*
		, l.`landname`
		, o.`name` `orgName`
		, AsText(p.`location`) location, X(p.`location`) lat, Y(p.`location`) lng
		, AsText(l.`location`) landLocation, X(l.`location`) landLat, Y(l.`location`) landLng
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %db_org% o ON o.`orgid` = p.`orgid`
		WHERE p.`tagname` = "GREEN,TREE"
			AND (p.`location` IS NOT NULL OR l.`location` IS NOT NULL)';

	$plantDbs = mydb::select($stmt);

	//$ret .= print_o($plantDbs, '$plantDbs');

	$map = array(
		'center' => array('lat' => 12.5, 'lng' => 101.5),
		'zoom' => 6,
	);

	//if (R()->appAgent) R::Option('fullpage', true);
	page_class('-page-fill');


	foreach ($plantDbs->items as $rs) {
		$iconColor = '02A500';
		$map['markers'][] = array(
			'lat' => SG\getFirst($rs->lat, $rs->landLat),
			'lng' => SG\getFirst($rs->lng, $rs->landLng),
			'title' => $rs->productname.' @'.$rs->landname,
			'content' => '<h4>'.$rs->orgName.'</h3>'.$rs->detail,
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