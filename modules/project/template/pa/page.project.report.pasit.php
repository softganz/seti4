<?php
/**
* Project NHSO Action for Export
*
* @param Object $self
* @return String
*/

$debug = true;

function project_report_pasit($self) {
	$year = SG\getFirst(post('yr'), date('Y'));
	$region = post('region');
	$prov = post('prov');
	$planIdSelect = post('pnid');

	$repTitle='สถานการณ์สุขภาพ';


	R::View('project.toolbar', $self, $repTitle, 'report');

	$ret = '';

	$regionList = array('1,7'=>'ภาคกลาง', '3,4'=>'ภาคเหนือ', '5,6'=>'ภาคอีสาน', '2'=>'ภาคตะวันออก', '8,9'=>'ภาคใต้');
	$provList = array(''=>'ทุกจังหวัด');
	$dbs = mydb::select('SELECT DISTINCT `changwat`, `provname` `changwatName` FROM %project% p LEFT JOIN %co_province% cop ON p.`changwat` = cop.`provid` HAVING `provname` IS NOT NULL; -- {key: "changwat"}');
	foreach ($dbs->items as $rs) $provList[$rs->changwat] = $rs->changwatName;

	$form = new Form(NULL, url('project/report/pasit'), 'condition', 'sg-form -inlineitem');

	$catList = R::Model('category.get',array('taggroup' => "project:planning", 'process' => 1),'catid');

	$form->addField(
		'pnid',
		array(
			'type' => 'select',
			//'label' => 'แผนงาน',
			'options' => array(''=>'== ทุกแผนงาน ==')+$catList,
			'value' => $planIdSelect,
		)
	);

	$form->addField(
		'region',
		array(
			'type' => 'select',
			'options' => array('' => 'ทุกภาค')+$regionList,
			'value' => $region,
		)
	);

	$form->addField(
		'prov',
		array(
			'type' => 'select',
			'id' => 'province',
			'options' => $provList,
			'value' => $prov,
		)
	);

	$form->addField(
		'pset',
		array(
			'type' => 'select',
			'options' => array('' => 'ทุกโครงการ')
		)
	);

	$form->addField(
		'view',
		array(
			'type' => 'button',
			'value' => '<i class="icon -search -white"></i>',
		)
	);

/*
	'<form id="condition" action="'.url('project/nhso/action').'" method="get">';
	
	// Select year
	$stmt = 'SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`) >= 10, 1, 0) `budgetYear` FROM %project_gl% HAVING `budgetYear` ORDER BY `budgetYear` ASC';
	$yearList = mydb::select($stmt);
	$form .= '<select id="year" class="form-select" name="yr">';
	//$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form .= '<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form .= '</select> ';

	// Select area
	$form .= '<select id="area" class="form-select" name="area">';
	$form .= '<option value="">ทุกเขต</option>';
	$areaList = mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype` = "nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form .= '<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form .= '</select> ';

	// Select province
	if ($area) {
		$stmt = 'SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid` = :areaid HAVING `provname` IS NOT NULL';
		$provList = mydb::select($stmt,':areaid',$area);
		$form .= '<select id="province" class="form-select" name="prov">';
		$form .= '<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form .= '<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form .= '</select> ';
	}

	// Select province
	if ($prov) {
		$stmt = 'SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2) = :prov HAVING `distname` IS NOT NULL ';
		$ampurList = mydb::select($stmt,':prov',$prov);
		$form .= '<select id="ampur" class="form-select" name="ampur">';
		$form .= '<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form .= '<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form .= '</select> ';
	}
	//$form.='<label><input type="checkbox" name="gr" value="1" '.($graphSummary==1?'checked="checked"':'').' />แสดงกราฟผลรวม</label>';

	$form .= '<button class="btn -primary" type="submit"><i class="icon -search -white"></i></button> ';
	$form .= '</form>'._NL;
	*/

	$ret .= '<nav class="nav -page">'.$form->build().'</nav>';





	mydb::where('pb.`formid` = "info" AND pb.`part` = "problem"');
	if ($region) mydb::where('LEFT(d.`changwat`,1) IN (:region)',':region','SET:'.$region);
	if ($prov) mydb::where('d.`changwat`=:prov', ':prov', $prov);
	if ($planIdSelect) mydb::where('pb.`tagname` = :planningId', ':planningId', 'project:problem:'.$planIdSelect);

	$stmt = 'SELECT
		  pb.`tpid`
		, pb.`tagname`
		, pbc.`catparent`
		, pb.`detail1` `problemName`
		, AVG(pb.`num1`) `problemSizeAvg`
		, AVG(oj.`num2`) `targetSizeAvg`
	--	, pg.*
		FROM %project_tr% pb
			LEFT JOIN %project_dev% d USING(`tpid`)
			LEFT JOIN %tag% pbc ON pbc.`taggroup` = pb.`tagname` AND pbc.`catid` = pb.`refid`
			LEFT JOIN %project_tr% oj ON oj.`refcode` = pb.`trid`
		--	LEFT JOIN %tag% pg ON pg.`taggroup` = CONCAT("project:problem:",tt.`refid`) AND pbc.`catid` = tr.`refid`
		%WHERE%
		GROUP BY `problemName`
		ORDER BY
			CASE
				WHEN pb.`tagname` IS NOT NULL THEN 0
				ELSE 99
			END ASC,
			pb.`tagname` ASC,
			pb.`refid` ASC,
			`problemName` ASC
	';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;


	$tables = new Table();
	$tables->thead = array(
		'สถานการณ์',
		'problemsize -amt' => 'ค่าเฉลี่ยขนาดปัญหา',
		'targetsize -amt' => 'ค่าเฉลี่ยเป้าหมาย 1 ปี',
	);

	$exports->numrows = $dbs->count();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->problemName,
			number_format($rs->problemSizeAvg,2),
			is_null($rs->targetSizeAvg) ? '' : number_format($rs->targetSizeAvg,2),
		);
	}
	$ret .= '<div style="width:100%; overflow:scroll;">'.$tables->build().'</div>';

	//$ret .= print_o($dbs, '$dbs');


	$ret.='<script type="text/javascript">
	$("body").on("change","#condition select,#condition input", function() {
		var $this=$(this);
		if ($this.attr("name")=="region") {
			$("#province").val("");
		}
		if ($this.attr("name")=="prov") {
		}
		notify("LOADING");
		$(this).closest("form").submit();
	});
	</script>';
	return $ret;
}
?>