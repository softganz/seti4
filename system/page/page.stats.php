<?php
function stats($self) {
	$today = today();

	user_menu('list','list',url('stats/list'));
	$self->theme->navigator = user_menu();

	$stmt = 'SELECT COUNT(*) `total` FROM %users_online% LIMIT 1';
	$onlines = mydb::select($stmt)->total;

	$self->theme->title = 'Current online <b>'.number_format($onlines).'</b> users'
		. '@'.sg_date($today->datetime,cfg('dateformat'));

	$yesterday = date('Y-m-d',mktime(0,0,0,$today->mon,$today->mday-1,$today->year));

	mydb::where('log_date>= :yesterday', ':yesterday', $yesterday);
	$stmt  = 'SELECT `log_date`, `hits`, `users`
		FROM %counter_day%
		%WHERE%
		ORDER BY log_date DESC LIMIT 2';

	$rs = mydb::select($stmt);

	foreach ($rs->items as $item) {
		if ($item->log_date == $today->date) $today_hits = $item;
		else if ($item->log_date == $yesterday) $yesterday_hits = $item;
	}

	$ret .= '<p>'
		. 'Today <strong>'.number_format($today_hits->hits).'</strong> hits from <strong>'.number_format($today_hits->users).'</strong> users. '
		. 'Yesterday <strong>'.number_format($yesterday_hits->hits).'</strong> hits from <strong>'.number_format($yesterday_hits->users).'</strong> users.'
		. '</p>';

	$ret .= R::Page('stats.online', $self);

	$ret .= R::View('stats.hits.per.day',date('Y'),date('m'));

	$ret .= R::View('stats.hits.per.month');
	return $ret;
}
?>