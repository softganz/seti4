<?php
/**
* Project Nxt :: Entrepreneur DB
* Created 2021-11-02
* Modify  2021-11-02
*
* @return Widget
*
* @usage project/nxt/db/entrepreneur
*/

$debug = true;

class ProjectNxtDbEntrepreneur extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบฐานข้อมูล :: ผู้ประกอบการ',
			]),
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'ชื่อสถานประกอบการ',
								'หลักสูตร'
							],
							'children' => array_map(
								function($item) {
									return [
										$item->name,
										'<a href="'.url('project/proposal/'.$item->projectId).'">'.$item->title.'</a>'
									];
								},
								mydb::select(
									'SELECT
									org.`name`
									, coorg.`tpid` `projectId`
									, topic.`title`
									FROM %project_tr% coorg
										LEFT JOIN %db_org% org ON org.`orgId` = coorg.`refId`
										LEFT JOIN %topic% topic ON topic.`tpid` = coorg.`tpid`
									WHERE coorg.`formid` = "develop" AND coorg.`part` = "coorg"
									ORDER BY CONVERT(org.`name` USING tis620) ASC'
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