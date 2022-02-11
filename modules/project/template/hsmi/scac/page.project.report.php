<?php

/**
 * Send Document Report
 *
 */
function project_report($self) {
	R::View('project.toolbar',$self,'วิเคราะห์');

	$isAccessActivityExpense=user_access('access activity expense') || $isOwner;

	$menu.='<h3>การเงิน</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/expplan').'">งบประมาณ/การจ่ายเงินจำแนกตามแผนงาน</a>');
	$ui->add('<a href="'.url('project/report/expgroup').'">งบประมาณ/การจ่ายเงินจำแนกตามพื้นที่</a>');
	if ($isAccessActivityExpense) {
		$ui->add('<a href="'.url('project/report/exptran').'">บันทึกการจ่ายเงิน</a>');
	}
	$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));

	$menu.='<h3>การประเมินคุณค่า</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/valuation').'">แผนภูมิการประเมินคุณค่า</a>');
	$ui->add('<a href="'.url('project/report/goodproject').'">แผนที่โครงการดี ๆ จากการประเมินคุณค่า</a>');
	$ui->add('<a href="'.url('project/report/map/issue').'">แผนที่ประเด็นโครงการ</a>');
	//$ui->add('<a href="'.url('project/report/inno').'">การเกิดขึ้นของนวัตกรรม</a>');
	$ui->add('<a href="'.url('project/report/abstract').'">บทคัดย่อโครงการ</a>');
	$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));

	$menu.='<h3>เครือข่าย</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/networkbyissue').'">เครือข่ายตามประเด็น</a>');
	$ui->add('<a href="'.url('project/report/networklist').'">รายชื่อเครือข่าย</a>');
	$ui->add('<a href="'.url('project/report/leader').'">รายชื่อแกนนำในพื้นที่</a>');
	$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));


	$menu.='<br clear="all" />';
	$menu.='<h3>พัฒนาโครงการ</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/develop').'">สถานะโครงการพัฒนา</a>');
	$ui->add('<a href="'.url('project/develop/list').'">รายชื่อโครงการพัฒนา</a>');
	$ui->add('<a href="'.url('project/develop/report/expgroup').'">รายงานงบประมาณโครงการพัฒนาแยกตามหมวด</a>');
	$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));


	$menu.='<br clear="all" />';
	$menu.='<h3>รายงานอื่น ๆ</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/name').'">รายชื่อโครงการ</a>');
	$ui->add('<a href="'.url('project/report/m1late').'">โครงการส่งรายงาน ง.1 ล่าช้า</a>');
	$ui->add('<a href="'.url('project/activity','o=modify&i=20').'">รายงานกิจกรรมที่แก้ไขล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=date1&i=20').'">รายงานกิจกรรมที่เกิดขึ้นล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=trid&i=20').'">รายงานกิจกรรมที่ส่งมาล่าสุด</a>');
	//$ui->add('<a href="'.url('project/report/s1ready').'">โครงการที่สร้างรายงาน ส.1 แล้ว</a>');
	//$ui->add('<a href="'.url('project/report/s2ready').'">โครงการที่สร้างรายงาน ส.2 แล้ว</a>');
	//$ui->add('<a href="'.url('project/report/m1ready').'">โครงการที่สร้างรายงาน ง.1 แล้ว</a>');
	$ui->add('<a href="'.url('project/report/estimationready').'">โครงการที่สร้างรายงานประเมินแล้ว</a>');
	$ui->add('<a href="'.url('project/report/followready').'">โครงการที่สร้างรายงานติดตามแล้ว</a>');
	if (projectcfg::enable('trainer')) $ui->add('<a href="'.url('project/report/trainer').'" title="รายชื่อพี่เลี้ยง">รายชื่อพี่เลี้ยง</a>');
	$ui->add('<a href="'.url('project/report/owner').'" title="รายชื่อผู้รับผิดชอบโครงการ">รายชื่อผู้รับผิดชอบโครงการ</a>');
	$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));

	if (user_access('administer projects')) {
		$menu.='<br clear="all" />';
		$menu.='<h3>รายงานผู้จัดการระบบ</h3>';
		$ui=new ui();
		$menu.=$ui->build('ul',array('class'=>'card -menu -project-report-menu'));
	}

	$self->theme->sidebar=$menu;

	// Graph
	$graph=array(
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

	$stmt='SELECT `pryear`,COUNT(*) `totals`, SUM(`budget`) `budgets` FROM %project% WHERE `prtype`="โครงการ" GROUP BY `pryear`';
	$dbs=mydb::select($stmt);

	$ret.='<div class="project-report-section -yearly">';
	$ret.='<h3>โครงการ</h3>';
	$ret.='<div id="graph-yearly" class="graph-section"></div>';
	foreach ($dbs->items as $rs) {
		$graph['yearly']['data'][]=array(($rs->pryear+543).'',round($rs->totals));
		$graph['budget']['data'][]=array(($rs->pryear+543).'',round($rs->budgets));
	}
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div>';

	$ret.='<div class="project-report-section -budget">';
	$ret.='<h3>งบประมาณ</h3>';
	$ret.='<div id="graph-budget" class="graph-section"></div>';
	$ret.='</div>';
	//$ret.=print_o($graph,'$graph');


	$ret.='<style type="text/css">
	.sidebar {width:300px;}
	#main.main--withsidebar {margin-left:320px;}
	.page.-main {display: flex; flex-wrap: wrap; justify-content: space-between;}
	.project-report-section {width: 48%; margin: 32px 0 32px 0; padding:0; box-shadow: 2px 2px 10px #ccc;}
	.project-report-section>h3 {text-align: center;}

	.project-summary {width: 100%; padding:10px;background:#058DC7; color:#fff;}
	.project-summary>div {width:33%; display:inline-block;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.8em; line-height:2em;}
	.graph-section {width:100%; height:320px;}
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
?>