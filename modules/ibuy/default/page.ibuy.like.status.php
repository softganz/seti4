<?php
/**
* Like status
*
* @param Object $self
* @param Int $tpid
* @param String $status
* @return String
*/

$debug = true;

function ibuy_like_status($self, $tpid, $status = NULL) {
	$ret = '';

	if ($tpid && $status) {
		if (i()->ok) {
			switch ($status) {
				case 'bookmark':
					R::Model('reaction.add', $tpid, 'IBUY.BOOKM', '{addType: "toggle"}');
					break;

				case 'like':
					R::Model('reaction.add', $tpid, 'IBUY.LIKE', '{addType: "toggle", count: "topic:liketimes"}');
					break;

				case 'rate':
					$ret .= R::Page('node.review', $self, $tpid);
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


	$isMyAction = array();
	if (i()->ok) {
		$stmt = 'SELECT DISTINCT
			  `action`
			FROM %reaction%
			WHERE `refid` = :tpid AND `uid` = :uid;
			-- {key: "action"}
						';
		$isMyAction = explode(',',mydb::select($stmt, ':tpid',$tpid, ':uid', i()->uid)->lists->text);
	}

	$stmt = 'SELECT COUNT(*) `totals`
					FROM %reaction%
					WHERE `refid` = :tpid AND `action` = "IBUY.BOOKM" LIMIT 1';

	$bookmarkTotals = mydb::select($stmt, ':tpid',$tpid)->totals;

	//$ret .= '<div style="text-align:left;">'.print_o($isMyAction,'$isMyAction').'</div>';
	//$ret .= '<div style="text-align:left;">'.print_o($likeTotals,'$likeTotals').'</div>';

	$btnClass = i()->ok ? '' : ' -disabled';

	$ui = new Ui('span', 'ui-like-status');
	if ($error) $ui->add($error);
	$ui->add(
				(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('ibuy/like/status/'.$tpid.'/rate').'" data-rel="box" rel="nofollow" data-width="600" title="คลิกเพื่อรีวิวและให้คะแนน">':'<a class="btn -link'.$btnClass.'">')
				.'<i class="icon -material rating-star '.($ratings != '' ? '-rate-'.round($ratings) : '').'">star</i>'
				.'<span>'.$ratings.' Ratings</span><span>'.$views.' Views</span>'
				.'</a>'
			);
	$ui->add(
				(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('ibuy/like/status/'.$tpid.'/like').'" data-rel="replace:.ui-like-status" title="'.number_format($likeTotals).' People Like this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
				. '<i class="icon -thumbup '.(in_array('IBUY.LIKE',$isMyAction) ? '' : '-gray').'"></i>'
				. '<span>'.($likeTotals ? $likeTotals.' Likes' : 'Like').'</span>'
				. '</a>'
			);
	$ui->add(
				(i()->ok ? '<a class="sg-action btn -link'.$btnClass.'" href="'.url('ibuy/like/status/'.$tpid.'/bookmark').'" data-rel="replace:.ui-like-status" title="'.number_format($bookmarkTotals).' Peoples Bookmark this" rel="nofollow">' : '<a class="btn -link'.$btnClass.'">')
				. '<i class="icon -favorite '.(in_array('IBUY.BOOKM',$isMyAction) ? '' : '-outline -gray').'"></i>'
				. '<span class="">Bookmark</span>'
				. '</a>'
			);
	$ret .= $ui->build();
	return $ret;
}
?>