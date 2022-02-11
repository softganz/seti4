<?php
/**
* Show symbol in port
*
* @return String
*/
function set_port($self) {
	$stmt='SELECT
				  `symbol`
				, SUM(CASE bsd WHEN "B" then `volumes` WHEN "S" then -`volumes` WHEN "D" then `volumes` END) `netBalance`
				FROM %setport% p
				WHERE `uid` = :uid
				GROUP BY `symbol`
				HAVING `netBalance` > 0
				ORDER BY `bsd` = "D", `symbol` ASC';

	$dbs = mydb::select($stmt,':uid',i()->uid);

	$ret.='<ul>';
	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) $ret.='<li><a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output">'.$rs->symbol.'</a></li>'._NL;
	}
	$ret.='<li><a class="sg-action" href="'.url('set/view/.SET').'" data-rel="#app-output">SET</a></li>'._NL;
	$ret.='<li><a class="sg-action" href="'.url('set/view/.SET50').'" data-rel="#app-output">SET50</a></li>'._NL;
	$ret.='<li><a class="sg-action" href="'.url('set/view/.MAI').'" data-rel="#app-output">MAI</a></li>'._NL;
	$ret.='<li><a href="http://marketdata.set.or.th/mkt/sectorialindices.do?country=th&language=th" target="_blank">กลุ่มอุตสาหกรรม</a></li><li><a href="http://www.set.or.th/set/todaynews.do?language=th&country=TH" target="_blank">ข่าวบริษัท/หลักทรัพย์</a></li>';
	$ret.='</ul>'._NL;
	$ret.='<p><a class="sg-action btn" href="'.url('set/createport').'" data-rel="#app-output" title="Create new port">Crerate new port</a></p>';
	$ret.='</div>'._NL;

	$ret.='</div>'._NL;

	return $ret;
}
?>