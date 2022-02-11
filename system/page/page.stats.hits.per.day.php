<?php
function stats_hits_per_day($self,$year) {
	if ($year) {
		list($year,$month)=explode('-',$year);
	} else {
		$month = date('m');
		$year = date('Y');
	}

	$self->theme->navigator=user_menu();
	$ret.='<h3>Hits per day</h3>';
	$ret.=R::View('stats.hits.per.day',$year,$month);
	return $ret;
}
?>