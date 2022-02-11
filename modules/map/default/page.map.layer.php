<?php
/**
* Get Markers of map layer
*
* @param Object $self
* @param Int $var
* @return String
*/
function map_layer($self) {
	$mapGroup = SG\getFirst(post('gr'), post('mapgroup'));
	$layer = SG\getFirst(post('ly'), post('layer'));
	if (empty($mapGroup)) {
		$ret['html'] .= '<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)"<i class="icon -close"></i> data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
		$ret['html'] .= '<h3>เลือกกลุ่มแผนที่</h3>';
		$dbs=mydb::select('SELECT * FROM %map_name% ORDER BY `mapname` ASC');
		$ret['html'] .= '<ul>';
		foreach ($dbs->items as $value) {
			$ret['html'] .= '<li><a href="'.url('map/'.$value->mapgroup).'">'.$value->mapname.'</a></li>';
		}
		$ret['html'] .= '</ul>';
			return $ret['html'];
	} else if (empty($layer)) {
		$ret['html'] .= '<nav class="nav iconset -sg-text-right">';
		$ret['html'] .= '<a class="btn" href="#" data-action="clear-map">ล้างแผนที่</a>';
		$ret['html'] .= '<a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a> ';
		$ret['html'] .= '</nav>';
		$ret['html'] .= '<h3>เลือกชั้นแผนที่</h3>';
		$dbs = mydb::select('SELECT DISTINCT `dowhat` FROM %map_networks% WHERE `mapgroup`=:mapgroup AND `dowhat`!="" ORDER BY CONVERT (`dowhat` USING tis620) ASC',':mapgroup',$mapGroup);
		$tags = array();
		foreach (explode(',',$dbs->lists->text) as $value) {
			$value = trim($value);
			if (!array_key_exists($tags, $value)) {
				$tags[$value]='<a href="'.url('map/layer',array('gr'=>$mapGroup,'layer'=>$value)).'" data-action="load-layer">'.$value.'</a>';
			}
		}
		ksort($tags);
		$ret['html'] .= '<ul><li>'.implode('</li><li>', $tags).'</li></ul>';
		return $ret['html'];
	}

	$stmt = 'SELECT * FROM %';

	//if ($mapGroup != 'All')
	mydb::where('`mapgroup` IN (:mapgroup) ', ':mapgroup', $mapGroup);
	if ($layer && $layer != 'All') mydb::where('`dowhat` LIKE :layer', ':layer', '%'.$layer.'%');
	if (!user_access('access full maps')) mydb::where('`privacy` = "public"');
	mydb::where(NULL, ':uid',i()->uid);

	$stmt = 'SELECT
					  m.*
					, u.`name`
					, n.`orgid`
					, CONCAT(X(`latlng`),",",Y(`latlng`)) `latlng`
					, X(`latlng`) `lat`
					, Y(`latlng`) `lnt`
					, o.`membership`
				FROM %map_networks% m
					LEFT JOIN %users% u USING (uid)
					LEFT JOIN %map_name% n USING(`mapgroup`)
					LEFT JOIN %org_officer% o ON o.`orgid` = n.`orgid` AND o.`uid` = :uid
				%WHERE%
				ORDER BY `mapid` DESC
				;';
	//print_o(mydb(),'mydb()',1);
	$dbs = mydb::select($stmt);
	//echo $mapGroup.$layer.mydb()->_query;




	$ret['markers'] = array();
	foreach ($dbs->items as $rs) {
		if ($rs->latlng && $who = R::Model('map.who.get',$rs)) {
			$ret['markers'][$rs->mapid] = $who;
			//$ret[$rs->mapid]=$who;
		}
	}
	//debugMsg($dbs);
	//print_o($ret,'$ret',1);
	//return sg_json_encode($ret);
	return $ret;
}
?>