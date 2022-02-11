<?php
function project_history($self) {
	$tpid=post('tpid');
	$key=post('k');
	$isAdmin=user_access('administer projects');
	$ret.='<h2>ประวัติการแก้ไข</h2>';
	$stmt='SELECT w.*, u.`name` posterName
					FROM %watchdog% w
						LEFT JOIN %users% u USING (`uid`)
					WHERE `keyid`=:tpid AND `fldname`=:fldname
					ORDER BY `wid` DESC';
	$dbs=mydb::select($stmt,':tpid',$tpid,':fldname',$key);
	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) {
			$ret.='<div class="card">'._NL;
			$ret.='<div class="timestamp">แก้ไขเมื่อ '.sg_date($rs->date,'ว ดด ปปปป H:i:s').' โดย '.$rs->posterName.'</div>'._NL;
			$ret.=sg_text2html($rs->message)._NL;
			if ($isAdmin) {
				$ret.='<p class="timestamp">Url : '.$rs->url.'<br />Referer : '.$rs->referer.'<br />browser : '.$rs->browser.'</p>'._NL;
			}
			$ret.='</div>'._NL;
		}
	} else {
		$ret.='<p>ไม่มีประวัติการแก้ไข</p>';
	}
	return $ret;
}
?>