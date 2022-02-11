<?php
function r_imed_zone_get($uid,$module=NULL,$refid=NULL,$options='{}') {
	$defaults='{debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	$zones=array();

	if (!mydb::table_exists('db_userzone')) return $zones;

	mydb::where('`uid`=:uid',':uid',$uid);
	if ($module) mydb::where('`module`=:module',':module',$module);
	if ($refid) mydb::where('`refid`=:refid',':refid',$refid);
	$stmt='SELECT z.`zone`, z.`module`, z.`refid`, z.`right`, s.`subdistname`, cod.`distname`, cop.`provname` FROM %db_userzone% z
					LEFT JOIN %co_subdistrict% s ON s.`subdistid`=z.`zone`
					LEFT JOIN %co_district% cod ON cod.`distid`=SUBSTR(z.`zone`,1,4)
					LEFT JOIN %co_province% cop ON cop.`provid`=SUBSTR(z.`zone`,1,2)
				%WHERE%
				ORDER BY z.`zone` ASC';
	$dbs=mydb::select($stmt);

	// Return all zone
	if (empty($module) && empty($refid)) {
		foreach ($dbs->items as $rs) $zones[]=$rs;
	} else if ($module) {
		foreach ($dbs->items as $rs) $zones[$rs->zone]=$rs;
	} else {
		foreach ($dbs->items as $rs) $zones[$rs->zone]=$rs;
	}
	return $zones;
}
?>