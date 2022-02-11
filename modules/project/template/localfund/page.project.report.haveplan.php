<?php
function project_report_haveplan($self) {
	$prov=post('prov');
	$ampur=post('ampur');

	project_model::set_toolbar($self,'รายงานการจัดทำแผนงานของกองทุน');

	$provList=mydb::select('SELECT DISTINCT `changwat`,`namechangwat` FROM %project_fund%');

	// Graph
	$graph=array(
						'prov-project'=>array(),'prov-budget'=>array(),'prov-pop'=>array(),'prov-fund'=>array(),'type-project'=>array(),
						'type-budget'=>array(),
						);

	$ret.='<div class="toolbar"><ul>';
	$ret.='<li><a class="" href="'.url('project/report/haveplan').'">ทุกจังหวัด</a></li>';
	foreach ($provList->items as $rs) {
		$ret.='<li><a class="" href="'.url('project/report/haveplan',array('prov'=>$rs->changwat)).'">'.$rs->namechangwat.'</a></li>';
	}
	$ret.='</ul></div>';

	if (0 && $prov) {
		$where=array();
		if ($prov) $where=sg::add_condition($where,'f.`changwat`=:prov','prov',$prov);

		$stmt='SELECT f.*,o.`orgid`, o.`name`, o.`shortname`, COUNT(t.`tpid`) `totalProject`
						FROM %project_fund% f
							LEFT JOIN %db_org% o ON o.`shortname`=f.`fundid`
							LEFT JOIN %topic% t USING(`orgid`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY f.`fundid`
					ORDER BY f.`changwat` ASC, f.`ampur` ASC, CONVERT(`fundname` USING tis620) ASC';
		$dbs=mydb::select($stmt,$where['value']);

		$tables=new table('item -centerx');
		$tables->thead=array('ชื่อกองทุน','รหัสกองทุน','อำเภอ','จังหวัด','money openbalance'=>'ยอดยกมา','amt population'=>'จำนวนประชากร(คน)','amt'=>'โครงการ','');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->name.'</a>',$rs->shortname,
				$rs->nameampur,
				$rs->namechangwat,
				number_format($rs->openbalance,2),
				number_format($rs->population),
				$rs->totalProject?number_format($rs->totalProject):'-',
				$rs->totalProject?'<a href="'.url('project/fund/'.$rs->orgid.'/follow').'">รายชื่อโครงการ</a>':'',
			);
		}
		$ret.=$tables->build();
		//$ret.=print_o($dbs,'$dbs');
	} else {
		$where=array();
		if ($prov) $where=sg::add_condition($where,'f.`changwat`=:prov','prov',$prov);
		if ($ampur) $where=sg::add_condition($where,'f.`ampur`=:ampur','ampur',$ampur);

		if ($ampur) $labelby='tambon';
		else if ($prov) $labelby='ampur';
		else $labelby='prov';

		$labelList=array(
								'prov'=>'`changwat` `label`, `namechangwat` `labelname`',
								'ampur'=>'`ampur` `label`, `nameampur` `labelname`',
								'tambon'=>'`fundname` `label`, `fundname` `labelname`',
								);
		//$ret.='$prov='.$prov.' $labelby='.$labelby;
		$stmt='SELECT
						'.$labelList[$labelby].'
						, COUNT(IF(tr.`detail1`=1,1,NULL)) `betweenPlan`
						, COUNT(IF(tr.`detail1`=2,1,NULL)) `havePlan`
						, COUNT(IF(tr.`detail1`=-1,1,NULL)) `noPlan`
						, COUNT(IF(tr.`detail1` IS NULL,1,NULL)) `noInput`
						, COUNT(f.`fundid`) `totalFund`
						, YEAR(tr.`date1`) `year`
						FROM %project_fund% f
							LEFT JOIN %project_tr% tr ON tr.`formid`="population" AND tr.`part`=f.`fundid` AND YEAR(tr.`date1`)=2017
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY `label`;
						-- {key:"label",sum:"totalFund,betweenPlan,havePlan,noPlan,noInput"}';
		$dbs=mydb::select($stmt,$where['value']);

		$tables = new Table();
		$tables->thead=array('พื้นที่','amt -fund'=>'จำนวนกองทุน','amt -between'=>'กำลังทำแผน','amt -have'=>'มีแผนงาน','amt -no'=>'ไม่มีแผนงาน','amt -noinput'=>'ไม่บันทึก');
		foreach ($dbs->items as $rs) {
			$changwatProject=$projects->items[$rs->changwat]->totalProject;
			$tables->rows[]=array(
												$ampur?$rs->label:'<a href="'.url('project/report/haveplan',array('prov'=>SG\getFirst($prov,$rs->label),'ampur'=>$prov?$rs->label:NULL)).'">'.$rs->labelname.'</a>',
												$rs->totalFund,
												$rs->betweenPlan.' <span class="percent">('.number_format(100*$rs->betweenPlan/$rs->totalFund).'%)</span>',
												$rs->havePlan.' <span class="percent">('.number_format(100*$rs->havePlan/$rs->totalFund).'%)</span>',
												$rs->noPlan.' <span class="percent">('.number_format(100*$rs->noPlan/$rs->totalFund).'%)</span>',
												$rs->noInput.' <span class="percent">('.number_format(100*$rs->noInput/$rs->totalFund).'%)</span>',
												);
			$totalProject+=$changwatProject;
			$graph['prov-project'][]=array($rs->namechangwat,round($changwatProject));
			$graph['prov-pop'][]=array($rs->namechangwat,round($rs->totalPopulation));
			$graph['prov-fund'][]=array($rs->namechangwat,round($rs->totalFund));
			$graph['prov-budget'][]=array($rs->namechangwat,round($projects->items[$rs->changwat]->totalBudget));
		}
		$tables->tfoot[]=array(
											'รวม',
											number_format($dbs->sum->totalFund),
											number_format($dbs->sum->betweenPlan)
											.' <span class="percent">('.number_format(100*$dbs->sum->betweenPlan/$dbs->sum->totalFund).'%)</span>',
											number_format($dbs->sum->havePlan)
											.' <span class="percent">('.number_format(100*$dbs->sum->havePlan/$dbs->sum->totalFund).'%)</span>',
											number_format($dbs->sum->noPlan)
											.' <span class="percent">('.number_format(100*$dbs->sum->noPlan/$dbs->sum->totalFund).'%)</span>',
											number_format($dbs->sum->noInput)
											.' <span class="percent">('.number_format(100*$dbs->sum->noInput/$dbs->sum->totalFund).'%)</span>',
											);

		$ret.='<div class="">';
		//$ret.='<div id="prov-project" class="graph">กราฟแสดงจำนวนโครงการแต่ละจังหวัด</div><div id="prov-budget" class="graph">กราฟแสดงงบประมาณแต่ละจังหวัด</div><div id="prov-pop" class="graph">กราฟแสดงจำนวนประชากรแต่ละจังหวัด</div><div id="prov-fund" class="graph">กราฟแสดงจำนวนกองทุนแต่ละจังหวัด</div>';
		$ret.=$tables->build();
		//$ret.=print_o($dbs,'$dbs');
		$ret.='</div>';


	}

	//$ret.=print_o($graph,'$graph');


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
												legend: {position: "none"},
												chartArea: {width:"100%",height:"80%"},
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
	.percent {font-size:0.9em;color:#999;}
	.graph {width:49%; height:200px; margin:10px 0.5%; background-color:#eee; float:left;">
	</style>';
	return $ret;
}

?>