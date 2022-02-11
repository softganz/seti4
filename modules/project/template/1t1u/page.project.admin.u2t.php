<?php
/**
* Project :: U2T Admin
* Created 2021-11-01
* Modify  2021-11-01
*
* @param String $action
* @return Widget
*
* @usage project/admin/u2t/{action}
*/

$debug = true;

class ProjectAdminU2t extends Page {
	var $action;

	function __construct($action = NULL) {
		$this->action = $action;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'U2T Admin',
			]),
			'sideBar' => new Ui([
				'type' => 'menu',
				'children' => [
				'<a href="'.url('project/admin/u2t').'">U2T Admin</a>',
				'<a href="'.url('project/admin/u2t/bankover').'">บัญชีจ่ายเงินมากกว่า 1</a>',
				], // children
			]),
			'body' => new Widget([
				'children' => [
					(new Widget([
						// 'type' => 'menu',
						'children' => [
						'<a href="'.url('project/admin/u2t').'">U2T Admin</a>',
						'<a href="'.url('project/admin/u2t/bankover').'">บัญชีจ่ายเงินมากกว่า 1</a>',
						], // children
					]))->build(),
					$this->action ? $this->{$this->action}() : NULL,
				],
			]),
		]);
	}

	function bankOver() {
		$tables = new Table([
			'caption' => 'บัญชีจ่ายเงินมากกว่า 1',
			'thead' => ['Title', 'amt' => 'Amount'],
		]);
		$tables->rows = (function() {
				$rows = [];
				$dbs = mydb::select(
					'SELECT `keyid` `projectId`, t.`title`, COUNT(*) `amt`
						FROM %bigdata% b
							LEFT JOIN %topic% t ON t.`tpid` = b.`keyid`
						WHERE `keyname` LIKE "project.info" AND `fldname` LIKE "bankcheck"
						GROUP BY `keyid`
						HAVING `amt` > 1'
				);
				debugMsg($dbs,'$dbs');
				foreach ($dbs->items as $item) {
					$rows[] = [
						'<a href="'.url('project/'.$item->projectId).'" target="_blank">'.$item->title.'</a>',
						$item->amt
					];
				}
				if (empty($rows)) $rows[] = 'ไม่มีรายการ';
				debugMsg($rows,'$rows');
				return $rows;
			})();
		debugMsg($tables,'$tables');
		return $tables->build();

		return new Ui([
			'caption' => 'บัญชีจ่ายเงินมากกว่า 1',
			'thead' => ['Title', 'amt' => 'Amount'],
			'children' => (function() {
				$rows = [];
				$dbs = mydb::select(
					'SELECT `keyid` `projectId`, t.`title`, COUNT(*) `amt`
						FROM %bigdata% b
							LEFT JOIN %topic% t ON t.`tpid` = b.`keyid`
						WHERE `keyname` LIKE "project.info" AND `fldname` LIKE "bankcheck"
						GROUP BY `keyid`
						HAVING `amt` > 1'
				);
				debugMsg($dbs,'$dbs');
				foreach ($dbs->items as $item) {
					$rows[] = [
						'<a href="'.url('project/'.$item->projectId).'" target="_blank">'.$item->title.'</a>',
						$item->amt
					];
				}
				if (empty($rows)) $rows[] = 'ไม่มีรายการ';
				debugMsg($rows,'$rows');
				return $rows;
			})(),
		]);
	}
}
?>