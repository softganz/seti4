<?php
/**
* Saveup :: Report Member Map
* Created 2018-05-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/havetreat
*/

$debug = true;

function saveup_report_map($self) {
	$self->theme->title='แผนที่สมาชิก';
	$ret.='<p><a href="'.url('saveup/report').'">รายงาน</a> / <a href="'.url('saveup/report/map').'">แผนที่สมาชิก</a></p>';

	$stmt = 'SELECT `caddress`, `camphure`, `cprovince`, `czip`,
			g.gis, m.mid, CONCAT(m.firstname," ",m.lastname) name, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt
		FROM %saveup_member% m
			LEFT JOIN %gis% g ON g.gis=m.gis
		WHERE m.gis IS NOT NULL';

	$dbs = mydb::select($stmt);

	$gis['center']='7.011666,100.470088';
	$gis['zoom']=10;

	foreach ($dbs->items as $rs) {
		$gis['markers'][]=array(
			'latitude'=>$rs->lat,
			'longitude'=>$rs->lnt,
			'content'=>'<a href="'.url('saveup/member/view/'.$rs->mid).'" title="ดูรายละเอียด"><img src="'.saveup_model::member_photo($rs->mid).'" width="64" height="64" class="saveup-profile-map" /></a><h3>'.$rs->name.'</h3><hr /><p>'.$rs->caddress.' อำเภอ'.$rs->camphure.' จังหวัด'.$rs->cprovince.' '.$rs->czip.'</p>',
		);
	}
	$ret.='<div id="map_canvas" style="width:100%;height:800px;"></div>';
	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var data='.json_encode($gis).';
$("#map_canvas")
	.gmap({
		center: data.center,
		zoom: data.zoom,
		scrollwheel: false
	})
	.bind("init", function(event, map) {
		if (data.markers) {
			$.each( data.markers, function(i, marker) {
				$("#map_canvas").gmap("addMarker", {
					position: new google.maps.LatLng(marker.latitude, marker.longitude),
				}).click(function() {
					$("#map_canvas").gmap("openInfoWindow", { "content": marker.content }, this);
				});
			});
		}
	});
});
--></script>';
//		$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>