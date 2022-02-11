<?php

/**
 * Send Document Report
 *
 */
function project_report_issue($self) {
	R::View('project.toolbar', $self, 'รายงานประเด็นหลัก', 'report');

	if (post('id')) return __project_report_issue_list(post('id'));

	// Graph
	$graph=array(
		'category'=>array(
			'type'=>'col',
			'cols'=>array(array('string','ประเด็น'),array('number','โครงการ')),
		),
		'catbudget'=>array(
			'type'=>'col',
			'cols'=>array(array('string','ประเด็น'),array('number','งบประมาณ')),
		),
		'yearly'=>array(
			'type'=>'col',
			'cols'=>array(array('string','พ.ศ.'),array('number','โครงการ')),
		),
		'budget'=>array(
			'type'=>'col',
			'cols'=>array(array('string','พ.ศ.'),array('number','งบประมาณ')),
		),
	);

	// Show summary report
	$thisYearProjects=mydb::select('SELECT COUNT(*) `thisYearProjects` FROM %project% WHERE `prtype`="โครงการ" AND `pryear`=YEAR(CURDATE()) LIMIT 1')->thisYearProjects;
	$lastYearProjects=mydb::select('SELECT COUNT(*) `lastYearProjects` FROM %project% WHERE `prtype`="โครงการ" AND `pryear`=YEAR(CURDATE())-1 LIMIT 1')->lastYearProjects;
	$totalProjects=mydb::select('SELECT COUNT(*) `totalProjects` FROM %project% WHERE `prtype`="โครงการ" LIMIT 1')->totalProjects;

	$ret.='<div class="project-summary">';
	$ret.='<div class="thisyearprojects"><span>โครงการปีนี้</span><span class="itemvalue">'.$thisYearProjects.'</span><span>โครงการ​</span></div>';
	$ret.='<div class="lastyearprojects"><span>โครงการปีที่แล้ว</span><span class="itemvalue">'.$lastYearProjects.'</span><span>โครงการ​</span></div>';
	$ret.='<div class="totalprojects"><span>โครงการทั้งหมด</span><span class="itemvalue">'.$totalProjects.'</span><span>โครงการ​</span></div>';
	$ret.='</div>';

	$stmt='SELECT p.`pryear`,COUNT(*) `totals`, SUM(p.`budget`) `budgets`, `category`, tg.`name` `categoryName`
				FROM %project% p
					LEFT JOIN %project_dev% d USING(`tpid`)
					LEFT JOIN %tag% tg ON tg.`taggroup`="project:category" AND tg.`catid`=d.`category`
				WHERE `prtype`="โครงการ" AND d.`category` IS NOT NULL
				GROUP BY d.`category`
				ORDER BY `totals` DESC';
	$dbs=mydb::select($stmt);
	$categoryTable = new Table();
	$categoryTable->addClass('-category');
	$categoryTable->thead=array('ประเด็นหลัก','amt'=>'จำนวนโครงการ','money'=>'งบประมาณ(บาท)');
	foreach ($dbs->items as $rs) {
		$categoryName=SG\getFirst($rs->categoryName,'N/A');
		$graph['category']['data'][]=array($categoryName,round($rs->totals));
		$graph['catbudget']['data'][]=array($categoryName,round($rs->budgets));
		$categoryTable->rows[]=array('<a class="sg-action" href="'.url('project/report/issue',array('id'=>$rs->category)).'" data-rel="box">'.$categoryName.'</a>',$rs->totals,number_format($rs->budgets,2));
	}

	//$ret.=print_o($dbs,'$dbs');


	$stmt='SELECT `pryear`,COUNT(*) `totals`, SUM(`budget`) `budgets` FROM %project% WHERE `prtype`="โครงการ" GROUP BY `pryear`';
	$dbs=mydb::select($stmt);
	foreach ($dbs->items as $rs) {
		$graph['yearly']['data'][]=array(($rs->pryear+543).'',round($rs->totals));
		$graph['budget']['data'][]=array(($rs->pryear+543).'',round($rs->budgets));
	}

	$ret.=$categoryTable->build();

	$ret.='<div class="project-report-section -category">';
	$ret.='<h3>โครงการ</h3>';
	$ret.='<div id="graph-category" class="graph-section"></div>';
	$ret.='</div>';

	$ret.='<div class="project-report-section -catbudget">';
	$ret.='<h3>งบประมาณ</h3>';
	$ret.='<div id="graph-catbudget" class="graph-section"></div>';
	$ret.='</div>';


	$ret.='<div class="project-report-section -yearly">';
	$ret.='<h3>โครงการ</h3>';
	$ret.='<div id="graph-yearly" class="graph-section"></div>';
	$ret.='</div>';

	$ret.='<div class="project-report-section -budget">';
	$ret.='<h3>งบประมาณ</h3>';
	$ret.='<div id="graph-budget" class="graph-section"></div>';
	$ret.='</div>';

	//$ret.=print_o($graph,'$graph');


	$ret.='<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary>div {width:33%; display:inline-block;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.8em; line-height:2em;}
	.project-report-section {margin: 16px; padding:8px; float: left; box-shadow: 2px 2px 10px #ccc;}
	.graph-section {width:480px; height:320px;}
	.item.-category {width: 360px; float:left;}
	</style>';

	
	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret.='
	<script type="text/javascript">
		var chartGroup='.json_encode($graph).'
		var chartType="col";
		google.charts.load("current", {"packages":["corechart"]});
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {
			var options = {
											vAxis: {
												viewWindowMode: "explicit",
												/* viewWindow: {max: '.$maxStudent.'}, */
											},
											legend: {position: "none"},
										};

			if (chartType=="pie") {
				var options = {
												legend: {position: "none"},
												chartArea: {width:"100%",height:"100%"},
											};
			}
			$.each(chartGroup,function(i,chartInfo) {
				var data = new google.visualization.DataTable();
				//data.addColumn("string", "น้ำหนักตามเกณฑ์ส่วนสูง");
				//data.addColumn("number", "จำนวนคน");
				$.each(chartInfo.cols,function(i,colItem) {
					console.log("colItem="+colItem[0]+colItem[1]);
					data.addColumn(colItem[0], colItem[1]);
				});
				//alert(chartInfo.data)
				data.addRows(chartInfo.data);

				var chart = new google.visualization.PieChart(document.getElementById("graph-"+i));
				if (chartType=="line") {
					chart = new google.visualization.LineChart(document.getElementById("graph-"+i));
				} else if (chartType=="bar") {
					chart = new google.visualization.BarChart(document.getElementById("graph-"+i));
				} else if (chartType=="col") {
					chart = new google.visualization.ColumnChart(document.getElementById("graph-"+i));
				} else if (chartType=="pie") {
					chart = new google.visualization.PieChart(document.getElementById("graph-"+i));
				}
				chart.draw(data, options);
			});
		}
	</script>
	';
	
	return $ret;
}

function __project_report_issue_list($catid) {
	$where=array();
	$where=sg::add_condition($where,'`prtype`="โครงการ"');
	if ($catid) $where=sg::add_condition($where,'d.`category`=:catid','catid',$catid);
	$stmt='SELECT t.`tpid`,t.`title`, p.`pryear`, p.`budget`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %project_dev% d USING(`tpid`)
					LEFT JOIN %tag% tg ON tg.`taggroup`="project:category" AND tg.`catid`=d.`category`'
					.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				';
	$dbs=mydb::select($stmt,$where['value']);
	$tables = new Table();
	$tables->thead=array('no'=>'','ปี พ.ศ.','โครงการ','money'=>'งบประมาณ(บาท)');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(++$no,$rs->pryear+543,'<a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',number_format($rs->budget,2));
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>