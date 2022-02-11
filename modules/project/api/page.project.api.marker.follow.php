<?php
/**
* Get Markers of map layer
*
* @param Object $self
* @param Int $var
* @return String
*/
function project_api_marker_follow($self) {
	$getSearch = post('q');
	$getType = SG\getFirst(post('type'), 'โครงการ');

	header('Access-Control-Allow-Origin: *');
	//$headerResult = http_response_code(200);

	$result->status = true;
	$result->count = 0;
	/*
	if ($mapGroup != 'All')
		mydb::where('`mapgroup` IN (:mapgroup) ', ':mapgroup', $mapGroup);
	if ($layer && $layer != "All" )
		mydb::where('`dowhat` LIKE :layer', ':layer', '%'.$layer.'%');
	if (!user_access('access full maps'))
		mydb::where('`privacy` = "public"');
	*/
	mydb::where('p.`prtype` = :type', ':type', $getType);
	mydb::where('p.`location` IS NOT NULL');
	if (post('set')) mydb::where('t.`parent` = :set', ':set', post('set'));
	if (post('yy')) mydb::where('p.`pryear` = :pryear', ':pryear', post('yy'));
	if (post('s')) mydb::where('o.`sector` = :sector', ':sector', post('s'));
	if (post('p')) mydb::where('pl.`refid` = :supportplan', ':supportplan', post('p'));
	if (post('inno')) mydb::where('inno.`fldname` = :innoid', ':innoid', post('inno'));
	if (post('issue')) mydb::where('issue.`fldname` = :issueid', ':issueid', post('issue'));
	if (post('rate')) mydb::where('t.`rating` >= :rating', ':rating', post('rate'));
	if (post('like')) mydb::where('t.`liketimes` > 0');
	if (post('approve')) mydb::where('t.`approve` = :approve', ':approve', post('approve'));

	if ($getSearch) {
		$getSearch=preg_replace('/\s+/', ' ', $getSearch);
		if (preg_match('/^code:(\w.*)/',$getSearch,$out)) {
			mydb::where('t.`tpid` = :q',':q',$out[1]);
		} else {
			$searchExplode=explode('+',$getSearch);
			$searchList=array();
			foreach ($searchExplode as $key=>$str) {
				$str=trim($str);
				if ($str=='') continue;
				$searchList[]='(t.title RLIKE :q'.$key.')';

				//$str=mysqli_real_escape_string($str);
				$str=preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
				$str=preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

				// this comment for correct sublimetext syntax highlight
				// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

				mydb::where(NULL,':q'.$key,str_replace(' ', '|', $str));
			}
			if ($searchList) mydb::where('('.(is_numeric($getSearch)?'t.`tpid` = :q OR ':'').implode(' AND ', $searchList).')',':q',$getSearch);
		}
	}

	$stmt = 'SELECT
		  p.`tpid`, t.`title`
		, t.`approve`
		, o.`name`
		, X(p.`location`) `lat`, Y(p.`location`) `lng`
		, p.`project_status`, p.`project_status` + 0 `project_statuscode`
		, GROUP_CONCAT(pl.`refid`) `supportplan`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_tr% pl ON pl.`tpid` = p.`tpid` AND pl.`formid` = "info" AND pl.`part` = "supportplan"
			LEFT JOIN %bigdata% issue ON issue.`keyname` = "project.info" AND issue.`keyid` = p.`tpid` AND issue.`fldname` LIKE "category-%"
			LEFT JOIN %bigdata% inno ON inno.`keyname` = "project.info" AND inno.`keyid` = p.`tpid` AND issue.`fldname` LIKE "inno-%"
		--	LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = CONCAT(o.`changwat`, o.`ampur`, o.`tambon`)
		--	LEFT JOIN %co_province% cop ON cop.`provid` = o.`changwat`
		%WHERE%
		GROUP BY p.`tpid`
		ORDER BY CONVERT(`title` USING tis620) ASC
		';

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query); $result->query = mydb()->_query;

	//die;
	/*
				--	, o.`name`
				--	, cos.`lat`
				--	, cos.`lng`
				--	, cop.`provname` `changwatName`
				--	, o.`changwat`, o.`ampur`, o.`tambon`
				--	, (SELECT COUNT(*) FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype` = "โครงการ" AND t.`orgid` = o.`orgid`) `projectCount`
				--	, (SELECT COUNT(*) FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype` = "แผนงาน" AND t.`orgid` = o.`orgid`) `planningCount`
				--	, (SELECT COUNT(*) FROM %project_dev% p LEFT JOIN %topic% t USING(`tpid`) WHERE t.`orgid` = o.`orgid`) `developCount`
	*/

	$result->count = $dbs->_num_rows;
	$result->markers = array();

	foreach ($dbs->items as $rs) {
		if (is_null($rs->name)) $rs->name = '';
		if (is_null($rs->supportplan)) $rs->supportplan = '';
		if ($rs->lat && $rs->lng) {
			$offset1 = rand(0,1000)/1000000;
			$offset2 = rand(0, 1000)/1000000;
			$rs->lat = (double) $rs->lat + $offset1;
			$rs->lng = (double) $rs->lng + $offset2;
			$result->markers[] = $rs;
			//$result[$rs->mapid]=$who;
		}
	}
	//debugMsg($result,'$result');
	//return sg_json_encode($result);
	return $result;
}
?>