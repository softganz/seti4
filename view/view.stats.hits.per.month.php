<?php
function view_stats_hits_per_month() {
	$stmt  = 'SELECT
		date_format(`log_date`,"%Y-%m")  AS `log_month` 
		, SUM(`hits`) as `hits`
		, SUM(`users`) as `users` 
		FROM %counter_day% 
		GROUP BY `log_month` 
		ORDER BY `log_month` DESC ';

	$dbs = mydb::select($stmt);

	$max_hits = 0;
	$max_users = 0;
	foreach ( $dbs->items as $rs ) {
		$max_hits = $rs->hits > $max_hits ? $rs->hits : $max_hits;
		$max_users = $rs->users > $max_users ? $rs->users : $max_users;
	}

	$hits_count = 0;
	$users_count = 0;

	$tables = new Table();
	$tables->addClass('hits -sg-text-center');
	$tables->addConfig('caption', 'Hits per month');
	$tables->thead = array(
		'date -date' => 'Date',
		'chart -fill' => '',
		'Hits',
		'Users',
	);

	foreach ($dbs->items as $rs) {
		$hits_count = $hits_count+$rs->hits;
		$users_count = $users_count+$rs->users;
		if ( $max_hits > 0 ) $hit_width = round($rs->hits*200/$max_hits);
		if ( $max_hits > 0 ) $user_width = round($rs->users*200/$max_hits);

		$tables->rows[] = array(
			'<a href="'.url('stats/hits/per/day/'.$rs->log_month).'">'.$rs->log_month.'</a>',
			'<div class="hits-item -hit" style="width:'.$hit_width.'px;"></div><div class="hits-item -user" style="width:'.$user_width.'px;"></div>',
			number_format($rs->hits),
			number_format($rs->users),
		);
	}

	$tables->tfoot[] = array(
		'',
		'Total',
		number_format($hits_count),
		number_format($users_count),
	);

	$ret .= $tables->build();

	$ret .= '<table cellspacing=0 cellpadding=0>
<tr><td><div class="hits-item -hit" style="width:20px;"></td><td>&nbsp;hits</td></tr>
<tr><td><div class="hits-item -user" style="width:20px;"></div></td><td>&nbsp;users</td></tr>
</table>';

	$ret .= '<style type="text/css">
	.hits-item {margin: 0; padding: 0; height: 12px;}
	.hits-item.-hit {background-color:#009900;}
	.hits-item.-user {background-color:#99CC00;}
	</style>';

	return $ret;
}
?>