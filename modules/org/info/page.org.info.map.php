<?php
/**
* Org :: Map
* Created 2021-10-13
* Modify  2021-10-13
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.map
*/

$debug = true;

class OrgInfoMap extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

		$isAdmin = $this->orgInfo->RIGHT & _IS_ADMIN;
		$isEdit = $this->orgInfo->RIGHT & _IS_EDITABLE;
		$isAndroidApp = R()->appAgent;

		$center = explode(',', SG\getFirst(property('project:map.center:0'),'13.2000,100.0000'));
		list($lat,$lng) = explode(',', $this->orgInfo->info->location);

		$map = [
			'zoom' => 6,
			'gisDigit' => 6,
			'dropPin' => $isEdit ? true : false,
			'drag' => 'pin',
			'debug' => true,
			'updateUrl' => url('org/info/api/'.$this->orgId.'/location.save'),
			'width' => '100%',
			'height' => '100%',
			'center' => ['lat' => $center[0], 'lng' => $center[1]],
			'pin' => [
				'lat' => $lat ? floatval($lat) : NULL,
				'lng' => $lng ? floatval($lng) : NULL,
				// 'title' => $this->orgInfo->name,
				// 'content' => '<h5>'.$this->orgInfo->name.'</h5>',
			],
		];




		// $center = explode(',', SG\getFirst(property('project:map.center:0'),'13.2000,100.0000'));
		// $gis['center'] = array('lat'=>$center[0],'lng'=>$center[1]);
		// $gis['zoom'] = (int)SG\getFirst(property('project:map.zoom:0'),6);

		// if ($orgInfo->info->address && empty($orgInfo->info->location)) {
		// 	$gis['zoom'] = 12;
		// } else if ($orgInfo->info->location) {
		// 	list($lat,$lng) = explode(',', $orgInfo->info->location);
		// 	$gis['center'] = array('lat' => floatval($lat), 'lng' => floatval($lng));
		// 	$gis['zoom'] = 13;
		// 	$gis['current'] = array(
		// 		'lat' => $lat,
		// 		'lng' => $lng,
		// 		'title' => $orgInfo->name,
		// 		'content' => '<h4>'.$orgInfo->name.'</h4><p>พื้นที่ : '.$orgInfo->info->area.'</p>'
		// 	);
		// }

		// $gis['title'] = $orgInfo->name;
		// $gis['address'] = array();
		// if ($orgInfo->info->address) $gis['address'][] = $orgInfo->info->address;


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่'.$this->orgInfo->name,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Container([
				'id' => 'org-info-map',
				'class' => 'page -map',
				'children' => [
					'<nav class="nav -map-canvas">'
						. '<a id="getgis" class="btn"><i class="icon -material">my_location</i><span class="-hidden">ขอพิกัดปัจจุบัน</span></a> '
						. (R()->appAgent->OS == 'Android' ? '<a id="googlenav" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="geo:?q='.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Navigator</span></a>' : '<a id="googlemap" class="sg-action btn'.($currentLatLnt ? '' : ' -hidden').'" href="https://www.google.com/maps/place/'.$currentLatLnt.'" data-webview="googlemap" target="_blank"><i class="icon -material">navigation</i><span class="-hidden">Google Map</span></a>')
						. '</nav>',
					'<div id="map-canvas" class="map-canvas"></div>',
					'<script type="text/javascript">
						// function onWebViewComplete() {
						// 	console.log("CALL onWebViewComplete FROM WEBVIEW")
						// 	var options = {refresh: false, permission: "ACCESS_FINE_LOCATION"}
						// 	return options
						// }

						$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})

						var orgMap

						function initMap() {
							orgMap = new sgDrawMap("orgMap",'.json_encode($map).');
						}
					</script>',
					// new DebugMsg($map,'$map'),
					// new DebugMsg($this->orgInfo,'$orgInfo'),
				], // children
			]), // Container
		]);
	}
}

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
?>

<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_info_map($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self,'Map', NULL, $orgInfo);

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

	$ret = '';

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่องค์กร @'.$orgInfo->name.'</h3></header>';

	$ret .= '<span class="-hidden">Address : <span id="org-info-map-address">'.$orgInfo->info->address.'</span>';
	$ret .= ' Location : <span id="org-info-map-location">'.($orgInfo->info->location ? $orgInfo->info->location : '').'</span></span>';

	$ret .= '<div id="org-info-map" class="page -map">'._NL
		.'<div id="map_canvas"></div>'._NL
		.'</div>'._NL;


	$center = explode(',', SG\getFirst(property('project:map.center:0'),'13.2000,100.0000'));
	$gis['center'] = array('lat'=>$center[0],'lng'=>$center[1]);
	$gis['zoom'] = (int)SG\getFirst(property('project:map.zoom:0'),6);

	if ($orgInfo->info->address && empty($orgInfo->info->location)) {
		$gis['zoom'] = 12;
	} else if ($orgInfo->info->location) {
		list($lat,$lng) = explode(',', $orgInfo->info->location);
		$gis['center'] = array('lat' => floatval($lat), 'lng' => floatval($lng));
		$gis['zoom'] = 13;
		$gis['current'] = array(
			'lat' => $lat,
			'lng' => $lng,
			'title' => $orgInfo->name,
			'content' => '<h4>'.$orgInfo->name.'</h4><p>พื้นที่ : '.$orgInfo->info->area.'</p>'
		);
	}

	$gis['title'] = $orgInfo->name;
	$gis['address'] = array();
	if ($orgInfo->info->address) $gis['address'][] = $orgInfo->info->address;


	//$ret.=print_o($orgInfo,'$orgInfo');

	// Section :: Script
	$ret .= '<script type="text/javascript"><!--
	';
	if ($isEdit) {
		$ret .= '
		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initProjectMap")})

		function initProjectMap() {
			var gis = '.json_encode($gis).'
			var is_point = gis.current ? true : false

			$("#cboxLoadedContent .-map-wrapper").css({width: $("#cboxLoadedContent").css("width"), height: $("#cboxLoadedContent").css("height")})
			$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

			if (!is_point) notify("คลิกบนแผนที่ตรงตำแหน่งที่ต้องการวางหมุด")


			var orgUpdate = function(lat,lng) {
				var para = {}
				para.tr = "'.$orgId.'"
				para.action = "save"
				para.group = "org"
				para.fld = "location"
				para.value = Number(lat).toPrecision(10)+","+Number(lng).toPrecision(10)
				para.debug = "inline"
				console.log("Update Location")
				$.post("'.url('org/edit/info/'.$orgId).'", para, function(data) {
					console.log(data)
					$("#org-info-map-location").text(para.value)
				},"json")
			}

			var $map = new GMaps({
				div: "#map_canvas",
				zoom: gis.zoom,
				scrollwheel: true,
				lat: gis.center.lat,
				lng: gis.center.lng,

				click: function(event) {
					notify("ลากหมุดเพื่อเปลี่ยนตำแหน่ง", 5000)
					if (is_point) return

					orgUpdate(event.latLng.lat(),event.latLng.lng())
					$map.addMarker({
						lat: event.latLng.lat(),
						lng: event.latLng.lng(),
						draggable: true,
						dragend: function(event) {
							orgUpdate(event.latLng.lat(),event.latLng.lng())
						}
					})
					is_point = true
				}
			})

			if (gis.current) {
				$map.addMarker({
					lat: gis.current.lat,
					lng: gis.current.lng,
					draggable: true,
					infoWindow: {content: gis.title},
					dragend: function(event) {
						orgUpdate(event.latLng.lat(),event.latLng.lng())
					}
				})
			}

			if (gis.address) {
				$.each( gis.address, function(i, address) {
					GMaps.geocode({
						address: address,
						callback: function(results, status) {
							if (status == "OK") {
								var latlng = results[0].geometry.location;
								if (!is_point) $map.setCenter(latlng.lat(), latlng.lng());
								$map.addMarker({
									lat: latlng.lat(),
									lng: latlng.lng(),
									icon: "/library/img/geo/circle-green.png",
									infoWindow: {content: address}
								});
							}
						}
					})
				})
			}
		}
	';
	} else {
		$ret.='
	// your code here - init map ...
	loadGoogleMaps("initProjectMap")

	function initProjectMap() {
		var gis = '.json_encode($gis).'
		var is_point = gis.current ? true : false

		$("#cboxLoadedContent .-map-wrapper").css({width: $("#cboxLoadedContent").css("width"), height: $("#cboxLoadedContent").css("height")})
		$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

		var $map = new GMaps({
			div: "#map_canvas",
			zoom: gis.zoom,
			scrollwheel: true,
			lat: gis.center.lat,
			lng: gis.center.lng,
		})

		if (gis.current) {
			$map.addMarker({
				lat: gis.current.lat,
				lng: gis.current.lng,
				infoWindow: {content: gis.title},
			})
		}

		if (gis.address) {
			$.each( gis.address, function(i, address) {
				GMaps.geocode({
					address: address,
					callback: function(results, status) {
						if (status == "OK") {
							var latlng = results[0].geometry.location;
							if (!is_point) $map.setCenter(latlng.lat(), latlng.lng())
							is_point = true
							$map.addMarker({
								lat: latlng.lat(),
								lng: latlng.lng(),
								icon: "/library/img/geo/circle-green.png",
								infoWindow: {content: address}
							});
						}
					}
				})
			})
		}
	}
	';
	}

	$ret .= '
	--></script>';

	return $ret;
}
?>