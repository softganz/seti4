<?php
/**
* Project :: Follow Summary Information
* Created 2021-05-31
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.summary
*/

import('widget:project.follow.nav.php');

class ProjectInfoSummary extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">assessment</i>',
				'navigator' => new ProjectFollowNavWidget($this->projectInfo),
			]),
			'body' => new Widget([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'รายงานประจำงวด',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Nav([
								'class' => 'nav -app-menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/info.financials').'"><i class="icon -material">attach_money</i><span>รายงานการเงินประจำงวด</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/info.progresses').'"><i class="icon -material">task_alt</i><span>รายงานความก้าวหน้าประจำงวด</span></a>',
									// '<a class="" href="'.url('project/'.$this->projectId.'/operate').'"><i class="icon -material">assignment</i><span>รายงานประจำงวด</span></a>',
								], // children
							]), // Nav
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'ผลการดำเนินงาน',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Nav([
								'class' => 'nav -app-menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/info.articles').'"><i class="icon -material">menu_book</i><span>ผลงานวิจัย</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/info.evalform').'"><i class="icon -material">checklist</i><span>แบบประเมินโครงการ</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/result').'"><i class="icon -material">assignment</i><span>สรุปผลการดำเนินโครงการ</span></a>',
								], // children
							]), // Nav
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'รายงานสรุปโครงการ',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Nav([
								'class' => 'nav -app-menu',
								'children' => [
									'<a class="" href="'.url('project/'.$this->projectId.'/operate.m2').'"><i class="icon -material">attach_money</i><span>รายงานสรุปการเงินโครงการ</span></a>',
									'<a class="" href="'.url('project/'.$this->projectId.'/finalreport').'"><i class="icon -material">verified</i><span>รายงานฉบับสมบูรณ์</span></a>',
								], // children
							]), // Nav
						], // children
					]), // Card

					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		return '<style type="text/css">
		.nav.-app-menu {background-color: transparent;}
		.nav.-app-menu .-item {flex: 0 0 auto;}
		.nav.-app-menu>ul>li>a>span {white-space: nowrap; padding: 0 8px;}
		</style>';
	}
}
?>