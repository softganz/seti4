<?php
/**
* iMed :: Elder Map
* Created 2018-10-13
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage imed/map/elder
*/

$debug = true;

function imed_map_elder($self) {
	// Data Model
	$isAdmin = is_admin('imed');
	$zones = imed_model::get_user_zone(i()->uid,'imed');
	$cares = array(0 => 'ไม่ระบุ', 1 => 'ติดเตียง', 2 => 'ติดบ้าน', 3 => 'ติดสังคม');
	$icons = array(
		'ไม่ระบุ' => '/library/img/geo/elder-normal.png',
		'ติดเตียง' => '/library/img/geo/elder-nursery.png',
		'ติดบ้าน' => '/library/img/geo/elder-home.png',
		'ติดสังคม' => '/library/img/geo/elder-social.png',
	);

	mydb::where('c.`careid` = 2 AND g.`gis` IS NOT NULL');

	if ($isAdmin) {

	} else  if ($zones) {
		mydb::where('('.'p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
	} else {
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}

	$stmt = 'SELECT
			c.`careid` `careid`, g.`gis`, c.`pid`
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `name`
		, p.`adl`
		, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) `latlng`
		, X(g.`latlng`) `lat`, Y(g.`latlng`) `lnt`
		, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
		, IFNULL(cosub.`subdistname`,p.`t_tambon`) `subdistname`
		, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
		, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
		FROM %imed_care% c
			LEFT JOIN %db_person% p ON p.`psnid` = c.`pid`
			LEFT JOIN %gis% g ON g.`gis` = p.`gis`
			LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
			LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			LEFT JOIN %co_village% covi ON covi.`villid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`) = 1,CONCAT("0",p.`village`),p.`village`))
		%WHERE%
		GROUP BY `pid`
		ORDER BY `careid` DESC
		';

	$dbs = mydb::select($stmt);

	$map = array(
		'center' => array('lat' => 7.011666, 'lng' => 100.470088),
		'zoom' => 10,
		'height' => '600px',
		'markers' => array(),
		'debug' => true,
	);


	foreach ($dbs->items as $rs) {
		if (!$rs->elderGroup) $rs->elderGroup = 0;
		$barthel = R::Model('imed.barthel.level', $rs->adl);
		$map['markers'][] = array(
			'lat' => $rs->lat,
			'lng' => $rs->lnt,
			'icon' => $icons[$barthel->text],
			'content' => '<img class="imed-patient-photo" src="'.imed_model::patient_photo($rs->pid).'" height="64" alt="" />'
				. '<h5><a href="'.url('imed', ['pid' => $rs->pid]).'" target="_blank">'.$rs->prename.' '.$rs->name.'</a></h5>'
				. '<hr />'
				. '<p>กลุ่มผู้สูงอายุ : '.$barthel->lebel.$barthel->text
				. (is_null($rs->adl) ? '' : ' (ADL='.$rs->adl.')')
				. '<br />ที่อยู่ : '.SG\implode_address($rs,'short').'</p>',
		);
	}



	// View Model

	$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
	$ret.='<h3>แผนที่ผู้สูงอายุ</h3>';
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