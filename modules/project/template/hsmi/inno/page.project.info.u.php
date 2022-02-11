<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_u($self, $projectinfo, $uid) {
	$tpid = $projectinfo->tpid;
	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>รายชื่อ{tr:โครงการ}</h3></header>';


	$ret .= '<h3>{tr:โครงการ}</h3>';

	mydb::where('t.`parent` = :parent', ':parent', SG\getFirst($projectinfo->info->parent, $tpid));
	mydb::where('(t.`uid` = :uid OR tu.`uid` = :uid)', ':uid', $uid);

	$stmt = 'SELECT
		t.`tpid`, t.`title`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_user% tu USING(`tpid`)
		%WHERE%
		GROUP BY `tpid`
		';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->colgroup = array('no'=>'');
	$no = 0;

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(++$no, '<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>');
	}

	$ret .= $tables->build();

	$ret .= '<h3>โครงการขยายผล</h3>';

	mydb::where('t.`parent` = :parent', ':parent', SG\getFirst($projectinfo->info->parent, $tpid));
	mydb::where('(t.`uid` = :uid OR tu.`uid` = :uid)', ':uid', $uid);

	$stmt = 'SELECT
		t.`tpid`, t.`title`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_user% tu USING(`tpid`)
		%WHERE%
		GROUP BY `tpid`
		';

	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->colgroup = array('no'=>'');
	$no = 0;

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(++$no, '<a href="'.url('project/proposal/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>');
	}

	$ret .= $tables->build();

	//$ret .= print_o($projectinfo, $projectinfo);

	return $ret;
}
?>