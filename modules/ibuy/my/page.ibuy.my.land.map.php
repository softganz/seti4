<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_my_land_map($self, $landId = NULL) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');



	$ret = '';

	$areaName = $shopInfo->info->address;
	list($currentLat, $currentLnt) = explode(',', $shopInfo->info->location);

	if ($landId) {
		$stmt = 'SELECT *
			, CONCAT(X(`location`),",",Y(`location`)) `latlng`
			, X(`location`) `lat`
			, Y(`location`) `lnt`
			FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';

		$landInfo = mydb::select($stmt, ':orgid', $shopId, ':landid', $landId);
		$areaName = SG\implode_address($landInfo, 'short');
		$currentLat = $landInfo->lat;
		$currentLnt = $landInfo->lnt;
	}

	R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');

	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE || $landInfo->uid == i()->uid;

	$ui = new Ui();
	if ($isEdit) {
		//$ui->add('<a class="" href="javascript:void(0)"><i class="icon -material">delete</i></a>');
	}

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แผนที่ '.$landInfo->landname.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	//$ret.=print_o($shopInfo,'$shopInfo');
	//$ret .= print_o($landInfo, '$landInfo');

	if ($isEdit) {

		$ret .= '<div class="ibuy-land-map-text">'._NL;

		$form = new Form('data', url('ibuy/my/info/land.save/'.$landId), 'form-map', 'sg-form -map');
		$form->attr = array('style' => 'padding:0;');
		$form->addField(
			'location',
			array(
				'type' => 'text',
				'label' => 'ละติจูด-ลองจิจูด',
				'value' => $landInfo->latlng,
				'posttext' => '<div class="input-append"><span><a id="clear-gis" class="btn -link -sg-16" href="javascript:void(0)"><i class="icon -material -gray -sg-16">cancel</i></a></span><span><a id="save-gis" class="btn -link -sg-16" href="javascript:void(0)" ><i class="icon -material -gray -sg-16">done</i></a></span></div>',
				'container' => '{class: "-group"}',
			)
		);
		$ret .= $form->build();

		$ret .= '</div>';

		$ret .= '<style type="text/css">
		.ibuy-land-map-text {position: absolute; z-index: 1; top: 56px; left: 70px;}
		#cboxLoadedContent .sg-form.-map {padding: 0; background-color: transparent; border-radius: 2px;}
		#cboxLoadedContent .sg-form.-map .form-item {padding: 0;}
		#cboxLoadedContent .sg-form.-map label {display: none;}
		#edit-data-location {width: 120px;}
		@media (min-width:45em) {
			.ibuy-land-map-text {background-color: #fff;}
			#cboxLoadedContent .sg-form.-map {padding: 2px;}
			#cboxLoadedContent .sg-form.-map label {display: inline-block;}
			.ibuy-land-map-text {top: 10px; left: 270px;}
		}
		</style>';
	}

	$ret .= '<div id="project-info-map" class="project-info-map" width="100%" height="400">'._NL
		. '<div id="map_canvas"></div>'._NL
		. '</div>'._NL;



	$center = explode(',', SG\getFirst($shopInfo->info->location,'13.2000,100.0000'));
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
			'title' => $landInfo->landname,
			'content' => '<h4>'.$landInfo->landname.'</h4><p>พื้นที่ : '.$areaName.'</p>'
		);
	}

	$gis['title'] = $landInfo->landname;
	$gis['address'] = array();

	//$gis['address'][] = $areaName;
	if ($areaName) $gis['address'][] = substr($areaName,strpos($areaName,'ตำบล'));

	// Get multiple area
	$stmt = 'SELECT
		l.*
		, AsText(l.`location`) location, X(l.`location`) lat, Y(l.`location`) lnt
		FROM %ibuy_farmland% l
		WHERE l.`orgid` = :orgid AND l.`landid` != :landid';
	$landList = mydb::select($stmt,':orgid',$shopId, ':landid', $landId);
	//$ret .= print_o($landList, '$landList');


	foreach ($landList->items as $item) {
		$address = SG\implode_address($item, 'short');

		if ($item->location) {
			$gis['all'][] = array(
				'lat' => $item->lat,
				'lng' => $item->lnt,
				'title' => $item->landname,
				'content' => '<h4>'.$item->landname.'</h4><p>พื้นที่ : '.$address.'</p>'
			);
		}
		if ($address) $gis['address'][] = $address;
	}

	//$ret .= print_o($gis,'$gis');

	// Section :: Script
	$ret .= '<script type="text/javascript"><!--
		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initProjectMap")})
	';

	if ($isEdit) {
		$ret .= '
	// your code here - init map ...
	function initProjectMap() {
		var gisDigit = 6
		var gis = '.json_encode($gis).'
		var is_point = gis.current ? true : false
		var mapId = "'.$landId.'"

		function clearMap() {
			$(".project-info-latlng").data("value","").find("span").text("")
			is_point = false
			locationUpdate("")
		}

		$("#clear-gis").click(function() {
			$("#edit-data-location").val("")
			//is_point = false
			//locationUpdate("")
		})

		$("#save-gis").click(function() {
			var latLng = $("#edit-data-location").val()
			is_point = latLng != ""
			$(".project-info-latlng").data("value","").find("span").text("")
			locationUpdate($("#edit-data-location").val())
		})


		$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

		if (is_point) notify("ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)
		else if (!is_point) notify("คลิกบนแผนที่ตรงตำแหน่งที่ต้องการวางหมุด / ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)

		var locationUpdate = function(latLng) {
			var para = {}
			var data = {}
			data.landid = mapId
			data.location = latLng
			para.data = data
			$.post("'.url('ibuy/my/info/land.save/'.$landId).'", para, function(data) {
				$("#edit-data-location").val(latLng)
				var mapIcon = latLng != "" ? "where_to_vote" : "room"
				var mapActive = latLng != "" ? "-active" : ""
				$("#ibuy-land-"+mapId+" .icon.-land-map").text(mapIcon)
					.removeClass("-active")
					.addClass(mapActive)
				notify("บันทึกเรียบร้อย"+data)
				//console.log(data)
			});
			/*
			if (latLng) {
				$("#project-info-area-pin-link-"+mapId+">.icon").removeClass("-gray").addClass("-green")
			} else {
				$("#project-info-area-pin-link-"+mapId+">.icon").removeClass("-gray")	.addClass("-green")					
			}
			*/
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

				var latLng = event.latLng.lat().toFixed(gisDigit)+","+event.latLng.lng().toFixed(gisDigit)
				console.log(latLng)

				locationUpdate(latLng)
				$map.addMarker({
					lat: event.latLng.lat(),
					lng: event.latLng.lng(),
					draggable: true,
					dragend: function(event) {
						var latLng = event.latLng.lat().toFixed(gisDigit)+","+event.latLng.lng().toFixed(gisDigit)
						locationUpdate(latLng)
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
				infoWindow: {content: "<h2>"+gis.title+"</h2><p>ตำแหน่งปัจจุบัน ลากหมุดเพื่อเปลี่ยนตำแหน่ง"},
				dragend: function(event) {
					var latLng = event.latLng.lat().toFixed(gisDigit)+","+event.latLng.lng().toFixed(gisDigit)
					locationUpdate(latLng)
				}
			})
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
	}
	';
	} else {
		$ret.='
	// your code here - init map ...
	function initProjectMap() {
		var gis = '.json_encode($gis).'
		var is_point = gis.current ? true : false

		var $map = new GMaps({
			div: "#map_canvas",
			zoom: gis.zoom,
			scrollwheel: true,
			lat: gis.center.lat,
			lng: gis.center.lng,
		})

		$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

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
								icon: "https://softganz.com/library/img/geo/circle-green.png",
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