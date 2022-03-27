<?php
/**
* Module Method
*
* @param
* @return String
*/

$debug = true;

function paper_like_status($self, $tpid, $status = NULL) {
	$ret = '';

	if ($tpid && $status) {
		if (i()->ok) {
			switch ($status) {
				case 'bookmark':
					R::Model('reaction.add', $tpid, 'TOPIC.BOOK', '{addType: "toggle"}');
					break;

				case 'like':
					R::Model('reaction.add', $tpid, 'TOPIC.LIKE', '{addType: "toggle"}');
					break;

				case 'rate':
					$ret .= (R::PageWidget('node.review', [(Object)['nodeId' => $tpid]]))->build();
					return $ret;

				default:
					# code...
					break;
			}
		} else $error = 'For Member Only';
	}

	$nodeInfo = mydb::select('SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid);
	$views = $nodeInfo->view;
	$ratings = $nodeInfo->rating;
	$likeTotals = $nodeInfo->liketimes;


	$isMyAction = [];

	if (i()->ok) {
		$stmt = 'SELECT DISTINCT
			`action`
			FROM %reaction%
			WHERE `refid` = :tpid AND `uid` = :uid;
			-- {key: "action", value: "action"}
			';
		$isMyAction = explode(',',mydb::select($stmt, ':tpid',$tpid, ':uid', i()->uid)->lists->text);
		$isMyAction = mydb::select($stmt, ':tpid',$tpid, ':uid', i()->uid)->items;
	}

	$bookmarkTotals = mydb::select(
		'SELECT COUNT(*) `totals`
		FROM %reaction%
		WHERE `refid` = :tpid AND `action` = "TOPIC.BOOK" LIMIT 1',
		[ ':tpid' => $tpid]
	)->totals;

	$btnClass = i()->ok ? '' : ' -disabled';

	$ui = new Ui('span', 'ui-like-status');
	if ($error) $ui->add($error);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/rate').'" rel="nofollow" data-width="600" data-rel="box" title="คลิกเพื่อรีวิวและให้คะแนน">':'<a class="btn -link'.$btnClass.'">')
		.'<i class="icon -material rating-star '.($ratings != '' ? '-rate-'.round($ratings) : '').'">star</i>'
		.'<span>'.$ratings.' Ratings</span><span>'.$views.' Views</span>'
		.'</a>'
	);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/like').'" data-rel="replace:.ui-like-status" data-options=\'{"silent": true}\' title="'.number_format($likeTotals).' People Like this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
		. '<i class="icon -thumbup '.(array_key_exists('TOPIC.LIKE',$isMyAction) ? '' : '-gray').'"></i>'
		. '<span>'.($likeTotals ? $likeTotals.' Likes' : 'Like').'</span>'
		. '</a>'
	);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/bookmark').'" data-rel="replace:.ui-like-status" data-options=\'{"silent": true}\' title="'.number_format($bookmarkTotals).' Peoples Bookmark this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
		. '<i class="icon -material '.(array_key_exists('TOPIC.BOOK',$isMyAction) ? '-active' : '-gray').'">bookmark_add</i>'
		. '<span class="">Bookmark</span>'
		. '</a>'
	);

	$ret .= $ui->build();

	return $ret;
}
?>
