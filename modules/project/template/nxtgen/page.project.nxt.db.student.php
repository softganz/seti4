<?php
/**
* Project Nxt :: Student DB
* Created 2021-11-02
* Modify  2021-11-02
*
* @return Widget
*
* @usage project/nxt/db/student
*/

$debug = true;

class ProjectNxtDbStudent extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบฐานข้อมูล :: นักศึกษา',
			]),
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'ชื่อ นามสกุล',
								'serie -center' => 'รุ่น',
								'status -center' => 'สถานภาพ',
								'หลักสูตร'
							],
							'children' => array_map(
								function($item) {
									return [
										$item->fullname,
										$item->serieNo,
										$item->status,
										'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>'
									];
								},
								mydb::select(
									'SELECT
									CONCAT(p.`prename`, p.`name`, " ", p.`lname`) `fullname`
									, topic.`title`
									, s.*
									FROM %lms_student% s
										LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
										LEFT JOIN %topic% topic ON topic.`tpid` = s.`projectId`
									ORDER BY CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC'
								)->items
							), // children
						]), // Table
					]), // ScrollView
				], // children
			]), // Row
		]);
	}
}
?>