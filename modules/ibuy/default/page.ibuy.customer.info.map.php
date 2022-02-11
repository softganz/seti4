<?php
/**
* iBuy My Dashboard
* Created 2019-11-15
* Modify  2019-11-15
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_customer_info_map($self, $customerInfo = NULL) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');
	$ret = '';
	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่ลูกค้า</h3></header>';

	$areaName = $customerInfo->info->custaddress;
	$currentLatLnt = $customerInfo->info->location;
	$currentLat = $customerInfo->info->lat;
	$currentLnt = $customerInfo->info->lnt;


	$ret .= '<nav class="nav -map"><span id="currentLoc"></span> <a id="getgis" class="btn" href="javascript:void()"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		//. '<a id="usegis" class="-hidden" href="javascript:void(0)">ใช้ตำแหน่งปัจจุบันเป็นพิกัดบ้าน</a> '
		//. '<a id="getroute" class="-hidden" href="javascript:void(0)">เส้นทาง</a> '
		. '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">room</i><span class="-hidden">Google Map</span></a> '
		. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '')
		. '</nav>';

	$ret .= '<div id="ibuy-customermap" class="ibuy-map -customer" width="100%" height="400">'._NL
		. '<div id="map_canvas"></div>'._NL
		. '</div>'._NL;



	$center = explode(',', SG\getFirst($customerInfo->info->location,'13.2000,100.0000'));
	$gis['center'] = array('lat'=>$center[0],'lng'=>$center[1]);
	$gis['zoom'] = (Int) SG\getFirst($center ? 8 : NULL,6);

	if ($areaName && empty($currentLat)) {
		$gis['zoom'] = 12;
	} else if ($currentLat) {
		$gis['center'] = array('lat' => $currentLat, 'lng' => $currentLnt);
		$gis['zoom'] = 13;
		$gis['current'] = array(
			'lat' => $currentLat,
			'lng' => $currentLnt,
			'title' => $customerInfo->name,
			'content' => '<h4>'.$customerInfo->name.'</h4><p>พื้นที่ : '.$areaName.'</p>'
		);
	}

	$gis['title'] = $customerInfo->name;
	$gis['address'] = array();

	//$gis['address'][] = $areaName;
	if ($areaName) $gis['address'][] = substr($areaName,strpos($areaName,'ตำบล'));

	//$ret .= print_o($gis,'$gis');
	//$ret .= print_o($customerInfo, '$customerInfo');

	$ret .= '<style type="text/css">
	#cboxLoadedContent {padding: 0;}
	</style>';

	// Section :: Script

	/*
	$ret .= '<script type="text/javascript">
		loadGoogleMaps("initMap")

	function initMap() {
		var data = '.json_encode($gis).'

		console.log(data)
		var map;

		$(".box-page").css({width: "640px", height: "640px", minWidth: "100%", minHeight: "100%"})
		$("#ibuy-customermap").css({minWidth: "100%", minHeight: "100%"})
		$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

		map = new google.maps.Map(document.getElementById("map_canvas"), {
			center: {lat: data.current.lat, lng: data.current.lng},
			zoom: data.zoom
		});

		var myLatLng = {lat: data.current.lat, lng: data.current.lng}
		//console.log(data.markers[0].title)
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			title: data.title
		})
	}

	</script>';
	*/

	$ret .= '<script type="text/javascript">
	$.getScript("https://softganz.com/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})

	// your code here - init map ...
	var customerMap

	function initMap() {
		customerMap = new initCustomerMap("customerMap",
			{
				gisDigit: 4,
				updateUrl: "'.url('ibuy/customer/'.$customerId.'/info/map.save').'",
				updateIcon: "#ibuy-customer-'.$customerId.' .-customer-pin",
			}
		)
	}

	var initCustomerMap = function(thisMap, options = {}) {
		var currentMarker
		var gis = '.json_encode($gis).'
		var is_point = gis.current ? true : false
		var thisMap
		//var dataOptions = $this.data("options")

		var defaults = {
			updateUrl : "",
			mapCanvas: "map_canvas",
			callback : false,
		}

		var settings = $.extend({}, defaults, options)

		var updateUrl = settings.updateUrl


		function clearMap() {
			//$(".project-info-latlng").data("value","").find("span").text("")
			is_point = false
			locationUpdate("")
			currentMarker.setMap(null);
		}

		$(document).on("click", "#save-gis", function() {
			var latLng = $("#edit-data-location").val()
			is_point = latLng != ""
			$(".project-info-latlng").data("value","").find("span").text("")
			locationUpdate($("#edit-data-location").val())
		})


		$(".box-page").css({width: "100%", height: "100%", minWidth: "100%", minHeight: "100%"})
		$("#ibuy-customermap").css({height: "100%", minWidth: "100%", minHeight: "100%"})
		$("#"+settings.mapCanvas).css({height: "100%", minWidth: "100%", minHeight: "100%"})

		if (is_point) notify("ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)
		else if (!is_point) notify("คลิกบนแผนที่ตรงตำแหน่งที่ต้องการวางหมุด / ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)

		$("#getgis").click(function() {
			notify("กำลังหาตำแหน่งปัจจุบัน");
			// Try HTML5 geolocation.
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {
					var pos = {
						lat: position.coords.latitude,
						lng: position.coords.longitude
					};

					$map.setCenter(pos);
					$map.addMarker({
						lat: position.coords.latitude,
						lng: position.coords.longitude,
						icon: "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|CCCCCC|FFFFFF",
						infoWindow: {content: "<p>ตำแหน่งปัจจุบัน คือ "+pos.lat+","+pos.lng+"</p>"},
					})
					console.log(pos)
				}, function() {
					notify("Error: The Geolocation service failed.");
				});
			} else {
				// Browser doesnt support Geolocation
				notify("Error: Browser doesnt support Geolocation.");
			}
			return false;
		});

		var locationUpdate = function(latLng) {
			var para = {}
			para.location = latLng
			console.log("SAVE Location "+para.location)
			$.post(updateUrl, para, function(data) {
				$("#edit-data-location").val(latLng)
				var googleMapUrl = "https://www.google.com/maps/place/"+latLng
				var googleNavUrl = "geo:?q="+latLng

				$("#googlemap").attr("href", googleMapUrl).removeClass("-hidden")
				$("#googlenav").attr("href", googleNavUrl).removeClass("-hidden")

				var mapIcon = latLng != "" ? "where_to_vote" : "room"
				var mapActive = latLng != "" ? "-active" : ""
				if (settings.updateIcon) {
					$(settings.updateIcon)
						//.text(mapIcon)
						.removeClass("-active")
						.addClass(mapActive)
				}
				notify("บันทึกเรียบร้อย"+data, 3000)
				//console.log(data)
			});
		}

		function createMarker(marker) {
			return $map.addMarker({
				lat: marker.lat,
				lng: marker.lng,
				draggable: true,
				infoWindow: {content: "<h2>"+gis.title+"</h2><p>ตำแหน่งปัจจุบัน ลากหมุดเพื่อเปลี่ยนตำแหน่ง</p><nav class=\"nav -map -sg-text-right\"><a id=\"xclear-gis\" class=\"btn -link\" onclick=\""+thisMap+".clearMap()\"><i class=\"icon -material\">cancel</i><span>ลบหมุด</span></a></nav>"},
				dragend: function(event) {
					var latLng = event.latLng.lat().toFixed(settings.gisDigit)+","+event.latLng.lng().toFixed(settings.gisDigit)
					locationUpdate(latLng)
				}
			})
		}
		var $map = new GMaps({
			div: "#"+settings.mapCanvas,
			zoom: gis.zoom,
			scrollwheel: true,
			lat: gis.center.lat,
			lng: gis.center.lng,

			click: function(event) {
				notify("ลากหมุดเพื่อเปลี่ยนตำแหน่ง", 5000)
				if (is_point) return

				var latLng = event.latLng.lat().toFixed(settings.gisDigit)+","+event.latLng.lng().toFixed(settings.gisDigit)
				locationUpdate(latLng)
				var marker = {lat: event.latLng.lat(), lng: event.latLng.lng()}
				currentMarker = createMarker(marker)
				is_point = true
			}
		})

		if (gis.current) {
			currentMarker = createMarker(gis.current)
		}

		if (gis.all) {
			$.each( gis.all, function(i, item) {
				$map.addMarker({
					lat: item.lat,
					lng: item.lng,
					draggable: false,
					icon: "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|CCCCCC|FFFFFF",
					infoWindow: {content: item.title},
				})
			});
		}

		if (gis.address) {
			$.each( gis.address, function(i, address) {
				GMaps.geocode({
					address: address,
					callback: function(results, status) {
						if (status == "OK") {
							var latlng = results[0].geometry.location;
							if (!is_point && i == 0) $map.setCenter(latlng.lat(), latlng.lng());
							$map.addMarker({
								lat: latlng.lat(),
								lng: latlng.lng(),
								icon: "https://softganz.com/library/img/geo/circle-green.png",
								infoWindow: {content: address}
							});
						}
					}
				})
			})
		}

		//console.log(thisMap)
		return {
			clearMap: clearMap
		}
	}
	//var a = new initCustomerMap()
	//console.log(a.inner())
	//console.log(initCustomerMap.gisDigit)
	</script>';

	return $ret;
}
?>