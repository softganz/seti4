<?php
/**
* Get Project TOR
* Created 2017-04-15
* Modify  2019-10-29
*
* @param Object $conditions, Int $tpid
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_project_tor_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$tpid = $conditions;
		$conditions = (Object) ['tpid' => $tpid];
	}

	$stmt = 'SELECT
		  tr.`trid` `torId`
		, tr.`tpid`
		, tr.`formid`
		, tr.`part`
		, tr.`date1` `tordate`
		, tr.`detail1` `payan1`
		, tr.`detail2` `payan2`
		, tr.`detail3` `payantype1`
		, tr.`detail4` `payantype2`
		, tr.`uid`
		, tr.`created`
		, tr.`modified`
		, tr.`modifyby`
		FROM %project_tr% tr
		WHERE tr.`tpid` = :tpid AND tr.`formid` = "tor" AND tr.`part` = "title"
		LIMIT 1';

	$result = mydb::select($stmt,':tpid',$tpid);

	//debugMsg(mydb()->_query);

	if ($result->_empty) return NULL;

	$result = mydb::clearprop($result);

	$stmt = 'SELECT
		f.`fid`, f.`type`, f.`file`, f.`title`
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND `tagname` = "project,tor"
		ORDER BY `fid` ASC';

	$result->photos = mydb::select($stmt, ':tpid',$tpid)->items;

	return $result;
}
?>