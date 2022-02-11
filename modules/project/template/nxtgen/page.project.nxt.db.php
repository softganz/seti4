<?php
/**
* Project Nxt :: DB Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/db
*/

$debug = true;

class ProjectNxtDb extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบฐานข้อมูล',
				'leading' => '<i class="icon -material">groups</i>',
			]),
			'body' => new Row([
				'class' => 'nav -app-menu',
				'children' => [
					'<a href="'.url('project/nxt/db/student').'"><i class="icon -material">school</i><span>ข้อมูลนักศึกษา</span></a>',
					'<a href="'.url('project/nxt/db/teacher').'"><i class="icon -material">school</i><span>ข้อมูลอาจารย์</span></a>',
					'<a href="'.url('project/nxt/db/entrepreneur').'"><i class="icon -material">school</i><span>ข้อมูลสถานประกอบการ</span></a>',
				], // children
			]), // Row
		]);
	}
}
?>