<?php
/**
 * Widget   :: Inline Statistic Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2011-11-04
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * @param String $para
 * 	data-header=Header
 * @return String
 */

function widget_stat($funcName = NULL, $para = NULL) {
	$isAccessStat = user_access('access statistic');
	$counter = cfg('counter');

	$today = date('Y-m-d');
	$yesterday = date('Y-m-d', strtotime( '-1 days' ) );

	$stmt  = 'SELECT log_date,hits,users FROM %counter_day% WHERE log_date IN (:yesterday, :today)';
	$dbs = mydb::select($stmt,':yesterday',$yesterday,':today',$today);

	$today_hits = $yesterday_hits = null;
	foreach ($dbs->items as $rs) {
		if ($rs->log_date==$today) $today_hits=$rs;
		else if ($rs->log_date==$yesterday) $yesterday_hits=$rs;
	}

	$ret = '<span class="stat--label">'
		. ($isAccessStat ? '<a href="'.url('stats').'">' : '')
		. 'Web Statistics: '.($isAccessStat ? '</a>' : '')
		. '</span>'
		. '<span class="stat--current">Current <strong><a href="'.($isAccessStat ? url('stats') : '#').'" title="'.($isAccessStat ? $counter->online_name : '').'">'.$counter->online_members.'</a></strong> '
		. 'members from <strong>'.number_format($counter->online_count).'</strong>persons online. </span>'
		. '<span class="stat--today">Today <strong>'.number_format($today_hits->users).'</strong> persons <strong>'.number_format($today_hits->hits).'</strong> views. </span>'
		. '<span class="stat--yesterday">Yesterday <strong>'.number_format($yesterday_hits->users).'</strong> persons '
		. '<strong>'.number_format($yesterday_hits->hits).'</strong> views. </span>'
		. '<span class="stat--total">Total view <strong>'.number_format($counter->users_count).'</strong> persons '
		. '<strong>'.number_format($counter->hits_count).'</strong> views from <strong>'.number_format($counter->members).'</strong> members. '
		. 'Since '.sg_date($counter->created_date,'M,d Y')
		. '.</span><!--stat-->';

	return array($ret,$para);
}
?>