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

class ProjectAdminRepairAreacode extends Page {
	function build() {
		$changwatFieldExists = mydb::columns('topic', 'changwat');

		if ($changwatFieldExists && SG\confirm()) {
			$ret .= '<h3>Update areacode</h3>';
			mydb::query(
				'UPDATE %project% p
					LEFT JOIN %topic% t USING(`tpid`)
				SET t.`areacode` = IF(
					p.`changwat` IS NULL OR p.`changwat` = "",
					t.`changwat`,
					CONCAT(IFNULL(p.`changwat`,"00"), IFNULL(p.`ampur`,"00"),IFNULL(p.`tambon`,"00"), IF(p.`village` IS NULL, "", LPAD(p.`village`,2,"0")))
					)
				WHERE t.`areacode` IS NULL'
			);
			$ret .= mydb()->_query.'<br />';

			mydb::query(
				'UPDATE %project_dev% d
					LEFT JOIN %topic% t USING(`tpid`)
				SET t.`areacode` = IF(
					d.`changwat` IS NULL OR d.`changwat` = "",
					t.`changwat`,
					CONCAT(IFNULL(d.`changwat`,"00"), IFNULL(d.`ampur`,"00"),IFNULL(d.`tambon`,"00"), IF(d.`village` IS NULL, "", LPAD(d.`village`,2,"0")))
					)
				WHERE t.`areacode` IS NULL'
			);

			$ret .= mydb()->_query.'<br />';
		}

		$ret .= '<h3>Clear empty code</h3>';
		if ($changwatFieldExists) {
			mydb::query('UPDATE %topic% SET `changwat` = NULL WHERE `type` = "project" AND `changwat` = "" ');
			$ret .= mydb()->_query.'<br />';
		}

		mydb::query('UPDATE %project% SET `changwat` = NULL WHERE `changwat` = "" ');
		$ret .= mydb()->_query.'<br />';

		mydb::query('UPDATE %project% SET `ampur` = NULL WHERE `ampur` = "" ');
		$ret .= mydb()->_query.'<br />';

		mydb::query('UPDATE %project% SET `tambon` = NULL WHERE `tambon` = "" ');
		$ret .= mydb()->_query.'<br />';

		mydb::query('UPDATE %project% SET `village` = NULL WHERE `village` = "" ');
		$ret .= mydb()->_query.'<br />';

		$stmt = 'SELECT p.`tpid`, t.`title`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE t.`areacode` IS NULL
			';

		$dbs = mydb::select($stmt);

		//$ret .= mydb()->_query;

		$ret .= mydb::printtable($dbs);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Project Areacode',
			]),
			'body' => new Container([
				'children' => [
					'<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/areacode').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR AREACODE</a></nav>',
					$ret
				],
			]),
		]);
	}
}
?>