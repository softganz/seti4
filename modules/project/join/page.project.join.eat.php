<?php
/**
* Project Action Join Eat
* Created 2019-02-20
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_eat($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;
	$showJoinGroup = post('group');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	// Show All of Register
	// Only show for auth
	mydb::where('do.`tpid` = :tpid AND do.`calid` = :calid', ':tpid', $tpid, ':calid', $calId);

	// Show Register
	$stmt = 'SELECT
		  ds.`foodtype`, COUNT(*) `amt`
		FROM %org_dos% ds
			LEFT JOIN %org_doings% do USING(`doid`)
		%WHERE%
		GROUP BY `foodtype`
		ORDER BY `amt` DESC;
		-- {sum: "amt"}';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;


	$tables = new Table();
	$graphYear = new Table();
	$graphYear->addClass('-hidden');

	$tables->thead = array('อาหาร', 'amt -total-type' => 'จำนวนคน','amt -percent' => '%');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			SG\getFirst($rs->foodtype, 'ไม่ระบุ'),
			number_format($rs->amt),
			number_format($rs->amt*100/$dbs->sum->amt,2),
		);

		$graphYear->rows[] = array(
			'string:Year' => SG\getFirst($rs->foodtype, 'ไม่ระบุ'),
			'number:Budget' => $rs->amt
		);
	}
	$tables->tfoot[] = array('รวม', number_format($dbs->sum->amt),'');

	$ret .= '<div id="chart-app" class="sg-chart -jointype" data-chart-type="pie" data-options=\'{"pieHole": 0.4'.($isMobileDevice ? ', "legend": "bottom"' : '').'}\'>'._NL.$graphYear->build().'</div>'._NL;

	$ret .= $tables->build();
	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	.sg-chart.-jointype {height: 400px;}
	</style>';
	return $ret;
}
?>