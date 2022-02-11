<?php
/**
* iMed :: Disabled Map By Patient Group
* Created 2018-10-13
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage imed/map/defect
*/

$debug = true;

function imed_map_patient($self) {
	$prov=SG\getFirst(post('p'),90);

	$isAdmin = is_admin('imed');

	$zones = imed_model::get_user_zone(i()->uid,'imed');

	// Show Form
	if (!post('f')) {
		$ret.='<form method="get" action="'.url('imed/map/patient').'" class="report-form sg-form" data-rel="replace:#report-output"><input type="hidden" name="f" value="n" />';
		$ret.='<h3>แผนที่ภาพรวม</h3>';
		$ret.='<div class="form-item">'._NL;
		$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="xprov" class="form-select" onChange="$(this).closest(\'form\').submit()">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) {
			$ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
			if ($prov==$rs->provid) $provname=$rs->provname;
		}
		$ret.='</select>'._NL;
		/*
		if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
			$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		}
		*/
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>'._NL;
		/*
		$ret.='<div class="optionbar"><ul>';
		$ret.='</ul></div>';
		*/
		$ret.='</form>';
	}



	// Data Model
	mydb::where('p.`changwat` = :prov',':prov',$prov);
	if ($isAdmin) {
		// Get sll patient
	} else if ($zones) {
		// Get only user or in zone
		mydb::where('('.'p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
	} else {
		// Get olny user
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}



	$stmt = 'SELECT
		  1 `careid`, g.`gis`, d.`pid`
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `name`
		, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) `latlng`
		, X(g.`latlng`) lat, Y(g.`latlng`) lnt
		, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
		, IFNULL(cosub.`subdistname`,p.`t_tambon`) `subdistname`
		, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
		, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
		FROM %imed_disabled% d
			LEFT JOIN %db_person% p ON p.`psnid` = d.`pid`
			LEFT JOIN %gis% g ON g.`gis`=p.`gis`
			LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
			LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			LEFT JOIN %co_village% covi ON covi.`villid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`) = 1,CONCAT("0",p.`village`),p.`village`))
		%WHERE% AND g.`gis` IS NOT NULL
		UNION
		SELECT
		  c.`careid` `careid`, g.`gis`, c.`pid`
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `name`
		, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) `latlng`
		, X(g.`latlng`) lat, Y(g.`latlng`) lnt
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
		%WHERE% AND c.`careid` = 2 AND g.`gis` IS NOT NULL
		ORDER BY `careid` DESC
		';

	$dbs = mydb::select($stmt);

	//debugMsg($dbs, '$dbs');
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	$provname = mydb::select('SELECT `provname` FROM %co_province% WHERE `provid` = :prov LIMIT 1',':prov',$prov)->provname;

	$cares = array(0 => 'ไม่ระบุ', 1 => 'คนพิการ', 2 => 'ผู้สูงอายุ');
	$icons = array(
		0 => '/library/img/geo/pin-none.png',
		1 => '/library/img/geo/pin-disabled.png',
		2 => '/library/img/geo/pin-man.png',
	);


	$map = array(
		'center' => array('lat' => 7.011666, 'lng' => 100.470088),
		'zoom' => 10,
		'height' => '600px',
		'markers' => array(),
		//'address' => $provname,
	);

	foreach ($dbs->items as $rs) {
		if (!($rs->latlng)) continue;
		$content = '<div class="imed-partiant-info">'
			. '<img class="imed-patient-photo" src="'.imed_model::patient_photo($rs->pid).'" height="64" alt="" />'
			. '<h5>'
			. '<a href="'.url('imed', ['pid' => $rs->pid]).'" target="_blank">'.$rs->prename.' '.$rs->name.'</a>'
			. '</h5>'
			. '<hr />'
			. '<p>การดูแล : '.$cares[$rs->careid].'<br />ที่อยู่ : '.SG\implode_address($rs,'short').'</p>'
			. '</div>';

		$map['markers'][] = array(
			'lat' => $rs->lat,
			'lng' => $rs->lnt,
			'icon' => $icons[$rs->careid], // => intval($rs->careid),
			'content' => $content,
		);
	}


	// View Model
	$ret .= '<div id="report-output" class="page -map">';
	$ret .= '<div id="map-canvas" class="map-canvas"></div>';
	foreach ($icons as $k=>$icon) $ret.='<img src="'.$icon.'" /> = '.$cares[$k].' ';

	//debugMsg($map,'$map');


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

	$ret .= '</div><!-- report-output -->';

	return $ret;
}
?>