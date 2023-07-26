<?php
/**
* Paper   :: Like Status
* Created :: 2021-05-31
* Modify  :: 2023-07-26
* Version :: 2
*
* @param Int $nodeId
* @return String
*
* @usage paper/like/status/{nodeId}
*/

function paper_like_status($self, $tpid) {
	$BOOKMARK = 'TOPIC.BOOK';
	$LIKE = 'TOPIC.LIKE';
	$ret = '';

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
// debugMsg($isMyAction, '$isMyAction');
	$bookmarkTotals = mydb::select(
		'SELECT COUNT(*) `totals`
		FROM %reaction%
		WHERE `refid` = :tpid AND `action` = :bokmark LIMIT 1',
		[ ':tpid' => $tpid, ':bookmark' => $BOOKMARK]
	)->totals;

	$btnClass = i()->ok ? '' : ' -disabled';

	$ui = new Ui('span', 'ui-like-status');
	if ($error) $ui->add($error);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('node/'.$tpid.'/review').'" rel="nofollow" data-width="600" data-rel="box" title="คลิกเพื่อรีวิวและให้คะแนน">':'<a class="btn -link'.$btnClass.'">')
		.'<i class="icon -material rating-star '.($ratings != '' ? '-rate-'.round($ratings) : '').'">star</i>'
		.'<span>'.$ratings.' Ratings</span><span>'.$views.' Views</span>'
		.'</a>'
	);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('api/node/info/'.$tpid.'/like/'.$LIKE).'" data-rel="notify" data-done="load:parent .nav:'.url('paper/like/status/'.$tpid).'" data-options=\'{"silent": true}\' title="'.number_format($likeTotals).' People Like this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
		. '<i class="icon -material '.(array_key_exists($LIKE,$isMyAction) ? '-active' : '-gray').'">thumb_up_alt</i>'
		. '<span>'.($likeTotals ? $likeTotals.' Likes' : 'Like').'</span>'
		. '</a>'
	);

	$ui->add(
		(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('api/node/info/'.$tpid.'/bookmark/'.$BOOKMARK).'" data-rel="notify" data-done="load:parent .nav:'.url('paper/like/status/'.$tpid).'" data-options=\'{"silent": true}\' title="'.number_format($bookmarkTotals).' Peoples Bookmark this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
		. '<i class="icon -material '.(array_key_exists($BOOKMARK,$isMyAction) ? '-active' : '-gray').'">bookmark_add</i>'
		. '<span class="">Bookmark</span>'
		. '</a>'
	);

	// $ui->add(
	// 	(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/rate').'" rel="nofollow" data-width="600" data-rel="box" title="คลิกเพื่อรีวิวและให้คะแนน">':'<a class="btn -link'.$btnClass.'">')
	// 	.'<i class="icon -material rating-star '.($ratings != '' ? '-rate-'.round($ratings) : '').'">star</i>'
	// 	.'<span>'.$ratings.' Ratings</span><span>'.$views.' Views</span>'
	// 	.'</a>'
	// );

	// $ui->add(
	// 	(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/like').'" data-rel="replace:.ui-like-status" data-options=\'{"silent": true}\' title="'.number_format($likeTotals).' People Like this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
	// 	. '<i class="icon -material '.(array_key_exists($LIKE,$isMyAction) ? '-active' : '-gray').'">thumb_up_alt</i>'
	// 	. '<span>'.($likeTotals ? $likeTotals.' Likes' : 'Like').'</span>'
	// 	. '</a>'
	// );

	// $ui->add(
	// 	(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('paper/like/status/'.$tpid.'/bookmark').'" data-rel="replace:.ui-like-status" data-options=\'{"silent": true}\' title="'.number_format($bookmarkTotals).' Peoples Bookmark this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
	// 	. '<i class="icon -material '.(array_key_exists($BOOKMARK,$isMyAction) ? '-active' : '-gray').'">bookmark_add</i>'
	// 	. '<span class="">Bookmark</span>'
	// 	. '</a>'
	// );

	$ret .= $ui->build();

	return $ret;
}
?>
