<?php
/**
* Project owner
*
* @param Object $self
* @return String
*/
function project_map_pin($self) {
	$projectset = SG\getFirst(post('set'));
	$year = post('year');
	$property = property('project');

	if ($projectset) R::Module('project.template', $self, $projectset);

	R::View('project.toolbar', $self, 'แผนที่ติดตามโครงการ', 'map', (Object)array('set'=>$projectset));

	$ui=new Ui();

	//mydb::where('p.`prtype` = "โครงการ"');
	if ($projectset)
		mydb::where('(p.`projectset` = :projectset) ', ':projectset', $projectset);

	$stmt='SELECT *, COUNT(*) `amt` FROM
					(SELECT p.`pryear`, p.`tpid`, p.`projectset`
					FROM %project% p
					%WHERE%';

	if ($projectset) {
		$stmt .= '
					UNION
					SELECT `pryear`, `tpid`, `projectset` FROM %project% p WHERE `projectset` IN (SELECT `tpid` FROM %project% WHERE `projectset` = :projectset)';
	}
	$stmt .='
					) a
					GROUP BY `pryear`
					ORDER BY `pryear` DESC;
					-- {reset: false}';

	$yearlist = mydb::select($stmt);
	//$ret .= '<pre>'.mydb()->_query.'</pre>';
	//$ret .= print_o($yearlist);

	foreach ($yearlist->items as $rs) {
		$ypara = array();
		if ($projectset) $ypara['set'] = $projectset;
		$ypara['year'] = $rs->pryear;
		$ui->add('<a class="btn" href="'.url('project/map/pin',$ypara).'">ปี '.($rs->pryear + 543).'</a>');
	}
	$ret .= '<nav class="nav -page">'.$ui->build('ul').'</nav>';


	if ($year && $year != '*')
		mydb::where('(p.`pryear`=:year)',':year',$year);

	$stmt='SELECT *, COUNT(*) `amt` FROM
					(SELECT p.`tpid`, p.`changwat`, `provname`
					FROM %project% p
						LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
					%WHERE%';

	if ($projectset) {
		$stmt .= '
					UNION
					SELECT p.`tpid`, `changwat`, `provname`
					FROM %project% p
						LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
					WHERE `projectset` IN (SELECT `tpid` FROM %project% WHERE `projectset` = :projectset)'
					.($year && $year != '*' ? ' AND `pryear` = :year' : '');
	}
	$stmt .='
					) a
					GROUP BY provname;
					-- {reset: false}';
	$dbs = mydb::select($stmt);
	//$ret .= '<pre>'.mydb()->_query.'</pre>';

	if ($year)
		$self->theme->title .= ' ปี '.($year+543);


	$ret .= '<ul class="project-list">'._NL;
	foreach ($dbs->items as $rs) {
		$ret .= '<li><a href="'.url('project/list',array('province'=>$rs->changwat,'set'=>$projectset,'year'=>$year)).'">'.SG\getFirst($rs->provname,'ไม่ระบุ').'</a><p>'.$rs->amt.' โครงการ</p></li>'._NL;
	}
	$ret .= '</ul>'._NL;


	$stmt='SELECT
					  p.`tpid`, p.`project_status`, t.`title`, p.`area`
					, CONCAT(X(p.`location`),",",Y(p.`location`)) `latlng`
					, X(p.`location`) `lat`, Y(p.`location`) `lnt`
					FROM %project% p
						LEFT JOIN %topic% t USING (`tpid`)
					%WHERE%';

	if ($projectset) {
		mydb::where(NULL,':projectset',$projectset);
		$stmt .='
					UNION
					SELECT p.`tpid`, p.`project_status`, t.`title`, p.`area`
					, CONCAT(X(p.`location`),",",Y(p.`location`)) `latlng`
					, X(p.`location`) `lat`, Y(p.`location`) `lnt`
					FROM %project% p
						LEFT JOIN %topic% t USING (`tpid`)
					WHERE `projectset` IN (SELECT `tpid` FROM %project% WHERE `projectset` = :projectset)'
					.($year && $year != '*' ? ' AND `pryear` = :year' : '');
	}

	$stmt .='
					ORDER BY `project_status` ASC, `tpid` ASC
					LIMIT 15000;';
	$dbs=mydb::select($stmt);
	//$ret .= '<pre>'.mydb()->_query.'</pre>';
	//$ret .= print_o($dbs,'$dbs');


	$center = explode(',', SG\getFirst(property('project:map.center:0'),'13.2000,100.0000'));
	$gis['center'] = array('lat'=>$center[0],'lng'=>$center[1]);
	$gis['zoom'] = (int) SG\getFirst(property('project:map.zoom:0'),6);

	$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';
	foreach ($dbs->items as $rs) {
		if ($rs->latlng) {
			$icon=$icons[$rs->project_status]?$icons[$rs->project_status]:$icons['กำลังดำเนินโครงการ'];
			$gis['markers'][]=array('lat'=>$rs->lat,
														'lng'=>$rs->lnt,
														'title'=>$rs->title,
														'icon'=>$icon,
														'content'=>'<div class="project-map-info"><h4>'.$rs->title.'</h4><p>พื้นที่ : '.$rs->area.'<br />สถานภาพ : '.$rs->project_status.'<br /><a class="btn -link" href="'.url('paper/'.$rs->tpid).'" target="_blank"><i class="icon -view"></i><span>รายละเอียด</span></a></p></div>'
														);
		}
	}


	$ret.='<div id="project-info-map" class="project-info-map" width="100%" height="400">'._NL
			.'<div id="map_canvas"></div>'._NL
			.'</div>'._NL;

	$ret.='<div class="app-footer"><img src="'.$icons['กำลังดำเนินโครงการ'].'" /> กำลังดำเนินโครงการ <img src="'.$icons['ดำเนินการเสร็จสิ้น'].'" /> ดำเนินการเสร็จสิ้น <img src="'.$icons['ยุติโครงการ'].'" /> ยุติโครงการ <img src="'.$icons['ระงับโครงการ'].'" />ระงับโครงการ</div>';

	head('gmap.js','<script type="text/javascript" src="/js/gmaps.js"></script>');

	$ret.='<script type="text/javascript"><!--
	// your code here - init map ...
	loadGoogleMaps("initProjectMap")

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


		if (gis.markers) {
			$.each( gis.markers, function(i, marker) {
				//console.log(marker)
				$map.addMarker({
					lat: marker.lat,
					lng: marker.lng,
					icon : marker.icon,
					infoWindow: {
						content: marker.content,
						closeclick: function(e) {}
					},
					click: function(e) {stillOpen = true},
					mouseover: function(e){
						$map.hideInfoWindows()
						this.infoWindow.open(this.map, this)
					},
					mouseout: function(e){
					}

				})
			})
		}
	}

		/*
	$(document).ready(function() {
		var imgSize = new google.maps.Size(16, 16);
		var gis='.json_encode($gis).';
		var is_point=false;
		$map=$(".app-output");
		$map.gmap({
				center: gis.center,
				zoom: gis.zoom,
				scrollwheel: false
			})
			.bind("init", function(event, map) {
				if (gis.markers) {
					$.each( gis.markers, function(i, marker) {
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
							draggable: false,
						}).click(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						}).mouseover(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						});
					});
				}
			})
	});
	*/
	--></script>';
	return $ret;
}
?>