<?php

function project_develop_history($self,$tpid) {
	$ret.='<h2>ประวัติ</h2>';
	$stmt='SELECT w.*, u.`name`
					FROM %watchdog% w
					LEFT JOIN %users% u USING(`uid`)
					WHERE `keyid`=:tpid
					ORDER BY `wid` DESC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$ret.='<ul class="card__master">';
	foreach ($dbs->items as $rs) {
		$ret.='<li class="card__item"><div class="timestamp">'.$rs->name.' @'.sg_date($rs->date,'ว ดด ปปปป H:i:s').'</div>';
		$ret.='<div>Action is <strong>'.$rs->keyword.'</strong>'.($rs->fldname?' of field <strong>'.$rs->fldname.'</strong>':'').'</div>';
		$ret.=sg_text2html($rs->message);
		$ret.='</li>';
	}
	$ret.='</ul>';
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>