<?php
/**
* iBuy :: Green Smile Activity
* Created 2020-06-23
* Modify  2020-06-23
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_green_activity($self, $start = 0) {
	$showItems = 10;
	$uid = i()->uid;
	$isAdmin = user_access('administer ibuys');

	mydb::where('m.`tagname` LIKE "GREEN,%" AND `thread` IS NULL AND m.`touid` IS NULL AND m.`replyto` IS NULL');
	mydb::where(NULL, ':uid', i()->uid);
	if (post('u')) mydb::where('m.`uid` = :postuid', ':postuid', post('u'));

	/*
	if ($isAdmin) {
		// Get all record
	} else  if ($zones) {
		mydb::where('('.'p.`uid` = :uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',$uid);
	} else if (i()->ok) {
		mydb::where('s.`uid` = :uid',':uid',$uid);
	} else {
		mydb::where('false');
	}
	*/

	mydb::value('$START$', $start);
	mydb::value('$ITEMS$', $showItems);

	$stmt = 'SELECT
			m.*
		, u.`username`, u.`name` `posterName`
		, l.`landid`, l.`landname`, l.`orgid`
		, fp.`productname`, fp.`startdate`, fp.`cropdate`, fp.`qty`, fp.`unit`
		, fp.`saleprice`, fp.`bookprice`
		, fp.`standard`, fp.`approved`, fp.`detail`
		, fp.`qty` - IFNULL((SELECT SUM(`qty`) FROM %ibuy_farmbook% WHERE `plantid` = fp.`plantid`),0) `balance`
		, lc.`flddata` `locJson`
		, (SELECT GROUP_CONCAT(CONCAT(`fid`,"|",`file`)) FROM %topic_files% WHERE `refid` = m.`msgid` AND `tagname` = m.`tagname`) `photoList`
		, (SELECT COUNT(*) FROM %msg% WHERE `thread` = m.`msgid`) `commentCount`
		, (SELECT `actid` FROM %reaction% WHERE `refid` = m.`msgid` AND `uid` = :uid AND `action` = "MSG.LIKE") `liked`
		FROM %msg% m
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %ibuy_farmplant% fp USING(`plantid`)
			LEFT JOIN %bigdata% lc ON lc.`bigid` = m.`locid`
		%WHERE%
		ORDER BY m.`msgid` DESC
		LIMIT $START$ , $ITEMS$';

	$dbs = mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	$ui = new Ui('div','ui-card ibuy-green-activity');
	$ui->addId('ibuy-green-activity');

	foreach ($dbs->items as $rs) {
		$ui->add(
			R::View(
				'ibuy.green.activity.render',
				$rs,
				'{page: "'.(R()->appAgent ? 'app' : '').'"}'
			),
			'{class: "-ibuy-activity -'.strtolower(str_replace(',', '-', $rs->tagname)).'", id: "ibuy-activity-'.$rs->msgid.'"}'
		);
	}

	if ($start == 0 && $dbs->_empty) {
		$ui->add('<p class="-sg-text-center" style="padding: 32px 0;">ยังไม่มีกิจกรรม</p>');
	}

	$ret .= $ui->build().'<!-- ibuy-green-activity -->';


	if ($dbs->_num_rows && $dbs->_num_rows == $showItems) {
		$ret .= '<div id="more" class="ibuy-green-activity-more" style="padding: 24px 16px 44px;">'
			. '<a class="sg-action btn -primary" href="'.url('ibuy/green/activity/'.($start+$dbs->_num_rows)).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
			. '<span>{tr:More}</span>'
			. '<i class="icon -material">chevron_right</i>'
			. '</a>'
			. '</div>';
	}

	return $ret;
}
?>