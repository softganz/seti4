<?php
/**
* Project Nxt :: Entrepreneur List
* Created 2021-11-01
* Modify  2021-11-01
*
* @return Widget
*
* @usage project/nxt/entrepreneur
*/

$debug = true;

class ProjectNxtEntrepreneur extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ผู้ประกอบการ',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'class' => 'form-report',
						'action' => url('project/nxt/entrepreneur'),
						'children' => [
							// 'orgid' => [
							// 	'type' => 'select',
							// 	'options' => '=ทุกสถาบันการศึกษา=',
							// ],
							// 'group' => [
							// 	'type' => 'select',
							// 	'options' => '=ทุกกลุ่มอุตสาหกรรม=',
							// ],
							'q' => [
								'type' => 'text',
								'placeholder' => 'ค้นชื่อ',
							],
						], // children
					]), // Form

					// new Table([
					// 	'thead' => ['มหาวิทยาลัย'],
					// 	'children' => array_map(function($item) {
					// 		return [
					// 				'<a href="'.url('org/'.$item->orgId).'">'.$item->orgName.'</a>',
					// 			];
					// 	}, mydb::select(
					// 		'SELECT
					// 			o.`orgId`, o.`name` `orgName`
					// 		FROM %db_org% o
					// 		WHERE o.`sector` IN (10)
					// 		ORDER BY CONVERT(o.`name` USING tis620) ASC'
					// 	)->items), // children
					// ]), // Table

				],
			]),
		]);
	}
}
?>