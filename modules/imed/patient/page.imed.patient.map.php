<?php
/**
* iMed :: Patient Map
* Created 2019-02-28
* Modify  2020-12-08
*
* @param Object $self
* @param Int $psnId
* @return String
*
* @usage imed/patient/{id}/map
*/

$debug = true;

function imed_patient_map($self, $psnId = NULL) {
	// Data Model

	$psnId = SG\getFirst($psnId,post('id'));
	$getAllLoc = post('show');

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');
	
	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error',$psnInfo->error);

	$isAndroidApp = R()->appAgent;
	$ret = '';
	
	$map = array(
		'dropPin' => true,
		'drag' => 'map',
		'updateUrl' => url('imed/patient/'.$psnId.'/info/gis.save'),
		'zoom' => 9,
		'center' => array('lat' => 7.011666, 'lng' => 100.470088),
		'height' => $isAndroidApp ? '100%' : '600px',
		'pin' => array(
			'title' => $psnInfo->fullname,
			'content' => '<h5>'.$psnInfo->fullname.'</h5>',
		),
	);

	if (debug('map')) $map['debug'] = true;

	//$ret .= print_o($psnInfo,'$psnInfo');

	$currentLatLnt = $psnInfo->info->latlng;	
	if ($currentLatLnt) {
		list($currentLat, $currentLnt) = explode(',', $currentLatLnt);
		$map['zoom'] = 16;
		$map['pin']['lat'] = floatval($currentLat);
		$map['pin']['lng'] = floatval($currentLnt);
		//$map['pin']['content'] = '<p>'.$psnInfo->fullname.'</p>';
		$map['center'] = array('lat' => $map['pin']['lat'], 'lng' => $map['pin']['lng']);
		//$gis['address'] = 'ต.'.$psnInfo->info->subdistname.' อ.'.$psnInfo->info->distname.' จ.'.$psnInfo->info->provname;
	} else if ($psnInfo->info->subdistname) {
		$map['zoom'] = 14;
		$address['address'][] = 'ต.'.$psnInfo->info->subdistname.' อ.'.$psnInfo->info->distname.' จ.'.$psnInfo->info->provname;
	}

	if ($getAllLoc) {
		$dbs = mydb::select('SELECT CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt, `created` FROM %imed_patient_gis% g WHERE `pid` = :psnId AND `latlng` IS NOT NULL',':psnId',$psnId);

		foreach ($dbs->items as $item) {
			$map['markers'][] = array(
				'lat' => $item->lat,
				'lng' => $item->lnt,
				'content' => '<h5>[Moved]</h5>'.sg_date($item->created,'ว ดดด ปปปป'),
			);
		}
	}

	//page_class('-app-hide-toolbar');
	page_class('-page-full');


	// View Model	

	$mapNav = '<nav class="nav -map-canvas">'
		. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Map</span></a>')
		. '</nav>';

	$ret .= '<div id="imed-patient-map" class="page -map">'._NL
		. $mapNav
		. '<div id="map-canvas" class="map-canvas"></div>'._NL
		. '</div>'._NL;

	//$ret .= print_o($map,'$map');

	// Map Script
	$ret .= '<script type="text/javascript">
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {refresh: false, permission: "ACCESS_FINE_LOCATION"}
			return options
		}

		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})

		var patientMap

		function initMap() {
			patientMap = new sgDrawMap("patientMap",'.json_encode($map).');
		}
	</script>';

	return $ret;
}






function imed_patient_map_old($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');
	
	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error',$psnInfo->error);

	
	$currentLatLnt = $psnInfo->info->latlng;	


	if ($isEdit) {
		$inlineAttr['class']='sg-inline-edit';
		$inlineAttr['data-update-url'] = url('imed/patient/'.$psnId.'/gis.save');
		$inlineAttr['data-psnid'] = $psnId;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret.='<div id="imed-patient-map" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<nav class="nav -map"><span id="currentLoc"></span> <a id="getgis" class="btn" href="javascript:void()"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
		//. '<a id="usegis" class="-hidden" href="javascript:void(0)">ใช้ตำแหน่งปัจจุบันเป็นพิกัดบ้าน</a> '
		//. '<a id="getroute" class="-hidden" href="javascript:void(0)">เส้นทาง</a> '
		. '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">room</i><span class="-hidden">Google Map</span></a> '
		. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '')
		.' '
		. view::inlineedit(
				array('options'=>'{class: "-loc", var: "loc", placeholder: "7.00,100.00"}'),
				$psnInfo->info->latlng,
				$isEdit
			)
		.($isEdit ? '<a id="imed-patient-map-delbtn" class="sg-action btn -link" href="'.url('imed/patient/'.$psnId.'/gis.remove').'" data-rel="notify" data-title="ลบตำแหน่งบนแผนที่" data-confirm="ต้องการลบตำแหน่งบนแผนที่ กรุณายืนยัน?" data-callback="imedRemovePatientLoc"><i class="icon -material -gray">cancel</i></a>' : '')
		. '</nav>';


	$tables = new Table();
	$tables->colgroup = array('label -nowrap'=>'');

	$tables->rows[] = array('ที่อยู่',$psnInfo->info->address);

	$tables->rows[] = array('พิกัดบ้าน',
		view::inlineedit(
			array('options'=>'{class: "-fill -loc", var: "loc", onblur: "submit", result: "html"}'),
			$psnInfo->info->latlng,
			$isEdit
		)
		. ($isEdit ? '<a id="imed-patient-map-delbtn" class="sg-action'.($psnInfo->info->gis ? '' : ' -hidden').'" href="'.url('imed/patient/'.$psnId.'/gis.remove').'" data-rel="notify" data-title="ลบตำแหน่งบนแผนที่" data-confirm="ต้องการลบตำแหน่งบนแผนที่ กรุณายืนยัน?" data-callback="imedRemovePatientLoc" style="position: absolute; top: 14px; right: 8px;"><i class="icon -material -gray">cancel</i></a>' : '')
		,
	);


	$gis['lat'] = 7.011666;
	$gis['lng'] = 100.470088;
	$gis['zoom'] = 9;
	$gis['draggable'] = true;
	if (post('show') == 'all') {
		$gis['zoom'] = 12;
		$gis['draggable'] = false;
		$dbs = mydb::select('SELECT CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt, `created` FROM %imed_patient_gis% g WHERE `pid` = :psnId',':psnId',$psnId);

		foreach ($dbs->items as $item) {
			$gis['markers'][] = array(
				'latitude' => $item->lat,
				'longitude' => $item->lnt,
				'content' => sg_date($item->created,'ว ดดด ปปปป'),
			);
		}
	} else if ($psnInfo->info->latlng) {
		$gis['lat'] = $psnInfo->info->lat;
		$gis['lng'] = $psnInfo->info->lnt;
		$gis['zoom'] = 14;
		$gis['address'] = 'ต.'.$psnInfo->info->subdistname.' อ.'.$psnInfo->info->distname.' จ.'.$psnInfo->info->provname;
		$gis['markers'][] = array(
			'latitude' => $psnInfo->info->lat,
			'longitude' => $psnInfo->info->lnt,
		);
		$gis['marker'] = array(
			'lat' => $psnInfo->info->lat,
			'lng' => $psnInfo->info->lnt,
			'content' => '<h4>'.$psnInfo->fullname.'</h4><p><img src="'.imed_model::patient_photo($psnId).'" class="right" width="64" height="64" />'.$psnInfo->info->address.'</p>'
		);
	} else if ($psnInfo->info->subdistname) {
		$gis['zoom'] = 14;
		$gis['address'] = 'ต.'.$psnInfo->info->subdistname.' อ.'.$psnInfo->info->distname.' จ.'.$psnInfo->info->provname;
		$gis['marker'] = NULL;
	}

	//$ret .= ($isEdit?'<p>คลิกบนแผนที่เพื่อวางหมุดหรือลากหมุดเพื่อเปลี่ยนพิกัดของบ้าน - <a class="sg-action" href="'.url('imed/patient/map/'.$psnId,'show=all').'" data-rel="#imed-app">Show all</a></p>':'')._NL;

	$ret .= '<div id="map_canvas" width="100%" height="600"></div>'._NL;

	$ret .= '</div>';



	//$ret.=print_o($gis,'$gis').print_o($psnInfo,'$psnInfo');



	if ($isEdit) {
		$ret.='<script type="text/javascript"><!--
		function imedRemovePatientLoc() {
			$(".inline-edit-field.-loc").data("value","").html("<span></span>")
		}

		$(document).ready(function() {
			var currentLoc={};
			var $container = $("#imed-patient-map")
			var $mapField = $(".inline-edit-field.-loc")
			var gisDigit = 6
			var infoWindow
			var gis='.json_encode($gis).';
			var is_point=false;

			var $map;
			var center={};
			var marker;

			notify("คลิกบนแผนที่เพื่อวางหมุดหรือลากหมุดเพื่อเปลี่ยนพิกัดของบ้าน", 10000)

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

			$("#usegis").click(function() {
				var latlng = new google.maps.LatLng(currentLoc.x, currentLoc.y);
				marker.setPosition(latlng);
				//var location = currentLoc.x+","+currentLoc.y
				//console.log("Location GIS = "+location)
				saveLocation(latlng)
			});

			$("#getroute").click(function() {
				$map.drawRoute({
					origin: [currentLoc.x, currentLoc.y],
					destination: [gis.marker.lat, gis.marker.lng],
					travelMode: "driving",
					strokeColor: "#009900",
					strokeOpacity: 0.6,
					strokeWeight: 6
				});
			});

			function getLocation(location) {
				console.log("Current location "+location)
				currentLoc.x=location.coords.latitude;
				currentLoc.y=location.coords.longitude;
				$("#currentLoc").text(currentLoc.x+","+currentLoc.y);
				$("#usegis").removeClass("hidden");
				$("#getroute").removeClass("hidden");
				//		$map.drawCircle({lat:currentLoc.x, lng:currentLoc.y, radius:80,strokeColor: "#ff0000",strokeWeight: 1,strokeOpacity: 1,fillColor:"#ff0000",fillOpacity: 1});
				$map.addMarker({
					lat: currentLoc.x,
					lng: currentLoc.y,
					icon: "/library/img/geo/circle-red.png",
				});
				$map.setCenter(currentLoc.x, currentLoc.y);
			}

			function toLocationString(e) {
				return e.latLng.lat().toFixed(gisDigit) + "," + e.latLng.lng().toFixed(gisDigit)
			}

			function saveLocation(loc) {
				var updateUrl = $container.data("updateUrl")
				var para = {}
				para.loc = toLocationString(loc)
				$.post(updateUrl, para, function(html) {
					notify(html)
				})
				$("#imed-patient-map-delbtn").show()
				$mapField.html("<span>"+para.loc+"</span>").data("value",para.loc)
			}

			$map = new GMaps({
				el: "#map_canvas",
				lat: gis.lat,
				lng: gis.lng,
				zoom: gis.zoom,
				click: function(e) {
					if (!is_point) {
						marker=$map.addMarker({
							lat: e.latLng.lat(),
							lng: e.latLng.lng(),
							draggable: true,
							click: function(e) {},
							dragend: function(e) {saveLocation(e)},
						});
						saveLocation(e)
						is_point = true;
					}
				}
			})

			if (gis.address) {
				GMaps.geocode({
					address: gis.address,
					callback: function(results, status) {
						if (status == "OK") {
							var latlng = results[0].geometry.location;
							$map.addMarker({
								lat: latlng.lat(),
								lng: latlng.lng(),
								icon: "/library/img/geo/circle-green.png",
								infoWindow: {content: gis.address},
							});
							if (!gis.marker) $map.setCenter(latlng.lat(), latlng.lng());
						}
					}
				})
			}

			if (gis.marker) {
				is_point=true;
				marker = $map.addMarker({
					lat: gis.marker.lat,
					lng: gis.marker.lng,
					draggable: true,
					infoWindow: {content: gis.marker.content},
					click: function(e) {},
					dragend: function(e) {saveLocation(e)},
				});
			}
		});
		--></script>';
	} else {
		$ret.='<script type="text/javascript">
			$(document).ready(function() {
				var gis='.json_encode($gis).';
				$map = new GMaps({
					el: "#map_canvas",
					lat: gis.lat,
					lng: gis.lng,
					zoom: gis.zoom,
					scrollwheel: false,
				});
				if (gis.address) {
					GMaps.geocode({
						address: gis.address,
						callback: function(results, status) {
							if (status == "OK") {
								var latlng = results[0].geometry.location;
								$map.addMarker({
									lat: latlng.lat(),
									lng: latlng.lng(),
									icon: "/library/img/geo/circle-green.png",
									infoWindow: {content: gis.address},
									mouseover: function(e) {$map.showInfoWindow(this);},
									mouseout: function(e) {$map.hideInfoWindows();},
								});
								if (!gis.marker) $map.setCenter(latlng.lat(), latlng.lng());
							}
						}
					});
				}
				if (gis.marker) {
					$map.addMarker({
						lat: gis.marker.lat,
						lng: gis.marker.lng,
						title: gis.marker.content,
						infoWindow: {content: gis.marker.content},
						mouseover: function(e) {$map.showInfoWindow(this);},
						mouseout: function(e) {$map.hideInfoWindows();},
					});
				}
			});
			</script>';
		}
		$ret .= '<style type="text/css">
		.sg-inline-edit {position: relative;}
		.nav.-map {background-color: transparent; position: absolute; z-index: 1; top: 64px; left: 70px;}
		.nav.-map a>.icon {margin: 0;}
		.module-imed.-app.-softganz-app .toolbar.-main.-imed {display: none;}
		.module-imed.-app.-softganz-app.-module-has-toolbar .page.-content {padding-top: 0;}
		.inline-edit-field.-loc {width: 100px; overflow: hidden; background-color: #fff; border-radius: 4px;}
		</style>';
	return $ret;
}
?>