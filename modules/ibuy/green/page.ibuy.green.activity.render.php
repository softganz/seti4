<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_green_activity_render($self, $msgId) {
	$ret = '';

	mydb::where('m.`msgid` = :msgId AND m.`tagname` LIKE "GREEN,%" AND m.`touid` IS NULL', ':msgId', $msgId);

	$stmt = 'SELECT
			m.*
		, u.`username`, u.`name` `posterName`
		, l.`landid`, l.`landname`, l.`orgid`
		, fp.`productname`, fp.`startdate`, fp.`cropdate`, fp.`qty`, fp.`unit`, fp.`saleprice`, fp.`bookprice`, fp.`standard`, fp.`approved`, fp.`detail`
		, fp.`qty` - IFNULL((SELECT SUM(`qty`) FROM %ibuy_farmbook% WHERE `plantid` = fp.`plantid`),0) `balance`
		, lc.`flddata` `locJson`
		, (SELECT GROUP_CONCAT(CONCAT(`fid`,"|",`file`)) FROM %topic_files% WHERE `refid` = m.`msgid` AND `tagname` = m.`tagname`) `photoList`
		, (SELECT COUNT(*) FROM %msg% WHERE `thread` = m.`msgid`) `commentCount`
		FROM %msg% m
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %ibuy_farmplant% fp USING(`plantid`)
			LEFT JOIN %bigdata% lc ON lc.`bigid` = m.`locid`
		%WHERE%
		ORDER BY m.`created` DESC
		LIMIT 1';

	$rs = mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	if ($rs->_empty) return NULL;

	$ret .= '<div class="ui-item -'.strtolower(str_replace(',', '-', $rs->tagname)).'" id="ibuy-activity-'.$msgId.'">';

	if ($rs->tagname == "GREEN,ACTIVITY") {
		$ret .= R::View(
			'ibuy.green.activity.render',
			$rs,
			'{page: "'.(R()->appAgent ? 'app' : '').'"}'
		);
	} else {
		$ret .= R::View(
			'ibuy.green.activity.render',
			$rs,
			'{page: "'.(R()->appAgent ? 'app' : '').'"}'
		);
	}

	$ret .= '</div>';

	return $ret;
}
?>