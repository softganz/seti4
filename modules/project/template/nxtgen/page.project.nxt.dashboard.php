<?php
/**
* Project Nxt :: Dashboard Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/dashboard
*/

$debug = true;

class ProjectNxtDashboard extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบวิเคราะห์',
				'leading' => '<i class="icon -material">insights</i>',
			]),
			'body' => new Widget([
				'children' => [
					new Row([
						'style' => 'flex-wrap: wrap;',
						'children' => [
							$this->course(),
							$this->orgJoin(),
							$this->groupJoin(),
							$this->industry(),
						], // children
					]), // Row
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function course() {
		$followDbs = mydb::select(
			'SELECT
			course.`title`
			, COUNT(*) `totalProject`
			, COUNT(DISTINCT t.`orgId`) `totalOrg`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %topic% course ON course.`tpid` = t.`parent`
			WHERE t.`parent` IN ( :parent )
			GROUP BY t.`parent`',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);

		$proposalDbs = mydb::select(
			'SELECT
			course.`title`
			, COUNT(*) `totalProject`
			, COUNT(DISTINCT t.`orgId`) `totalOrg`
			FROM %project_dev% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %topic% course ON course.`tpid` = t.`parent`
			WHERE t.`parent` IN ( :parent )
			GROUP BY t.`parent`',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);

		return new Card([
			'class' => 'dashboard-box -group-join',
			'children' => [
				'เสนอหลักสูตร',
				new Column([
					'children' => array_map(
						function($item) {
							return new Row([
								'mainAxisAlignment' => 'spacebetween',
								'crossAxisAlignment' => 'center',
								'children' => [
									$item->title,
									' <big>'.number_format($item->totalOrg).'</big> สถาบัน'
									. ' <big>'.number_format($item->totalProject).'</big> หลักสูตร',
								], // children
							]);
						},
						$proposalDbs->items
					), // children
				]), // Column
				'อนุมัติหลักสูตร',
				new Column([
					'children' => array_map(
						function($item) {
							return new Row([
								'mainAxisAlignment' => 'spacebetween',
								'crossAxisAlignment' => 'center',
								'children' => [
									$item->title,
									' <big>'.number_format($item->totalOrg).'</big> สถาบัน'
									. ' <big>'.number_format($item->totalProject).'</big> หลักสูตร',
								], // children
							]);
						},
						$followDbs->items
					), // children
				]), // Column
			], // children
		]);
	}

	function orgJoin() {
		$followDbs = mydb::select(
			'SELECT
			COUNT(*) `totalProject`
			, COUNT(DISTINCT t.`orgId`) `totalOrg`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE t.`parent` IN ( :parent )
			LIMIT 1',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);

		$proposalDbs = mydb::select(
			'SELECT
			COUNT(*) `totalProject`
			, COUNT(DISTINCT t.`orgId`) `totalOrg`
			FROM %project_dev% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE t.`parent` IN ( :parent )
			LIMIT 1',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);

		return new Card([
			'class' => 'dashboard-box',
			'children' => [
				new Row([
					'mainAxisAlignment' => 'spacebetween',
					'crossAxisAlignment' => 'center',
					'class' => '-org',
					'children' => [
						'<span>สถาบันเข้าร่วม </span>',
						'<span class="-value"><big>'.$followDbs->totalOrg.'</big> สถาบัน</span>',
					], // children
				]), // Row
				new Row([
					'mainAxisAlignment' => 'spacebetween',
					'crossAxisAlignment' => 'center',
					'class' => '-project',
					'children' => [
						'<span>หลักสูตรอนุมัติ </span>',
						'<span class="-value"><big>'.$followDbs->totalProject.'</big> หลักสูตร</span>',
					], // children
				]), // Row
				new Row([
					'mainAxisAlignment' => 'spacebetween',
					'crossAxisAlignment' => 'center',
					'class' => '-project',
					'children' => [
						'<span>หลักสูตรเสนอ </span>',
						'<span class="-value"><big>'.$proposalDbs->totalProject.'</big> หลักสูตร</span>',
					], // children
				]), // Row
			], // children
		]);
	}

	function groupJoin() {
		$groupDbs = mydb::select(
			'SELECT g.`groupType`, b.`totalProject`, b.`totalOrg`
			FROM %db_org% g
				LEFT JOIN (
					SELECT
					o.`groupType`
					, COUNT(*) `totalProject`
					, COUNT(DISTINCT t.`orgId`) `totalOrg`
					FROM %project% p
						LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
						LEFT JOIN %db_org% o ON o.`orgId` = t.`orgId`
					WHERE t.`parent` IN ( :parent )
					GROUP BY o.`groupType`
				) b ON b.`groupType` = g.`groupType`
			WHERE g.`groupType` IS NOT NULL
			GROUP BY g.`groupType`',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);

		return new Card([
			'class' => 'dashboard-box -group-join',
			'children' => array_map(
				function($item) {
					return new Row([
						'mainAxisAlignment' => 'spacebetween',
						'crossAxisAlignment' => 'center',
						'children' => [
							$item->groupType,
							' <big>'.number_format($item->totalOrg).'</big> สถาบัน'
							. ' <big>'.number_format($item->totalProject).'</big> หลักสูตร',
						], // children
					]);
				},
				$groupDbs->items
			), // children
		]);
	}

	function industry() {
		$industryDbs = mydb::select(
			// 'SELECT
			// industry.`flddata` `industryName`
			// , COUNT(*) `totalProject`
			// FROM %project% p
			// 	LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			// 	LEFT JOIN %bigdata% industry ON industry.`keyname` = "project.info" AND industry.`fldname` = "industryId" AND industry.`keyid` = p.`tpid`
			// WHERE t.`parent` IN ( :parent )
			// GROUP BY industry.`flddata`
			// HAVING `industryName` IS NOT NULL
			// ',
			'SELECT
			tg.`name` `industryName`
			, COUNT(DISTINCT t.`orgId`) `totalOrg`
			, COUNT(DISTINCT industry.`keyId`) `totalProject`
			FROM %tag% tg
				LEFT JOIN %bigdata% industry ON industry.`keyName` = "project.develop" AND industry.`fldName` = "industryId" AND industry.`fldRef` = tg.`catid`
				LEFT JOIN %topic% t ON t.`tpid` = industry.`keyId`
			WHERE tg.`tagGroup` = "project:industry"
			GROUP BY `industryName`
			ORDER BY CONVERT(`industryName` USING tis620) ASC',
			[':parent' => 'SET:'.cfg('project')->nxt->course]
		);
		// debugMsg($industryDbs, '$industryDbs');
		return new Card([
			'class' => 'dashboard-box -group-join',
			'children' => array_map(
				function($item) {
					return new Row([
						'mainAxisAlignment' => 'spacebetween',
						'crossAxisAlignment' => 'center',
						'children' => [
							$item->industryName,
							' <big>'.number_format($item->totalOrg).'</big> สถาบัน'
							. ' <big>'.number_format($item->totalProject).'</big> หลักสูตร',
						], // children
					]);
				},
				$industryDbs->items
			), // children
		]);
	}

	function _script() {
		return '<style type="text/css">
		.dashboard-box {width: 200px; background-color: green; color: #fff; padding: 8px; border-radius: 8px; margin: 8px; display:}
		.dashboard-box .-item {padding: 4px 8px;}
		.dashboard-box.-group-join {white-space: nowrap; width: auto;}
		</style>';
	}
}
?>