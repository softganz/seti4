<?php
/**
* iBuy :: Green Land Activity
*
* @param Object $self
* @param Object $landInfo
* @return String
*/

$debug = true;

function green_land_activity($self, $landInfo) {
	if (!($landId = $landInfo->landId)) return message('error', 'PROCESS ERROR');

	$start = SG\getFirst(post('start'),0);
	$showItems = 10;
	$uid = i()->uid;
	$isAdmin = user_access('administer ibuys');


	$ret = $start == 0 ? '<section>' : '';

	mydb::where('m.`landid` = :landid AND m.`tagname` LIKE "GREEN,%" AND m.`touid` IS NULL', ':landid', $landId);


	mydb::value('$START$', $start);
	mydb::value('$ITEMS$', $showItems);

	$stmt = 'SELECT
			m.*
		, u.`username`, u.`name` `posterName`
		, l.`landid`, l.`landname`
		, (SELECT GROUP_CONCAT(CONCAT(`fid`,"|",`file`)) FROM %topic_files% WHERE `refid` = m.`msgid` AND `tagname` = m.`tagname`) `photoList`
		FROM %msg% m
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
		%WHERE%
		ORDER BY m.`created` DESC
		LIMIT $START$ , $ITEMS$';

	$dbs = mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	$ui = new Ui('div','ui-card green-activity');
	$ui->addId('green-activity');

	foreach ($dbs->items as $rs) {
		if ($rs->tagname == "ibuy-activity") {
			$ui->add(
				R::View(
					'green.activity.render',
					$rs,
					'{page: "'.(R()->appAgent ? 'app' : '').'"}'
				),
				'{class: "", id: "ibuy-activity-'.$rs->msgid.'"}'
			);
		} else {
			$ui->add(
				R::View(
					'green.activity.render',
					$rs,
					'{page: "'.(R()->appAgent ? 'app' : '').'"}'
				),
				'{class: "-urgency-'.$rs->urgency.'", id: "ibuy-activity-'.$rs->msgid.'"}'
			);
			//$ret.='<div class="noteUnit -urgency-'.$rs->urgency.' col'.($c=++$no%2+1).'" id="noteUnit-'.$rs->seq.'">'._NL;
		}
	}

	if ($start == 0 && $dbs->_empty) {
		$ui->add('<p class="-sg-text-center" style="padding: 32px 0;">ยังไม่มีกิจกรรม</p>');
	}

	$ret .= $ui->build().'<!-- green-activity -->';


	if ($dbs->_num_rows && $dbs->_num_rows == $showItems) {
		$ret .= '<div id="more" class="green-activity-more" style="padding: 24px 16px 44px;">'
			. '<a class="sg-action btn -primary" href="'.url('green/land/'.$landId.'/activity',array('start' => $start+$dbs->_num_rows)).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
			. '<span>{tr:More}</span>'
			. '<i class="icon -material">chevron_right</i>'
			. '</a>'
			. '</div>';
	}

	$ret .= $start == 0 ? '</section>' : '';
	return $ret;
}
?>