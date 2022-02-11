<?php
/**
* Project :: Main Page
* Created 2018-12-11
* Modify  2020-06-10
*
* @param Object $self
* @return String
*/

$debug = true;

function project_home($self) {
	$colors=array('#3366CC','#DC3912','#FF9900','#109618','#990099','#0099C6','#DD4477');
	// Graph
	$graph = array(
		'type-project'=>array(),
		'type-budget'=>array(),
		'prov-project'=>array(),
		'prov-budget'=>array(),
	);

	R::View('project.toolbar', $self, 'ระบบบริหารโครงการกองทุนสุขภาพตำบล');

	$stmt = 'SELECT
			a.`supporttype`
			, IFNULL(st.`name`,"ไม่ระบุ") `supporttypeName`
			, SUM(a.`totalProject`) `totalProject`
			, SUM(a.`totalBudget`) `totalBudget`
		FROM (
			SELECT supporttype,COUNT(*) `totalProject` , SUM(`budget`) `totalBudget`
			FROM `sgz_project` p
			WHERE p.`prtype` = "โครงการ"
			GROUP BY `supporttype`
		) a
		LEFT JOIN `sgz_tag` st ON st.`taggroup` = "project:supporttype" AND st.`catid` = a.`supporttype`
		GROUP BY supporttypeName;
		-- {sum:"totalProject,totalBudget"}
	';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;



	$tables = new Table();
	$tables->thead=array('','ประเภท','amt -project'=>'โครงการ','money -budget'=>'งบประมาณ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<span class="color" style="background-color:'.$colors[$no++].'"></span>',
			$rs->supporttypeName,
			number_format($rs->totalProject),
			number_format($rs->totalBudget,2)
		);

		$supporttypeName=$rs->supporttype?'ประเภท '.$rs->supporttype:'ไม่ระบุ';
		$graph['type-project'][]=array($supporttypeName,round($rs->totalProject));
		$graph['type-budget'][]=array($supporttypeName,round($rs->totalBudget));
	}
	$tables->tfoot[]=array('','รวม',number_format($dbs->sum->totalProject),number_format($dbs->sum->totalBudget,2));
	
	//$ret.='<div class=""><div id="mainact-project" class="graph">กราฟแสดงจำนวนโครงการแต่ละกิจกรรมหลัก</div><div id="mainact-budget" class="graph">กราฟแสดงงบประมาณแต่ละกิจกรรมหลัก</div>';

	$ret.='<div class="project-type"><div id="type-project" class="graph">กราฟแสดงจำนวนโครงการจำแนกตามประเภท</div><div id="type-budget" class="graph">กราฟแสดงงบประมาณจำแนกตามประเภท</div>';
	$ret.=$tables->build();
	$ret.='</div>';

	$stmt='SELECT
			t.`changwat`
		, IFNULL(cop.`provname`,"ไม่ระบุ") `changwatName`
		, COUNT(*) `totalProject`
		, SUM(`budget`) `totalBudget`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f ON f.`fundid`=o.`shortname`
			LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat`
		WHERE p.`prtype`="โครงการ" AND f.`fundid` IS NOT NULL
		GROUP BY `changwatName`;
		-- {sum:"totalProject,totalBudget"}';

	$dbs=mydb::select($stmt);

	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead=array('','จังหวัด','amt -project'=>'โครงการ','money -budget'=>'งบประมาณ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<span class="color" style="background-color:'.$colors[$no++].'"></span>',
			$rs->changwatName,
			number_format($rs->totalProject),
			number_format($rs->totalBudget,2)
		);

		$graph['prov-project'][]=array($rs->changwatName,round($rs->totalProject));
		$graph['prov-budget'][]=array($rs->changwatName,round($rs->totalBudget));
	}
	$tables->tfoot[]=array('','รวม',number_format($dbs->sum->totalProject),number_format($dbs->sum->totalBudget,2));

	$ret.='<div class="project-province"><div id="prov-project" class="graph">กราฟแสดงจำนวนโครงการจำแนกตามจังหวัด</div><div id="prov-budget" class="graph">กราฟแสดงงบประมาณจำแนกตามจังหวัด</div>';
	$ret.=$tables->build();
	$ret.='</div>'._NL;


	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret.='
	<script type="text/javascript">
		var chartData='.json_encode($graph).'
		var chartType="pie";
		google.charts.load("current", {"packages":["corechart"]});
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {
			var options = {
				vAxis: {
					viewWindowMode: "explicit",
					//viewWindow: {max: '.$maxStudent.'}, 
				},
			};

			if (chartType=="pie") {
				var options = {
					legend: {position: "labeled"},
					chartArea: {width:"100%",height:"80%"},
					pieSliceText: "label",
					pieHole:0.4,
				};
			}
			$.each(chartData,function(i,eachChartData) {
				console.log(i)
				options.title=$("#"+i).text();
				var data = new google.visualization.DataTable();
				data.addColumn("string", "จังหวัด");
				data.addColumn("number", "จำนวน");
				data.addRows(eachChartData);

				var chart = new google.visualization.PieChart(document.getElementById(i));
				if (chartType=="line") {
					chart = new google.visualization.LineChart(document.getElementById(i));
				} else if (chartType=="bar") {
					chart = new google.visualization.BarChart(document.getElementById(i));
				} else if (chartType=="col") {
					chart = new google.visualization.ColumnChart(document.getElementById(i));
				} else if (chartType=="pie") {
					chart = new google.visualization.PieChart(document.getElementById(i));
				}
				chart.draw(data, options);
			});
		}
		$(document).on("click", ".toolbar.-graphtype a", function() {
			var $this=$(this);
			chartType=$this.attr("href").substring(1);
			//notify("chartType="+chartType);
			$(".toolbar.-graphtype a").removeClass("active");
			$this.addClass("active");
			drawChart();
			return false;
		});
		</script>
	';

	$ret.='<style type="text/css">
	.graph {width:100%; height:400px; margin:10px 0; background-color:#eee; float:left;}
	.color {display:inline-block;width:16px;height:16px;}
	@media (min-width:40em){    /* 640/16 = 40 */
		.project-type {width:49%; float:left; margin:0 0 40px 0;}
		.project-province {width:49%; float:right; margin:0 0 40px 2%;}
	}
	</style>';
	return $ret;
}
?>