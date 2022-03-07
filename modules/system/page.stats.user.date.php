<?php
function stats_user_date($self,$date) {
	if (empty($date)) $date=date('Y-m-d');

	$stmt='SELECT a.*, u.`name`
				FROM
					(SELECT DISTINCT
						DATE(w.`date`), w.`uid`
					FROM %watchdog% w
					WHERE w.`uid` IS NOT NULL AND w.`date` between :start AND :end
					) a
					LEFT JOIN %users% u USING(`uid`)
				ORDER BY u.`name` ASC';
	$dbs=mydb::select($stmt,':start',$date.' 00:00:00',':end',$date.' 23:59:59');

	$ret.='<header class="header -box"><h3>Member list</h3></header>';
	$ret.='<ol>';
	foreach ($dbs->items as $rs) {
		$ret.='<li><a href="'.url('profile/'.$rs->uid).'">'.$rs->name.'</a></li>';
	}
	$ret.='</ol>';
	return $ret;
}
?>