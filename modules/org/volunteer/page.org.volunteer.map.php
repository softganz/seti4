<?php
function org_volunteer_map($self) {
	$isAdmin=user_access('administrator orgs');
	if ($isAdmin) $self->theme->title='แผนที่องค์กร';
	else {
		unset($self->theme->title);
		$ret.='<h2 class="title">แผนที่จิตอาสา</h2>';
	}

	$where=array();
	if ($projectset) $where=sg::add_condition($where,'`projectset`=:projectset ','projectset',$projectset);
	if ($year) $where=sg::add_condition($where,'`pryear`=:year ','year',$year);

	$gis['center']=SG\getFirst($self->property['map.center'],'7.000,100.5000');
	$gis['zoom']=intval(SG\getFirst($self->property['map.zoom'],11));

	$stmt='SELECT o.`orgid`, o.`name`, o.`address`, o.`location`
				FROM %db_org% o
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				ORDER BY `name` ASC;';
	$dbs=mydb::select($stmt,$where['value']);

	$icons['กำลังดำเนินโครงการ']='/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='/library/img/geo/circle-yellow.png';
	foreach ($dbs->items as $rs) {
		if ($rs->location) {
			list($x,$y)=explode(',',$rs->location);
			$icon=$icons[$rs->project_status]?$icons[$rs->project_status]:$icons['กำลังดำเนินโครงการ'];
			$gis['markers'][]=array(
														'latitude'=>$x,
														'longitude'=>$y,
														'title'=>$rs->name,
														'content'=>'<div class="map-info"><img class="logo" src="'.org_model::org_photo($rs->orgid).'" width="48" height="48" /><h4>'.$rs->name.'</h4><p>ที่อยู่ : '.$rs->address.'<br /><a href="'.url('org/'.$rs->orgid).'">ดูรายละเอียด</a></p></div>'
														);
		}
	}

	$ret.='<div class="app-output" style="width:100%;height:600px;">';
	$ret.='กำลังโหลดแผนที่!!!!';
	$ret.='</div>'._NL;

	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var imgSize = new google.maps.Size(16, 16);
		var gis='.json_encode($gis).';
		var is_point=false;
		$map=$(".app-output");
		$map.gmap({
				center: gis.center,
				zoom: gis.zoom,
				scrollwheel: true
			})
			.bind("init", function(event, map) {
				if (gis.markers) {
					$.each( gis.markers, function(i, marker) {
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							draggable: false,
						}).click(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						}).mouseover(function() {
						});
					});
				}
			})
	});
	--></script>';
	return $ret;
}
?>