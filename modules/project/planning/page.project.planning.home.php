<?php
function project_planning_home($self) {
	$planSelect = SG\getFirst(post('pn'),NULL);
	$provinceSelect = SG\getFirst(post('pv'),NULL);
	$ampurSelect = SG\getFirst(post('am'), NULL);
	$areaSelect = SG\getFirst(post('ar'),NULL);
	$sectorSelect = SG\getFirst(post('s'),NULL);
	$yearSelect = post('yr');

	$ret .= '<div id="project-planning-last">';
	$ret .= '<h3>Last 50 Plans</h3>';

	mydb::where('p.`prtype` = "แผนงาน"');
	$stmt = 'SELECT
		t.`tpid`,t.`orgid`,t.`title`,t.`changwat`,cop.`provname`,t.`created`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
		%WHERE%
		ORDER BY t.`tpid` DESC
		LIMIT 50
		';
	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	$tables = new Table();
	$tables->thead = array(
		'แผนงาน',
		'center -chanhwat' => 'จังหวัด',
		'date' => 'วันที่สร้างแผนงาน'
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="' . url('project/planning/' . $rs->tpid) . '">' . $rs->title . '</a>',
			$rs->provname,
			$rs->created
		);
	}
	$ret .= $tables->build();
	$ret .= '</div>';
	//$ret.=print_o($dbs,'$dbs');

	$ret .= '<script type="text/javascript">
	$("#edit-pv").change(function(){
		$("#edit-am").val("")
	})
	$("#project-planning-nav select").change(function() {
		console.log($(this).val())
		notify("LOADING")
		$(this).closest("form").submit()
	})
	</script>';
	return $ret;
}
?>