<?php
/**
* Project :: Report Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/report
*/

$debug = true;

class ProjectReport extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'วิเคราะห์',
			]),
			'sideBar' => new Container([
				'tagName' => 'nav',
				'class' => 'navbar -no-print',
				'children' => [
					R::View('project.report.inc'),
					'<header class="header"><h3>ติดตามโครงการ</h3></header>',
					new Ui([
						'type' => 'menu',
						'class'=> 'project-report-menu',
						'children' => [
							'<a class="-new" href="'.url('project/report/follow').'">ภาพรวมติดตามโครงการ</a>',
							'<a href="'.url('project/report/name').'">รายชื่อโครงการ</a>',
							'<h4>การเงิน</h4>',
							'<a href="'.url('project/report/expplan').'">งบประมาณ/การจ่ายเงินจำแนกตามแผนงาน</a>',
							'<a href="'.url('project/report/expgroup').'">งบประมาณ/การจ่ายเงินจำแนกตามพื้นที่</a>',
							'<a href="'.url('project/report/exptran').'">บันทึกการจ่ายเงิน</a>',
							'<h4>ผลลัพธ์</h4>',
							'<a href="'.url('project/report/inno').'">การเกิดขึ้นของนวัตกรรม</a>',
							'<a href="'.url('project/report/valuation').'">การประเมินคุณค่า(แผนภูมิ)</a>',
							'<a href="'.url('project/report/goodproject').'">การประเมินคุณค่า(แผนที่)</a>',
							'<a href="'.url('project/report/issue').'">รายงานประเด็นหลัก</a>',
							'<a href="'.url('project/report/yearresult').'">รายงานผลลัพธ์โครงการ</a>',
							'<a href="'.url('project/report/leader').'">รายชื่อแกนนำในพื้นที่</a>',
						], // children
					]), // Ui

					'<header class="header"><h3>พัฒนาโครงการ</h3></header>',
					new Ui([
						'type' => 'menu',
						'class' => 'project-report-menu',
						'children' => [
							'<a href="'.url('project/develop').'">สถานะโครงการพัฒนา</a>',
							'<a href="'.url('project/develop/list').'">รายชื่อโครงการพัฒนา</a>',
							'<a href="'.url('project/develop/report/expgroup').'">รายงานงบประมาณโครงการพัฒนาแยกตามหมวด</a>',
						], // children
					]), // Ui


					'<header class="header"><h3>อื่น ๆ</h3></header>',
					new Ui([
						'type' => 'menu',
						'class' => 'project-report-menu',
						'children' => [
							projectcfg::enable('trainer') ? '<a href="'.url('project/report/trainer').'" title="รายชื่อพี่เลี้ยง">รายชื่อพี่เลี้ยง</a>' : NULL,
							'<a href="'.url('project/report/owner').'" title="รายชื่อผู้รับผิดชอบโครงการ">รายชื่อผู้รับผิดชอบโครงการ</a>',
							'<a href="'.url('project/report/m1late').'">โครงการส่งรายงาน ง.1 ล่าช้า</a>',
							'<a href="'.url('project/activity','o=modify&i=20').'">รายงานกิจกรรมที่แก้ไขล่าสุด</a>',
							'<a href="'.url('project/activity','o=date1&i=20').'">รายงานกิจกรรมที่เกิดขึ้นล่าสุด</a>',
							'<a href="'.url('project/activity','o=trid&i=20').'">รายงานกิจกรรมที่ส่งมาล่าสุด</a>',
							// '<a href="'.url('project/report/s1ready').'">โครงการที่สร้างรายงาน ส.1 แล้ว</a>';
							// '<a href="'.url('project/report/s2ready').'">โครงการที่สร้างรายงาน ส.2 แล้ว</a>';
							// '<a href="'.url('project/report/m1ready').'">โครงการที่สร้างรายงาน ง.1 แล้ว</a>';
							'<a href="'.url('project/report/estimationready').'">โครงการที่สร้างรายงานประเมินแล้ว</a>',
							'<a href="'.url('project/report/followready').'">โครงการที่สร้างรายงานติดตามแล้ว</a>',
						], // children
					]), // Ui

					is_admin('projects') ? '<header class="header"><h3>รายงานผู้จัดการระบบ</h3></header>' : NULL,

				], // children
			]), // Container
			'body' => new Widget([
				'children' => [
					// Show summary report
					new Row([
						'class' => 'project-summary',
						'children' => [
							new Container([
								'class' => 'thisyearprojects',
								'child' => '<span>โครงการปีนี้</span><span class="itemvalue">'
									. (mydb::select('SELECT COUNT(*) `thisYearProjects` FROM %project% WHERE `prtype`="โครงการ" AND `pryear`=YEAR(CURDATE()) LIMIT 1')->thisYearProjects)
									. '</span><span>โครงการ​</span>',
							]),

							new Container([
								'class' => 'lastyearprojects',
								'child' => '<span>โครงการปีที่แล้ว</span><span class="itemvalue">'
									. (mydb::select('SELECT COUNT(*) `lastYearProjects` FROM %project% WHERE `prtype`="โครงการ" AND `pryear`=YEAR(CURDATE())-1 LIMIT 1')->lastYearProjects)
									. '</span><span>โครงการ​</span>',
							]),

							new Container([
								'class' => 'totalprojects',
								'child' => '<span>โครงการทั้งหมด</span><span class="itemvalue">'
									. (mydb::select('SELECT COUNT(*) `totalProjects` FROM %project% WHERE `prtype`="โครงการ" LIMIT 1')->totalProjects)
									. '</span><span>โครงการ​</span>',
							]),
						], // children
					]), // Container

					new Widget([
						'children' => [
							new Container([
								'id' => 'chart-project',
								'class' => 'sg-chart -line',
								'attribute' => [
									'data-chart-type' => 'line',
									// 'data-options' => '{"test": 1}',
									'data-options' => SG\json_encode([
										// 'series' => [
										// 	0 => ['targetAxisIndex' => 0],
										// 	1 => ['targetAxisIndex' => 1],
										// ],
										// 'vAxes' => [
										// 	0 => ['title' => 'โครงการ (จำนวน)'],
										// 	1 => ['title' => 'งบประมาณ (บาท)'],
										// ],
										'chart' => ['title' => 'โครงการ/งบประมาณ'],
										'series' => [
											0 => ['axis' => 'Project'],
											1 => ['axis' => 'Budget'],
										],
										'axes' => [
											'y' => [
												'Project' => ['label' => 'โครงการ(จำนวน)'],
												'Budget' => ['label' => 'งบประมาณ(บาท)'],
											],
										],
									]),
								],
								'children' => [
									new Table([
										'class' => '-hidden',
										'children' => (function() {
											$rows = [];

											$dbs = mydb::select('SELECT `pryear`,COUNT(*) `totals`, SUM(`budget`) `budgets` FROM %project% WHERE `prtype`="โครงการ" GROUP BY `pryear`');

											foreach ($dbs->items as $rs) {
												$rows[] = [
													'string:Year' => $rs->pryear+543,
													'number:Project' => $rs->totals,
													'number:Budget' => $rs->budgets,
												];
											}
											return $rows;
										})(),
									]), // Table

								], // children
							]), // Container
							// project_report(NULL),
						], // children
					]), // Container

					$this->script(),
				],
			]),
		]);
	}

	function script() {
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
		return '<style type="text/css">
			.project-report-section {margin: 32px 0 32px 0; padding: 0; box-shadow: 2px 2px 10px #ccc;}
			.project-report-section>h3 {text-align: center;}

			.project-summary {padding: 10px; background: #058DC7; color: #fff;}
			.project-summary>* {flex: 0 0 33%;}
			.project-summary>*>*>span {display: block;}
			.project-summary .itemvalue {font-size: 1.8em; line-height: 2em;}
			.graph-section {height: 320px;}
			</style>';
	}
}
?>