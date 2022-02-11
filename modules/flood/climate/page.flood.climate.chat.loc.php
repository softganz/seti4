<?php
/**
* Flood :: Climate Chat Location
* Created 2020-11-29
* Modify  2021-12-04
*
* @return Widget
*
* @usage flood/climate/chat/loc
*/

class FloodClimateChatLoc extends Page {
	function build() {
		$map = [
			'zoom' => 6,
			'center' => ['lat' => 13.2000, 'lng' => 100.0000],
			'dropPin' => true,
			'drag' => 'map',
			'locationText' => '<nav class="nav -sg-flex -sg-text-center"><a class="sg-action btn -primary" href="#none" data-rel="none"  onClick="saveLocation" data-done="back">ใช้ตำแหน่ง</a></nav>',
		];

		$ret = '<section class="page -map">'._NL
			. '<nav class="nav -map">'
			. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
			. '</nav>'
			. '<div id="map-canvas" class="map-canvas"></div>'._NL
			. '</section>'._NL;

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

			function saveLocation() {
				$("#edit-location").val($("#current-location .value").text())
				console.log("SAVE LOCATION "+landMap.currentMarker())
				//console.log("SAVE LOCATION "+$("#current-location").text())
				//console.log(landMap.currentMarker.lat)
				//console.log(landMap)
				//console.log(landMap.currentMarker())
			}
			--></script>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [$ret],
			]),
		]);
	}
}
?>