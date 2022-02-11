<?php
function view_stats_hits_per_day($year=null,$month=null) {
	mydb::where('DATE_FORMAT(log_date,"%Y")=:year',':year',$year);
	if ($month) mydb::where('DATE_FORMAT(log_date,"%m")=:month',':month',$month);

	$stmt='SELECT * 
		FROM %counter_day%
		%WHERE%
		ORDER BY `log_date` DESC';

	$dbs = mydb::select($stmt);

	$max_hits = 0;
	$max_users = 0;

	foreach ( $dbs->items as $rs ) {
		$max_hits = $rs->hits > $max_hits ? $rs->hits : $max_hits;
		$max_users = $rs->users > $max_users ? $rs->users : $max_users;
	}

	$no = 0;
	$hits_count = 0;
	$users_count = 0;
	$is_view_log = user_access('administer contents,administer watchdogs');

	$tables = new Table();
	$tables->addClass('hits -sg-text-center');
	$tables->addConfig('caption', 'Hits per day');
	$tables->thead = array(
		'date -date' => 'Date',
		'chart -fill' => '',
		'Hits',
		'Users',
		'Member',
	);

	foreach ($dbs->items as $rs) {
		$no++;
		$hits_count = $hits_count+$rs->hits;
		$users_count = $users_count+$rs->users;
		if ( $max_hits > 0 ) $hit_width = round($rs->hits*200/$max_hits);
		if ( $max_hits > 0 ) $user_width = round($rs->users*200/$max_hits);

		$tables->rows[] = array(
			($is_view_log?'<a href="'.url('stats/list',array('date'=>$rs->log_date)).'">':'').$rs->log_date.($is_view_log?'</a>':''),
			'<div class="hits-item -hit" style="width:'.$hit_width.'px;"></div><div class="hits-item -user" style="width:'.$user_width.'px;"></div>',
			number_format($rs->hits),
			number_format($rs->users),
			'<a class="sg-action" href="'.url('stats/user/date/'.$rs->log_date).'" data-rel="box" data-width="480" data-height="480"><i class="icon -material">find_in_page</i></a>',
		);
	}

	$tables->tfoot[] = array(
		'',
		'Total',
		number_format($hits_count),
		number_format($users_count),
		'',
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