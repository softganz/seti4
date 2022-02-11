<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_child_proposal($self, $projectInfo) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR');

	$getProcess = post('process');

	$ret = '';

	$ret .= '<header class="header"><h3>ข้อเสนอโครงการภายใต้ '.$projectInfo->title.'</h3></header>';

	$joinList = array();

	mydb::where('t.`parent` = :tpid', ':tpid', $tpid);

	if ($getProcess) {
		mydb::where('ps.`fldref` IN ( :process )', ':process', 'SET:'.$getProcess);
		$joinList[] = '-- Search process';
		$joinList[] = 'LEFT JOIN %bigdata% ps ON ps.`keyname` = "project.develop" AND ps.`keyid` = p.`tpid` AND ps.`fldname` = "process"';
	}

	mydb::value('$JOIN$', implode(_NL, $joinList), false);

	$stmt = 'SELECT
		p.`tpid`, t.`title`, p.`budget`
		, t.`uid`
		FROM %project_dev% p
			LEFT JOIN %topic% t USING(`tpid`)
			$JOIN$
		%WHERE%
		GROUP BY `tpid`
		';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('no' => '', 'ข้อเสนอโครงการ', 'budget -money' => 'งบประมาณ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			'<a href="'.url('project/proposal/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',
			number_format($rs->budget,2),
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>