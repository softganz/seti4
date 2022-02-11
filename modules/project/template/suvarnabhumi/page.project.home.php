<?php
/**
* Module :: Description
* Created 2021-01-18
* Modify  2021-01-18
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class ProjectHome extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบบริหารโครงการ',
			]), // AppBar
			'body' => new Row([
				'class' => 'nav -app-menu',
				'children' => [
					// '<a href="'.url('project/planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
					// '<a href="'.url('project/proposal').'"><i class="icon -material">tune</i><span>เสนอโครงการ</span></a>',
					'<a href="'.url('project/list').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',
					'<a href="{url:calendar/*}"><i class="icon -material">event</i><span>ปฎิทินกิจกรรม</span></a>',
					'<a href="'.url('project/articles').'"><i class="icon -material">menu_book</i><span>บทความ/งานวิจัย</span></a>',
					'<a href="'.url('project/map').'"><i class="icon -material">place</i><span>แผนที่โครงการ</span></a>',
					// '<a href="'.url('project/eval').'"><i class="icon -material">assessment</i><span>การประเมินผล</span></a>',
					'<a href="'.url('project/report').'"><i class="icon -material">receipt</i><span>ระบบรายงาน</span></a>',
				], // children
			]), // Row
		]);
	}
}
?>