<?php
/**
* iMed :: Disabled Map By Defect
* Created 2019-02-19
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage imed/map/defect
*/

$debug = true;

function imed_map_defect($self) {
	$isAdmin = is_admin('imed');

	$zones = imed_model::get_user_zone(i()->uid,'imed');

	$icons = array(
		'ไม่ระบุ' => '',
		'ทางการเห็น' =>' /library/img/geo/violet.png',
		'ทางการได้ยินหรือสื่อความหมาย' => '/library/img/geo/green.png',
		'ทางการเคลื่อนไหวหรือทางร่างกาย' => '/library/img/geo/point_blue_blank.png',
		'ทางจิตใจ' => '/library/img/geo/point_red_blank.png',
		'ทางสติปัญญา' => '/library/img/geo/point_orange_blank.png',
		'ทางการเรียนรู้' => '/library/img/geo/yellow.png',
		'ออทิสติก' => '/library/img/geo/brown.png',
	);


	$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
	$ret.='<h3>แผนที่ประเภทคนพิการ</h3>';
	$ret.='</form>';
	


	// Data Model
	mydb::where('g.`gis` IS NOT NULL');

	if ($isAdmin) {

	} else  if ($zones) {
		mydb::where('('.'p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
	} else {
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}

	$stmt='SELECT
		  g.`gis`, df.`pid`, CONCAT(p.`name`," ",p.`lname`) name
		, `defect`+0 defecttype, `defect`
		, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng
		, X(g.`latlng`) lat, Y(g.`latlng`) lng
		FROM %imed_disabled_defect% df
			LEFT JOIN %db_person% p ON p.`psnid` = df.`pid`
			LEFT JOIN %gis% g ON g.`gis` = p.`gis`
		%WHERE%
		';

	$dbs = mydb::select($stmt);

	$map = array(
		'center' => array('lat' => 7.011666, 'lng' => 100.470088),
		'zoom' => 10,
		'height' => '600px',
		'markers' => array(),
	);

	foreach ($dbs->items as $rs) {
		$map['markers'][]=array(
			'lat' => $rs->lat,
			'lng' => $rs->lng,
			'icon' => $icons[$rs->defect],
			'content' => '<h5><a href="'.url('imed',['pid'=>$rs->pid]).'" target="_blank">'.$rs->name.'</a></h5>'
				. '<hr />'
				. '<p>'.SG\getFirst($rs->defect,'ไม่ระบุ').'</p>',
		);
	}


	// View Model

	$ret .= '<div id="imed-map-defect" class="page -map">'._NL
		. '<div id="map-canvas" class="map-canvas"></div>'._NL
		. '</div>'._NL;

	foreach ($icons as $k => $icon) {
		$ret .= '<img src="'.$icon.'" /> = '.$k.' ';
	}

	// Section :: Script
	$ret .= '<script type="text/javascript">
		function onWebViewComplete() {
			var options = {refresh: false}
			return options
		}

		$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})

		var landMap

		function initMap() {
			landMap = new sgDrawMap("landMap",'.json_encode($map).');
		}
	</script>';

	return $ret;
}

?>