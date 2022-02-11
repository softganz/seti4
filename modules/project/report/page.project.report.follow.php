<?php
/**
* Project Follow Summary Report
* Created 2020-05-27
* Modify  2020-05-27
*
* @param Object $self
* @return String
*/

$debug = true;

function project_report_follow($self) {
	R::View('project.toolbar', $self, 'รายงานติดตามโครงการ', 'report');

	$getChangwat = post('p');
	$getAmpur = post('a');
	$getTambon = post('t');
	$getReportType = SG\getFirst(post('r'),'changwat');

	if ($getChangwat == '*') $getChangwat = '';


	$isAdmin = user_access('administer projects');



	$orderList = [
		'na' => 'ชื่อโครงการ:title',
		'cd' => 'วันที่ป้อน:t.created',
		'label' => 'ป้ายรายงาน:label',
	];

	$selectProvince = mydb::select(
		'SELECT LEFT(t.`areacode`, 2) `changwat`, `provname` `name`
		FROM %project% p
			LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
		WHERE `prtype` = "โครงการ" AND `provname` IS NOT NULL
		GROUP BY `changwat`
		ORDER BY CONVERT(`name` USING tis620);
		-- {key: "changwat", value: "name"}'
	)->items;

	$selectSet = mydb::select(
		'SELECT `tpid` `projectId`, `title`
		FROM %project% p
			LEFT JOIN %topic% USING(`tpid`)
		WHERE `prtype` = "ชุดโครงการ"
		ORDER BY CONVERT(`title` USING tis620);
		-- {key: "projectId", value: "title"}'
	)->items;

	$selectYear = mydb::select(
		'SELECT p.`pryear`, CONCAT("พ.ศ.", p.`pryear` + 543) `bcYear`
		FROM %project% p
		WHERE `prtype` = "โครงการ" AND `pryear` IS NOT NULL
		GROUP BY `pryear`
		ORDER BY `pryear` DESC;
		-- {key: "pryear", value: "bcYear"}'
	)->items;


	$selectNew = ['โครงการใหม่' => 'โครงการใหม่', 'โครงการต่อเนื่อง' => 'โครงการต่อเนื่อง'];

	$orgTypeCategory = R::Model('category.get','project:orgtype', 'catid');

	$goal10yrCategory = R::Model('category.get','project:goal10yr', 'catid');

	$goal3yrCategory = R::Model('category.get','project:goal3yr', 'catid');

	$mt5Category = R::Model('category.get','project:mt5', 'catid');

	$issueCategory = R::Model('category.get','project:issue', 'catid');

	$targetgroupCategory = R::Model('category.get','project:target', 'catid');

	$reportTypeArray = [
		'changwat' => ['text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince],
		'set' => ['text' => 'ชุดโครงการ', 'filter' => 'for_set', 'select' => $selectSet],
		'year' => ['text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear],
		'new' => ['text' => 'ต่อเนื่อง', 'filter' => 'for_new', 'select' => $selectNew],
		'org' => $orgTypeCategory ? ['text' => 'องค์กร', 'filter' => 'for_org', 'select' => $orgTypeCategory] : NULL,
		'goal10yr' => $goal10yrCategory ? ['text' => 'เป้าหมาย 10 ปี', 'filter' => 'for_goal10yr', 'select' => $goal10yrCategory] : NULL,
		'goal3yr' => $goal3yrCategory ? ['text' => 'เป้าหมาย 3 ปี', 'filter' => 'for_goal3yr', 'select' => $goal3yrCategory] : NULL,
		'mt5' => $mt5Category ? ['text' => 'มาตรา 5', 'filter' => 'for_mt5', 'select' => $mt5Category] : '',
		'issue' => $issueCategory ? ['text' => 'ประเด็น', 'filter' => 'for_issue', 'select' => $issueCategory] : NULL,
		'targetgroup' => $targetgroupCategory ? ['text' => 'กลุ่มเป้าหมาย', 'filter' => 'for_targetgroup', 'select' => $targetgroupCategory] : NULL,
	];


	$toolbar = new Report(url('project/api/follow.summary'), 'projet-report-follow');

	$toolbar->addId('projet-report-follow');
	$toolbar->addConfig('showArrowLeft', true);
	$toolbar->addConfig('showArrowRight', true);

	$toolbar->Filter('changwat', Array('group' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince, 'active' => true));
	$toolbar->Filter('set', Array('group' => 'ชุดโครงการ', 'filter' => 'for_set', 'select' => $selectSet));
	$toolbar->Filter('year', Array('group' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear));
	$toolbar->Filter('new', Array('group' => 'ต่อเนื่อง', 'filter' => 'for_new', 'select' => $selectNew));
	if ($orgTypeCategory) $toolbar->Filter('org', Array('group' => 'องค์กร', 'filter' => 'for_org', 'select' => $orgTypeCategory));
	if ($goal10yrCategory) $toolbar->Filter('goal10yr', Array('group' => 'เป้าหมาย 10 ปี', 'filter' => 'for_goal10yr', 'select' => $goal10yrCategory));
	if ($goal3yrCategory) $toolbar->Filter('goal3yr', Array('group' => 'เป้าหมาย 3 ปี', 'filter' => 'for_goal3yr', 'select' => $goal3yrCategory));
	if ($mt5Category) $toolbar->Filter('mt5', Array('group' => 'มาตรา 5', 'filter' => 'for_mt5', 'select' => $mt5Category));
	if ($issueCategory) $toolbar->Filter('issue', Array('group' => 'ประเด็น', 'filter' => 'for_issue', 'select' => $issueCategory));
	if ($targetgroupCategory) $toolbar->Filter('targetgroup', Array('group' => 'กลุ่มเป้าหมาย', 'filter' => 'for_targetgroup', 'select' => $targetgroupCategory));

	$toolbar->Output('chart', '<div class="loader -rotate" style="width: 128px; height: 128px; margin: 0 auto; display: block;"></div>');
	$toolbar->Output('chart-hide','<nav class="nav -page -sg-text-center"><a class="btn -link" onClick="$(\'#report-output-chart\').toggle()" title="Hide Chart"><i class="icon -material">expand_less</i><span class="-hidden">Hide Chart</span></a></nav>');
	$toolbar->Output('summary');
	$toolbar->Output('items');

	$toolbar->optionsUi->add('<button value="Pie" class="btn -link -submit -graph" title="Pie Chart"><i class="icon -material">pie_chart</i><span class="-hidden">Pie</span></button> '
		. '<button value="Bar" class="btn -link -submit -graph" title="Bar Chart"><i class="icon -material">sort</i><span class="-hidden">Bar</span></button> '
		. '<button value="Col" class="btn -link -submit -graph" title="Column Chart"><i class="icon -material">bar_chart</i><span class="-hidden">Col</span></button> '
		. '<button value="Line" class="btn -link -submit -graph" title="Line Chart"><i class="icon -material">show_chart</i><span class="-hidden">Line</span></button>'
	);

	$orderByOption = '';
	foreach ($orderList as $k=>$v) $orderByOption .= '<option value="'.$k.'">'.substr($v,0,strpos($v,':')).'</option>';

	$toolbar->optionsUi->add(
		'<input class="-submit" type="checkbox" name="incna" value="yes" checked="checked" /> รวมไม่ระบุ '
		. '<input class="-submit" type="checkbox" name="detail" value="yes" /> แสดงชื่อโครงการ '
		. '<select class="form-select" name="o"><option>--เรียงตาม--</option>'
		. $orderByOption
		. '</select>'
		. (user_access('access debugging program') ? '<input type="checkbox" name="debug" value="yes" /> Debug' : '')
	);

	$ret .= $toolbar->build();


	head('jsapi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');
	//head('table2excel','<script type="text/javascript" src="/js/jquery.table2excel.js"></script>');
	//head('table2excel','<script type="text/javascript" src="/js/js.export.js"></script>');

	// https://github.com/SheetJS/sheetjs
	// https://www.jqueryscript.net/table/Exporting-Html-Tables-To-CSV-XLS-XLSX-Text-TableExport.html
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.15.1/xlsx.core.min.js"></script>');
	//head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/1.0.20150320/Blob.js"></script>');
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>');
	head('<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/4.0.11/js/tableexport.min.js"></script>');

	$ret .= '
	<style type="text/css">
		table.report-summary {width:100%;}
		#report-output-chart {width:100%;height:400px; background: transparent;}
		table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
		table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
		#detail-list>tbody>tr>td:nth-child(n+2) {text-align: center;}
	</style>

	<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"], callback: drawChart});

		//google.setOnLoadCallback(drawChart);
		function drawChart() {
			var $sgDrawReport = $(".btn.-primary.-submit").sgDrawReport().doAction()
			/*
			let $form = $("#report-form")
			$form.attr("action", $form.data("query")).submit().attr("action", "")
			*/
		}

	function export2excel() {
		console.log("EXPORT")
		$("#detail-list").tableExport({headers: true, formats: ["xlsx", "csv", "txt"], position: "top", exportButtons: true});
	}

	</script>';

	return $ret;
}
?>