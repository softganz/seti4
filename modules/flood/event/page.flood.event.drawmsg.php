<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_event_drawmsg($self,$station=NULL) {
	$where=array();
	$where=sg::add_condition($where,'`parent` IS NULL');
	if ($station) $where=sg::add_condition($where,'`station` IN (:station)','station','SET-STRING:'.$station);
	$stmt='SELECT e.*, s.`title` stationTitle, u.`username`, u.`name`
				FROM %flood_event% e
					LEFT JOIN %users% u USING(uid)
					LEFT JOIN %flood_station% s USING(`station`)
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				ORDER BY eid DESC LIMIT 50';
	$dbs=mydb::select($stmt,$where['value']);

	$eid=array();
	foreach ($dbs->items as $rs) $eid[]=$rs->eid;

	if ($eid) {
		$cdbs=mydb::select('SELECT e.*, u.`username`, u.`name` FROM %flood_event% e LEFT JOIN %users% u USING(uid) WHERE `parent` IN ('.implode(',', $eid).') ORDER BY `parent` ASC, `eid` ASC');
		foreach ($cdbs->items as $rs) $comments[$rs->parent][$rs->eid]=$rs;
	}

	$ret.='<p align="right">ล่าสุดเมื่อ '.sg_date('ว ดดด ปปปป H:i:s').'</p>';

	foreach ($dbs->items as $rs) {
		$ret .= R::View('flood.event.render',$rs,$comments[$rs->eid])._NL;
	}
	return $ret;
}
?>