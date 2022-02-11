<?php
function map_new($self) {
	$mapGroup=intval(SG\getFirst($_REQUEST['gr'],$_REQUEST['mapgroup']));
	$ret['refreshTime']=$refreshtime=intval($_REQUEST['t']);
	$ret['time']=date('U');
	$ret['totals']=mydb::select('SELECT COUNT(*) totals FROM %map_networks% LIMIT 1')->totals;
	$ret['hits']=cfg_db('crowdsourcing.map.mapping.hits');

	mydb::where('`mapgroup` IN (:mapgroup) ',':mapgroup',$mapGroup);
	if (!user_access('access full maps')) mydb::where('`privacy` = "public"');
	mydb::where('(`created` > :refreshtime OR `modified` > :refreshtime)',':refreshtime',$refreshtime);

	$stmt='SELECT m.*, u.`name`,
								 CONCAT(X(`latlng`),",",Y(`latlng`)) latlng, X(`latlng`) lat, Y(`latlng`) lnt
				FROM %map_networks% m
					LEFT JOIN %users% u USING (uid)
				%WHERE%
				ORDER BY `mapid` DESC;';

	$dbs=mydb::select($stmt);

	if (debug('method')) $ret['html'].=print_o($dbs,'$dbs');

	$ret['count']=$dbs->_num_rows;
	$ret['markers']=array();
	foreach ($dbs->items as $rs) {
		if ($rs->latlng && $who = R::Model('map.who.get',$rs)) $ret['markers'][$rs->mapid]=$who;
	}

	return $ret;
}
?>