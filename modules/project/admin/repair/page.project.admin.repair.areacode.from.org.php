<?php
/**
* Project :: Repair AreaCode
* Created 2021-04-11
* Modify  2021-04-11
*
* @param Object $self
* @return String
*
* @usage project/admin/repair/areacode
*/

$debug = true;

class ProjectAdminRepairAreacodeFromOrg extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Project Areacode From Organization Areacode',
			]),
			'body' => new Widget([
				'children' => [
					'<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/areacode/from/org').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR AREACODE</a></nav>',

					SG\confirm() ? $this->_repair() : NULL,

					$this->_clearEmptyAreaCode(),

					new Table([
						'thead' => ['id -nowrap -center' => 'Project Id', 'Title', 'Organization', 'code -center -nowrap' => 'Area Code'],
						'children' => array_map(
							function($item) {
								return [
									$item->projectId,
									'<a href="'.url('project/'.$item->projectId).'" target="_blank">'.$item->title.'</a>',
									'<a class="sg-action" href="'.url('org/'.$item->orgId.'/info.view').'" data-rel="box" data-width="full" xtarget="_blank">'.$item->orgName.'</a>',
									$item->orgAreaCode
								];
							},
							mydb::select(
								'SELECT p.`tpid` `projectId`, t.`orgId`, t.`title`
								, o.`name` `orgName`, o.`areacode` `orgAreaCode`
								FROM %project% p
									LEFT JOIN %topic% t USING(`tpid`)
									LEFT JOIN %db_org% o ON o.`orgId` = t.`orgId`
								WHERE t.`areacode` IS NULL'
							)->items
						), // children
					])
				],
			]),
		]);
	}

	function _repair() {
		$ret = '<h3>Update areacode from Organization Areacode</h3>';
		mydb::query(
			'UPDATE %topic% t
			RIGHT JOIN %db_org% o ON o.`orgId` = t.`orgId`
			SET t.`areacode` = o.`areacode`
			WHERE o.`areacode` IS NOT NULL'
		);
		$ret .= mydb()->_query.'<br />';
		return $ret;
	}

	function _clearEmptyAreaCode() {
		mydb::query(
			'UPDATE %topic% SET `areacode` = NULL WHERE `areacode` = ""'
		);
		return '<h3>Clear empty code</h3>'.mydb()->_query.'<br />';
	}
}
?>