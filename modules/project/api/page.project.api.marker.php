<?php
/**
* Get Markers of map layer
*
* @param Object $self
* @param Int $var
* @return String
*/
function project_api_marker($self) {
	$ret->status = true;

	/*
	if ($mapGroup != 'All')
		mydb::where('`mapgroup` IN (:mapgroup) ', ':mapgroup', $mapGroup);
	if ($layer && $layer != "All" )
		mydb::where('`dowhat` LIKE :layer', ':layer', '%'.$layer.'%');
	if (!user_access('access full maps'))
		mydb::where('`privacy` = "public"');
	*/

	if (post('s')) mydb::where('o.`sector` = :sector', ':sector', post('s'));

	$stmt='SELECT
					o.`orgid`
					, o.`name`
				--	, X(p.location) lat, Y(p.location) lng, p.project_status, p.project_status+0 project_statuscode,
					, cos.`lat`
					, cos.`lng`
					, cop.`provname` `changwatName`
					, o.`changwat`, o.`ampur`, o.`tambon`
					, (SELECT COUNT(*) FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype` = "โครงการ" AND t.`orgid` = o.`orgid`) `projectCount`
					, (SELECT COUNT(*) FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype` = "แผนงาน" AND t.`orgid` = o.`orgid`) `planningCount`
					, (SELECT COUNT(*) FROM %project_dev% p LEFT JOIN %topic% t USING(`tpid`) WHERE t.`orgid` = o.`orgid`) `developCount`
					FROM %db_org% o
						LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = CONCAT(o.`changwat`, o.`ampur`, o.`tambon`)
						LEFT JOIN %co_province% cop ON cop.`provid` = o.`changwat`
					%WHERE%
					ORDER BY CONVERT(`name` USING tis620) ASC
					';
	$dbs=mydb::select($stmt);
	//$ret->query = mydb()->_query;



	$ret->markers = array();
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			$offset1 = rand(0,1000)/10000000;
			$offset2 = rand(0, 1000)/10000000;
			$rs->lat = (double) $rs->lat + $offset1;
			$rs->lng = (double) $rs->lng + $offset2;
			$ret->markers[] = $rs;
			//$ret[$rs->mapid]=$who;
		}
	}
	//print_o($ret,'$ret',1);
	//return sg_json_encode($ret);
	return $ret;
}
?>