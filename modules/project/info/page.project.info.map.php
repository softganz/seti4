<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_map($self, $tpid, $mapId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);


	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit;
	$isEditDetail = $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;

	$ret = '';

	$areaName = $projectInfo->info->area;
	$currentLat = $projectInfo->info->lat;
	$currentLnt = $projectInfo->info->lnt;

	if ($mapId) {
		$stmt = 'SELECT m.*
			, AsText(m.`location`) location, X(m.`location`) lat, Y(m.`location`) lnt
			, cot.`subdistname` `tambonName`, coa.`distname` `ampurName`, cop.`provname` `changwatName`
			FROM %project_prov% m
				LEFT JOIN %co_province% cop ON cop.`provid` = m.`changwat`
				LEFT JOIN %co_district% coa ON coa.`distid` = CONCAT(m.`changwat`,m.`ampur`)
				LEFT JOIN %co_subdistrict% cot ON cot.`subdistid` = CONCAT(m.`changwat`,m.`ampur`,m.`tambon`)
			WHERE `tpid` = :tpid AND `autoid` = :autoid
			LIMIT 1';

		$mapInfo = mydb::select($stmt, ':tpid', $tpid, ':autoid', $mapId);
		$areaName = SG\implode_address($mapInfo, 'short');
		$currentLat = $mapInfo->lat;
		$currentLnt = $mapInfo->lnt;
		//$ret .= print_o($mapInfo);
	}

	$ui = new Ui();
	if ($isEdit) {
		//$ui->add('<a class="" href="javascript:void(0)"><i class="icon -material">delete</i></a>');
	}

	$ret .= '<header class="header -box -hidden"><h3>แผนที่ '.$areaName.'</h3><nav class="nav">'.$ui->build().'</nav></header>';
	//$ret.=print_o($projectInfo,'$projectInfo');

	if ($isEdit) {
		$inlineAttr = array();
		$inlineAttr['class'] = 'project-info-map-input sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';

		$ret .= '<div id="project-info-map-text" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret .= '<span class="-hidden">Address : <span id="project-info-map-address">'.$areaName.'</span></span>';
		/*
		$ret .= '<span id="" class="-hidden">Location : <span id="project-info-map-location">'
			. ($projectInfo->info->location ? $currentLat.','.$currentLnt : '')
			. '</span></span>';
			*/

		if ($mapId) {
			$ret .= view::inlineedit(
					array(
						'group' => 'prov',
						'fld' => 'location',
						'tr' => $mapId,
						'label' => 'ละติจูด-ลองจิจูด',
						'ret' => 'point',
						'options' => '{id: "project-info-map-location",class: "project-info-latlng -fill", placeholder: "เช่น 7.0000,100.0000"}',
						'posttext' => '<a id="clear-gis" class="btn -link -sg-16" href="javascript:void(0)" ><i class="icon -material -gray">cancel</i></a>',
					),
					($currentLat ? $currentLat.','.$currentLnt:''),
					$isEdit
				);
		} else {
			$ret .= view::inlineedit(
					array(
						'group' => 'project',
						'fld' => 'location',
						'label' => 'ละติจูด-ลองจิจูด',
						'options' => '{id: "project-info-map-location",class: "project-info-latlng -fill", placeholder: "เช่น 7.0000,100.0000"}',
						'posttext' => '<a id="clear-gis" class="btn -link -sg-16" href="javascript:void(0)" ><i class="icon -material -gray">cancel</i></a>',
					),
					($currentLat ? $currentLat.','.$currentLnt:''),
					$isEdit
				);
		}

		$ret .= '</div>';
		$ret .= '<style type="text/css">
		.project-info-map-input {position: absolute; z-index: 1; top: 10px; left: 270px; background-color: #fff;}
		.project-info-map-input .inline-edit-item {display: flex; flex-wrap: nowrap; white-space: nowrap;}
		#clear-gis {margin-top: 6px;}
		</style>';
	}

	$ret .= '<div id="project-info-map" class="project-info-map" width="100%" height="400">'._NL
		. '<div id="map_canvas"></div>'._NL
		. '</div>'._NL;



	$center = explode(',', SG\getFirst(property('project:map.center:0'),'13.2000,100.0000'));
	$gis['center'] = array('lat'=>$center[0],'lng'=>$center[1]);
	$gis['zoom'] = (int)SG\getFirst(property('project:map.zoom:0'),6);

	if ($areaName && empty($currentLat)) {
		$gis['zoom'] = 12;
	} else if ($currentLat) {
		$gis['center'] = array('lat' => $currentLat, 'lng' => $currentLnt);
		$gis['zoom'] = 13;
		$gis['current'] = array(
			'lat' => $currentLat,
			'lng' => $currentLnt,
			'title' => $projectInfo->title,
			'content' => '<h4>'.$projectInfo->title.'</h4><p>พื้นที่ : '.$areaName.'</p>'
		);
	}

	$gis['title'] = $projectInfo->title;
	$gis['address'] = array();

	//$gis['address'][] = $areaName;
	if ($areaName) $gis['address'][] = substr($areaName,strpos($areaName,'ตำบล'));

	// Get multiple area
	$stmt = 'SELECT
		pv.*
		, cot.`subdistname` `tambonName`, coa.`distname` `ampurName`, cop.`provname` `changwatName`
		, AsText(pv.`location`) location, X(pv.`location`) lat, Y(pv.`location`) lnt
		FROM %project_prov% pv
			LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
			LEFT JOIN %co_district% coa ON coa.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
			LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
		WHERE `tpid` = :tpid';
	$provList = mydb::select($stmt,':tpid',$tpid);


	foreach ($provList->items as $item) {
		$address = SG\implode_address($item, 'short');

		if ($item->autoid != $mapId && $item->location) {
			$gis['all'][] = array(
				'lat' => $item->lat,
				'lng' => $item->lnt,
				'title' => $projectInfo->title.'@'.$address,
				'content' => '<h4>'.$projectInfo->title.'</h4><p>พื้นที่ : '.$address.'</p>'
			);
		}
		if ($item->autoid != $mapId) {
			$gis['address'][] = $address;
		}
		//'ต.'.$item->subdistname.' อ.'.$item->distname.' จ.'.$item->provname;
	}

	//$ret .= print_o($gis,'$gis');

	// Section :: Script
	$ret .= '<script type="text/javascript"><!--
		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initProjectMap")})
	';

	if ($isEdit) {
		$ret .= '
	// your code here - init map ...
	var gisDigit = 6

	function initProjectMap() {
		var gis = '.json_encode($gis).'
		var is_point = gis.current ? true : false
		var mapId = "'.$mapId.'"

		$("#clear-gis").click(function() {
			$(".project-info-latlng").data("value","").find("span").text("")
			projectUpdate("")
		})


		$("#map_canvas").css({minWidth: "100%", minHeight: "100%"})

		if (is_point) notify("ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)
		else if (!is_point) notify("คลิกบนแผนที่ตรงตำแหน่งที่ต้องการวางหมุด / ลากหมุดเพื่อเปลี่ยนตำแหน่ง",20000)

		var projectUpdate = function(latLng) {
			var para = {}
			para.tpid = "'.$tpid.'"
			para.action = "save"
			para.ret = "point"
			para.value = latLng
			para.debug = "inline"

			if (mapId) {
				para.tr = mapId
				para.group = "prov"
				para.fld = "location"
				$.post("'.url('project/edit/tr').'", para, function(data) {
					$("#project-info-map-location>span").text(latLng)
					$("#project-info-map-location").data("value",latLng)
					//notify(data.debug)
				},"json");
				if (latLng) {
					$("#project-info-area-pin-link-"+mapId+">.icon").removeClass("-gray").addClass("-green")
				} else {
					$("#project-info-area-pin-link-"+mapId+">.icon").removeClass("-gray")	.addClass("-green")					
				}
			} else {
				para.group = "project"
				para.fld = "location"
				$.post("'.url('project/edit/tr').'", para, function(data) {
					console.log("data",data)
					$("#project-info-map-location>span").text(latLng)
					$("#project-info-map-location").data("value",latLng)
					$("#project-info-gis>span").text(latLng)
					$("#project-info-gis").data("value",latLng)
				},"json")
			}
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

				projectUpdate(latLng)
				$map.addMarker({
					lat: event.latLng.lat(),
					lng: event.latLng.lng(),
					draggable: true,
					dragend: function(event) {
						var latLng = event.latLng.lat().toFixed(gisDigit)+","+event.latLng.lng().toFixed(gisDigit)
						projectUpdate(latLng)
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
					projectUpdate(latLng)
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