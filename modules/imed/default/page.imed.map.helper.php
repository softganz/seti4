<?php
/**
* iMed :: Disabled Map By Helper
* Created 2019-02-15
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage imed/map/helper
*/

$debug = true;

function imed_map_helper($self) {
	// Data Model
	$isAdmin = is_admin('imed');

	$zones = imed_model::get_user_zone(i()->uid,'imed');

	$icons = array(
		'ไม่ระบุ' => '/library/img/geo/violet.png',
		'มีผู้ดูแล' => '/library/img/geo/green.png',
		'มีผู้ช่วยคนพิการ' => '/library/img/geo/point_blue_blank.png',
		'ไม่มีผู้ดูแลหรือถูกทอดทิ้ง' => '/library/img/geo/point_red_blank.png'
	);

	mydb::where('g.`gis` IS NOT NULL');

	if ($isAdmin) {

	} else  if ($zones) {
		mydb::where('(p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
	} else {
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}

	$stmt = 'SELECT
		  g.`gis`, d.`pid`, CONCAT(p.`name`," ",p.`lname`) `name`
		, `helper`+0 helpertype, `helper`
		, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) `latlng`
		, X(g.`latlng`) lat, Y(g.`latlng`) `lnt`
		FROM %imed_disabled% d
			LEFT JOIN %db_person% p ON p.`psnid` = d.`pid`
			LEFT JOIN %gis% g ON g.`gis` = p.`gis`
		%WHERE%';

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
			'lng' => $rs->lnt,
			'icon' => $icons[SG\getFirst($rs->helper,'ไม่ระบุ')],
			//'helpertype'=>intval($rs->helpertype?$rs->helpertype:0),
			'content'=>'<h3><a href="'.url('imed', ['pid' => $rs->pid]).'" target="_blank">'.$rs->name.'</a></h3><hr /><p>'.SG\getFirst($rs->helper,'ไม่ระบุ').'</p>',
			);
	}

	// View Model
	$ret .= '<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
	$ret.='<h3>แผนที่ผู้ดูแลคนพิการ</h3>';
	$ret.='</form>';

	// View Model
	$ret .= '<div id="imed-map-helper" class="page -map">'._NL
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