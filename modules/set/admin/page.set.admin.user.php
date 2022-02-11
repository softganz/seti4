<?php
function set_admin_user($self) {
	$dbs=mydb::select('SELECT DISTINCT `uid`, `name` FROM %setport% p LEFT JOIN %users% u USING (`uid`) ');
	$ret.='<ul>';
	foreach ($dbs->items as $rs) {
		$ret.='<li><a class="sg-action" href="'.url('set/portstatus',array('u'=>$rs->uid)).'" data-rel="#set-info">'.$rs->name.'</a></li>';
	}
	$ret.='</ul>';
	return $ret;
}
?>