<?php
/**
* Project Nxt :: Next Gen Home Page
* Created 2021-09-24
* Modify  2021-09-24
*
* @return Widget
*
* @usage project/nxt
*/

$debug = true;

class ProjectNxt extends Page {
	var $right;

	function __construct() {
		$this->right = (Object) [
			'admin' => is_admin('project'),
		];
	}
	function build() {
		// debugMsg(i(),'i()');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'โครงการบัณฑิตพันธุ์ใหม่',
				'leading' => '<i class="icon -material">school</i>',
			]),
			'body' => new Row([
				'class' => 'nav -app-menu',
				'children' => [
					'<a href="'.url('project/proposal').'"><i class="icon -material">tune</i><span>เสนอหลักสูตร</span></a>',
					'<a href="'.url('project/nxt/proove').'"><i class="icon -material">rule</i><span>พิจารณาหลักสูตร</span></a>',
					$this->right->admin ? '<a href="'.url('project/nxt/budget').'"><i class="icon -material">attach_money</i><span>งบประมาณ</span></a>' : NULL,
					'<a href="'.url('project/nxt/follow').'"><i class="icon -material">directions_run</i><span>การดำเนินงานหลักสูตร</span></a>',
					'<a href="'.url('project/nxt/db').'"><i class="icon -material">groups</i><span>ฐานข้อมูล</span></a>',
					'<a href="'.url('project/nxt/eval').'"><i class="icon -material">assessment</i><span>การประเมินผล</span></a>',
					'<a href="'.url('project/nxt/report').'"><i class="icon -material">receipt</i><span>ระบบรายงาน</span></a>',
					'<a href="'.url('project/nxt/dashboard').'"><i class="icon -material">insights</i><span>ระบบวิเคราะห์</span></a>',
					'<a href="'.url('project/nxt/km').'"><i class="icon -material">movie</i><span>ระบบคลังข้อมูล</span></a>',
					'<a href="'.url('project/nxt/news').'"><i class="icon -material">newspaper</i><span>ประชาสัมพันธ์</span></a>',
				], // children
			]), // Row
		]);
	}
}
?>