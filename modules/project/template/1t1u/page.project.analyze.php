<?php
/**
* Project :: Analyze
* Created 2022-01-29
* Modify  2022-01-29
*
* @return Widget
*
* @usage project/analyze
*/

import('model:project.follow.php');

class ProjectAnalyze extends Page {
	function build() {
		$cacheData = mydb::select('SELECT `bigId`, `fldData` FROM %bigdata% WHERE `keyName` = "cache" AND `fldName` = "project/app/follow" LIMIT 1');
		$projectDbs = SG\json_decode($cacheData->fldData);

		$projectCard = new Ui('div', 'ui-card');

		$projectCard->add(
			'<div class="header"><h3>ภาพรวม</h3></div>'
			. '<div class="detail">'
			.($projectDbs->sum->totalTambon ? 'โครงการ <b>'.number_format($projectDbs->sum->totalTambon).'</b> ตำบล ' : '')
			.($projectDbs->sum->totalEmployee ? 'ผู้รับจ้าง <b>'.number_format($projectDbs->sum->totalEmployee).'</b> คน<br />' : '')
			.'กิจกรรมวันนี้ <b>'.number_format($projectDbs->sum->totalTodayEmployee).'</b> คน <b>'.number_format($projectDbs->sum->totalTodayAction).'</b> กิจกรรม<br />'
			.'กิจกรรมเมื่อวาน <b>'.number_format($projectDbs->sum->totalYesterdayEmployee).'</b> คน <b>'.number_format($projectDbs->sum->totalYesterdayAction).'</b> กิจกรรม<br />'
			. 'กิจกรรมทั้งหมด <b>'.number_format($projectDbs->sum->totalEmployeeAction).'</b> คน <b>'.number_format($projectDbs->sum->totalAction).'</b> กิจกรรม'
			.'</div>',
		);

		foreach ($projectDbs->items as $rs) {
			$url = url('project/app/follow/'.$rs->projectId);
			$cardOption = array(
				'class' => 'sg-action',
				'href' => $url,
				'data-webview' => $rs->title,
			);

			$projectCard->add(
				'<div class="header"><h3><a class="sg-action" href="'.$url.'" data-webview="'.$rs->title.'">'.$rs->title.'</a></h3></div>'
				. '<div class="detail">'
				.($rs->totalTambon ? 'โครงการ <b>'.number_format($rs->totalTambon).'</b> ตำบล ' : '')
				.($rs->totalEmployee ? 'ผู้รับจ้าง <b>'.number_format($rs->totalEmployee).'</b> คน<br />' : '')
				.'กิจกรรมวันนี้ <b>'.number_format($rs->totalTodayEmployee).'</b> คน <b>'.number_format($rs->totalTodayAction).'</b> กิจกรรม<br />'
				.'กิจกรรมเมื่อวาน <b>'.number_format($rs->totalYesterdayEmployee).'</b> คน <b>'.number_format($rs->totalYesterdayAction).'</b> กิจกรรม<br />'
				. 'กิจกรรมทั้งหมด <b>'.number_format($rs->totalEmployeeAction).'</b> คน <b>'.number_format($rs->totalAction).'</b> กิจกรรม'
				.'</div>',
				$cardOption
			);
		}


		// - งานที่ผู้รับจ้าง วิเคราะห์ประเภทงานว่าแต่ละประเภทมีจำนวนมากน้อย
		// - การเงิน ผู้รับจ้างได้เงินกี่คน เต็มจำนวนกี่คน ไม่เต็มกี่คน
		// - ผลการอบรม แต่ละด้าน
		// - ผู้รับจ้างจากพื้นที่ไหน/จบ/
		// 	- เป็นคนในพื้นที่มากน้อย(ที่อยู่กับจังหวัด)
		// 	- อบรมเรื่องอะไร
		// 	- ทำงานครบถ้วน
		// 	- การจ่ายเงิน
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'วิเคราะห์',
				'leading' => '<i class="icon -material">insights</i>',
			]), // AppBar
			'sideBar' => new Nav([
						'class' => 'nav -app-menu',
						'children' => [
							'<a href="'.url('project/report/follow').'"><i class="icon -material">insights</i><span>ภาพรวมโครงการ</span></a>',
							'<a href="'.url('project/report/u2t/jobtype').'"><i class="icon -material">insights</i><span>ประเภทงาน</span></a>',
							'<a href="'.url('project/report/u2t/training').'"><i class="icon -material">insights</i><span>การอบรม</span></a>',
							'<a href="'.url('project/report/u2t/salary').'"><i class="icon -material">insights</i><span>การรับเงินเดือน</span></a>',
							'<a href="'.url('project/report/u2t/emparea').'"><i class="icon -material">insights</i><span>ผู้รับจ้างในพื้นที่</span></a>',
							'<a href="'.url('project/report/u2t/money').'"><i class="icon -material">insights</i><span>การจ่ายเงิน</span></a>',
						]
					]), // Nav

			'body' => new Widget([
				'children' => [
					// new Nav([
					// 	'class' => 'nav -app-menu',
					// 	'children' => [
					// 		'<a href="'.url('project/report/u2t/jobtype').'"><i class="icon -material">insights</i><span>ประเภทงาน</span></a>',
					// 		'<a href="'.url('project/report/u2t/salary').'"><i class="icon -material">insights</i><span>การรับเงินเดือน</span></a>',
					// 		'<a href="'.url('project/report/u2t/training').'"><i class="icon -material">insights</i><span>การอบรม</span></a>',
					// 		'<a href="'.url('project/report/u2t/emparea').'"><i class="icon -material">insights</i><span>ผู้รับจ้างในพื้นที่</span></a>',
					// 	]
					// ]), // Nav
					$projectCard,
					'<style type="text/css">
					.module.-module-has-sidebar .page.-sidebar {border-right: none;}
					.nav.-app-menu {background-color: transparent;}
					.nav.-app-menu .-item {flex: 0 0 calc(100% - 16px);}
					.nav.-app-menu .-item>a>.icon, .nav.-app-menu .ui-item>a>.icon {display: inline-block;}
					</style>',
				], // children
			]), // Widget
		]);
	}
}
?>