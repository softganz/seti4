<?php
/**
* Project Nxt :: Teacher DB
* Created 2021-11-02
* Modify  2021-11-02
*
* @return Widget
*
* @usage project/nxt/db/teacher
*/

$debug = true;

class ProjectNxtDbTeacher extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบฐานข้อมูล :: อาจารย์',
			]),
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'ชื่อ นามสกุล',
								'หลักสูตร'
							],
							'children' => array_map(
								function($item) {
									return [
										$item->teacherName,
										'<a href="'.url('project/proposal/'.$item->projectId).'">'.$item->title.'</a>'
									];
								},
								mydb::select(
									'SELECT
									teacher.`detail1` `teacherName`
									, teacher.`tpid` `projectId`
									, topic.`title`
									FROM %project_tr% teacher
										LEFT JOIN %topic% topic ON topic.`tpid` = teacher.`tpid`
									WHERE teacher.`formid` = "develop" AND teacher.`part` = "owner"
									ORDER BY CONVERT(`teacherName` USING tis620) ASC'
								)->items
							), // children
						]), // Table
					]), // ScrollView
					// new DebugMsg(mydb()->_query),
				], // children
			]), // Row
		]);
	}
}
?>