<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function project_admin_repair_employee_period($self) {
	// Data Model
	$stmt = 'SELECT
		p.`tpid`, t.`title`, p.`ownertype`, p.`project_status`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "info" AND `part` = "period") `periodCount`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `ownertype` IN (:ownertype)
		HAVING `periodCount` = 0
		ORDER BY `tpid` ASC
		LIMIT 100';

	$dbs = mydb::select($stmt, ':ownertype', 'SET-STRING:'.implode(',',array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE)));

	if (SG\confirm()) {
		foreach ($dbs->items as $rs) {
			$projectInfo = R::Model('project.get', $rs->tpid, '{data: "info"}');
			$periodInfo = R::Model('project.employee.period.create', $projectInfo);
			//debugMsg($periodInfo, '$periodInfo');
		}

		$dbs = mydb::select($stmt, ':ownertype', 'SET-STRING:'.implode(',',array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE)));
	}

	$periodLessDbs = mydb::select('SELECT
		p.`tpid`, t.`title`, p.`ownertype`, p.`project_status`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "info" AND `part` = "period") `periodCount`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `ownertype` IN (:ownertype)
		HAVING `periodCount` < 11
		ORDER BY `tpid` ASC
		LIMIT 100',
		':ownertype', 'SET-STRING:'.implode(',',array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE))
	);



	// View Model
	new Toolbar($self, 'REPAIR EMPLOYEE PERIOD');


	$ret = '';
	$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/employee/period').'" data-rel="#main" data-title="ซ่อมแซมงวดรายงาน" data-confirm="กรุณายืนยัน?">START REPAIR PERIOD</a></nav>';

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = [
			$rs->tpid,
			'<a href="'.url('project/app/follow/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',
			$rs->ownertype,
			$rs->periodCount
		];
	}

	$ret .= $tables->build();


	$tables = new Table();
	$tables->caption = 'โครงการที่งวดน้อยกว่า 11 งวด';
	$tables->thead = ['projectId', 'Title', 'Type', 'Period', 'Status'];

	foreach ($periodLessDbs->items as $rs) {
		$tables->rows[] = [
			$rs->tpid,
			'<a href="'.url('project/app/follow/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',
			$rs->ownertype,
			$rs->periodCount,
			$rs->project_status,
		];
	}

	$ret .= $tables->build();

	return $ret;
}
?>