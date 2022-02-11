<?php
function map_list() {
	$mapGroup=SG\getFirst($_REQUEST['gr'],$_REQUEST['mapgroup']);

	$ret='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';

	if ($_REQUEST['o']=='new') $order='`mapid` DESC';
	else $order='CONVERT (`who` USING tis620) ASC';

	$stmt='SELECT m.*,
								CONCAT(X(`latlng`),",",Y(`latlng`)) latlng, X(`latlng`) lat, Y(`latlng`) lnt
				FROM %map_networks% m
				WHERE `mapgroup` IN (:mapgroup)
				ORDER BY '.$order;

	$dbs=mydb::select($stmt,':mapgroup', $mapGroup);

	$tables = new Table();
	$icons['prepare']='/library/img/geo/prepare.png';
	$icons['during']='/library/img/geo/during.png';
	$icons['after']='/library/img/geo/after.png';
	$icons['place']='/library/img/geo/risk-1-1.png';
	$icons['หมู่บ้าน']='/library/img/geo/risk-2.png';
	$icons['cctv']='/library/img/geo/webcam.jpg';
	$icons['none']='/library/img/geo/none.png';


	foreach ($dbs->items as $rs) {
		$isEdit = user_access('administer maps','edit own maps content',$rs->uid);
		if ($rs->latlng && $rs->mapid!=$rs->mapid) {
			if (preg_match('/CCTV/',$rs->dowhat)) $icon=$icons['cctv'];
			else if (preg_match('/เครือข่ายเตือนภัย/',$rs->dowhat)) $icon=$icons['เครือข่ายเตือนภัย'];
			else if (preg_match('/หมู่บ้าน/',$rs->dowhat)) $icon=$icons['หมู่บ้าน'];
			else if (preg_match('/สถานที่/',$rs->dowhat)) $icon=$icons['place'];
			else if ($rs->prepare) $icon=$icons['prepare'];
			else if ($rs->during) $icon=$icons['during'];
			else if ($rs->after) $icon=$icons['after'];
			else $icon=$icons['none'];
			unset($where);
			if ($rs->prepare) $where[]='ก่อนเกิดเหตุ';
			if ($rs->during) $where[]='ระหว่างเกิดเหตุ';
			if ($rs->after) $where[]='หลังเกิดเหตุ';

			$gis['markers'][]=array('lat'=>$rs->lat,
				'lng'=>$rs->lnt,
				'title'=>$rs->who,
				'icon'=>$icon,
				'content'=>'<h4>'.$rs->who.'</h4><p><a class="sg-action" data-rel="#main" href="'.url('map','id='.$rs->mapid).'">แก้ไข</a></p><p>ทำอะไร : '.$rs->dowhat.'<br />เมื่อไหร่ : '.implode(',',$where).'<br />ที่ไหน : '.$rs->address.'</p>'
			);
		}

		$menu = '';
		if ($isEdit) {
			$menu .= '<nav class="nav iconset -hover">'
				. '<a class="sg-action" data-rel="#Emap-box" href="'.url('map/add',array('id'=>$rs->mapid)).'"><i class="icon -edit"></i></a>'
				. '</nav>';
		}
		$info = '<a href="'.url('map','id='.$rs->mapid).'" data-action="showmap" data-id="'.$rs->mapid.'" data-who="'.$rs->who.'">'.$rs->who.($rs->latlng ? '<i class="icon -pin-drop -gray"></i>' : '').'</a>'
					. '<p>'.$rs->dowhat.'</p>'
					. $menu;

		$tables->rows[]=array($info);
		$items[]=$info;
	}

	// $ret .= $tables->build();

	$ret.='<ul class="map-list"><li class="-hover-parent">'.implode('</li><li class="-hover-parent">',$items).'</li></ul>';
	return $ret;
}
?>