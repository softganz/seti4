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

function project_admin_repair_employee_period_date($self) {
	// Data Model

	if (SG\confirm()) {
		// มหาวิทยาลัยเทคโนโลยีราชมงคลศรีวิชัย, มหาวิทยาลัยนราธิวาสราชนครินทร์, มหาวิทยาลัยราชภัฏสงขลา, มหาวิทยาลัยสงขลานครินทร์
		// REPAIR PERIOD : End date 24
		$stmt = 'UPDATE %project_tr% pe
				LEFT JOIN %topic% t ON t.`tpid` = pe.`tpid`
				LEFT JOIN %project% p ON p.`tpid` = pe.`tpid`
				LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
				LEFT JOIN %topic% university ON university.`tpid` = tambon.`parent`
			SET
				pe.`date1` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-01"
					WHEN pe.`period` = 2 THEN "2021-02-25"
					WHEN pe.`period` = 3 THEN "2021-03-25"
					WHEN pe.`period` = 4 THEN "2021-04-25"
					WHEN pe.`period` = 5 THEN "2021-05-25"
					WHEN pe.`period` = 6 THEN "2021-06-25"
					WHEN pe.`period` = 7 THEN "2021-07-25"
					WHEN pe.`period` = 8 THEN "2021-08-25"
					WHEN pe.`period` = 9 THEN "2021-09-25"
					WHEN pe.`period` = 10 THEN "2021-10-25"
					WHEN pe.`period` = 11 THEN "2021-11-25"
					END
				, pe.`date2` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-24"
					WHEN pe.`period` = 2 THEN "2021-03-24"
					WHEN pe.`period` = 3 THEN "2021-04-24"
					WHEN pe.`period` = 4 THEN "2021-05-24"
					WHEN pe.`period` = 5 THEN "2021-06-24"
					WHEN pe.`period` = 6 THEN "2021-07-24"
					WHEN pe.`period` = 7 THEN "2021-08-24"
					WHEN pe.`period` = 8 THEN "2021-09-24"
					WHEN pe.`period` = 9 THEN "2021-10-24"
					WHEN pe.`period` = 10 THEN "2021-11-24"
					WHEN pe.`period` = 11 THEN "2021-12-31"
					END
			WHERE  pe.`formid`="info" AND pe.`part`="period"
				AND p.`ownertype` IN ("graduate","people","student")
				AND university.`tpid` IN (119,120,108,103)';

		$result = mydb::query($stmt);
		$ret .= '<p>Updated มหาวิทยาลัยเทคโนโลยีราชมงคลศรีวิชัย, มหาวิทยาลัยนราธิวาสราชนครินทร์, มหาวิทยาลัยราชภัฏสงขลา, มหาวิทยาลัยสงขลานครินทร์ <b>'.number_format(mydb()->_affected_rows).'</b> rows.</p>';
		//$ret .= '<pre>'.preg_replace('/\t+/Sm', '	', $result->_query).'</pre>';
		//$ret .= '<pre>RESULT->_query '.$result->_query.'</pre>'.print_o($result, '$result');
		$ret .= '<pre>'.mydb()->_query.'</pre>';

		//มหาวิทยาลัยราชภัฏยะลา
		//REPAIR PERIOD : End date 25
		$stmt = 'UPDATE %project_tr% pe
				LEFT JOIN %topic% t ON t.`tpid` = pe.`tpid`
				LEFT JOIN %project% p ON p.`tpid` = pe.`tpid`
				LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
				LEFT JOIN %topic% university ON university.`tpid` = tambon.`parent`
			SET
				pe.`date1` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-01"
					WHEN pe.`period` = 2 THEN "2021-02-26"
					WHEN pe.`period` = 3 THEN "2021-03-26"
					WHEN pe.`period` = 4 THEN "2021-04-26"
					WHEN pe.`period` = 5 THEN "2021-05-26"
					WHEN pe.`period` = 6 THEN "2021-06-26"
					WHEN pe.`period` = 7 THEN "2021-07-26"
					WHEN pe.`period` = 8 THEN "2021-08-26"
					WHEN pe.`period` = 9 THEN "2021-09-26"
					WHEN pe.`period` = 10 THEN "2021-10-26"
					WHEN pe.`period` = 11 THEN "2021-11-26"
					END
				, pe.`date2` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-25"
					WHEN pe.`period` = 2 THEN "2021-03-25"
					WHEN pe.`period` = 3 THEN "2021-04-25"
					WHEN pe.`period` = 4 THEN "2021-05-25"
					WHEN pe.`period` = 5 THEN "2021-06-25"
					WHEN pe.`period` = 6 THEN "2021-07-25"
					WHEN pe.`period` = 7 THEN "2021-08-25"
					WHEN pe.`period` = 8 THEN "2021-09-25"
					WHEN pe.`period` = 9 THEN "2021-10-25"
					WHEN pe.`period` = 10 THEN "2021-11-25"
					WHEN pe.`period` = 11 THEN "2021-12-31"
					END
			WHERE  pe.`formid`="info" AND pe.`part`="period"
				AND p.`ownertype` IN ("graduate","people","student")
				AND university.`tpid` IN (122)';

		$result = mydb::query($stmt);
		$ret .= '<p>Updated มหาวิทยาลัยราชภัฏยะลา <b>'.number_format(mydb()->_affected_rows).'</b> rows.</p>';
		$ret .= '<pre>'.mydb()->_query.'</pre>';

		//มหาวิทยาลัยทักษิณ
		//REPAIR PERIOD : End date 25
		$stmt = 'UPDATE %project_tr% pe
				LEFT JOIN %topic% t ON t.`tpid` = pe.`tpid`
				LEFT JOIN %project% p ON p.`tpid` = pe.`tpid`
				LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
				LEFT JOIN %topic% university ON university.`tpid` = tambon.`parent`
			SET
				pe.`date1` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-01"
					WHEN pe.`period` = 2 THEN "2021-02-26"
					WHEN pe.`period` = 3 THEN "2021-03-26"
					WHEN pe.`period` = 4 THEN "2021-04-26"
					WHEN pe.`period` = 5 THEN "2021-05-26"
					WHEN pe.`period` = 6 THEN "2021-06-26"
					WHEN pe.`period` = 7 THEN "2021-07-26"
					WHEN pe.`period` = 8 THEN "2021-08-26"
					WHEN pe.`period` = 9 THEN "2021-09-26"
					WHEN pe.`period` = 10 THEN "2021-10-26"
					WHEN pe.`period` = 11 THEN "2021-11-26"
					END
				, pe.`date2` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-25"
					WHEN pe.`period` = 2 THEN "2021-03-25"
					WHEN pe.`period` = 3 THEN "2021-04-25"
					WHEN pe.`period` = 4 THEN "2021-05-25"
					WHEN pe.`period` = 5 THEN "2021-06-25"
					WHEN pe.`period` = 6 THEN "2021-07-25"
					WHEN pe.`period` = 7 THEN "2021-08-25"
					WHEN pe.`period` = 8 THEN "2021-09-25"
					WHEN pe.`period` = 9 THEN "2021-10-25"
					WHEN pe.`period` = 10 THEN "2021-11-25"
					WHEN pe.`period` = 11 THEN "2021-12-25"
					END
			WHERE  pe.`formid`="info" AND pe.`part`="period"
				AND p.`ownertype` IN ("graduate","people","student")
				AND university.`tpid` IN (118)';

		$result = mydb::query($stmt);
		$ret .= '<p>Updated มหาวิทยาลัยทักษิณ <b>'.number_format(mydb()->_affected_rows).'</b> rows.</p>';
		$ret .= '<pre>'.mydb()->_query.'</pre>';

		//มหาวิทยาลัยราชภัฏภูเก็ต
		//REPAIR PERIOD : End of Month
		$stmt = 'UPDATE %project_tr% pe
				LEFT JOIN %topic% t ON t.`tpid` = pe.`tpid`
				LEFT JOIN %project% p ON p.`tpid` = pe.`tpid`
				LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
				LEFT JOIN %topic% university ON university.`tpid` = tambon.`parent`
			SET
				pe.`date1` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-01"
					WHEN pe.`period` = 2 THEN "2021-03-01"
					WHEN pe.`period` = 3 THEN "2021-04-01"
					WHEN pe.`period` = 4 THEN "2021-05-01"
					WHEN pe.`period` = 5 THEN "2021-06-01"
					WHEN pe.`period` = 6 THEN "2021-07-01"
					WHEN pe.`period` = 7 THEN "2021-08-01"
					WHEN pe.`period` = 8 THEN "2021-09-01"
					WHEN pe.`period` = 9 THEN "2021-10-01"
					WHEN pe.`period` = 10 THEN "2021-11-01"
					WHEN pe.`period` = 11 THEN "2021-12-01"
					END
				, pe.`date2` = CASE
					WHEN pe.`period` = 1 THEN "2021-02-28"
					WHEN pe.`period` = 2 THEN "2021-03-31"
					WHEN pe.`period` = 3 THEN "2021-04-30"
					WHEN pe.`period` = 4 THEN "2021-05-31"
					WHEN pe.`period` = 5 THEN "2021-06-30"
					WHEN pe.`period` = 6 THEN "2021-07-31"
					WHEN pe.`period` = 7 THEN "2021-08-31"
					WHEN pe.`period` = 8 THEN "2021-09-30"
					WHEN pe.`period` = 9 THEN "2021-10-31"
					WHEN pe.`period` = 10 THEN "2021-11-30"
					WHEN pe.`period` = 11 THEN "2021-12-31"
					END
			WHERE  pe.`formid`="info" AND pe.`part`="period"
				AND p.`ownertype` IN ("graduate","people","student")
				AND university.`tpid` IN (3136)';

		mydb::query($stmt);
		$ret .= '<p>Updated มหาวิทยาลัยราชภัฏภูเก็ต <b>'.number_format(mydb()->_affected_rows).'</b> rows.</p>';
		$ret .= '<pre>'.mydb()->_query.'</pre>';

		return $ret;
	}


	// View Model
	new Toolbar($self, 'REPAIR EMPLOYEE PERIOD DATE');


	$ret = '';
	$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/employee/period/date').'" data-rel="#result" data-title="ซ่อมแซมวันที่งวดรายงาน" data-confirm="กรุณายืนยัน?">START REPAIR PERIOD DATE</a></nav>';

	$ret .= '<div id="result"></div>';

	return $ret;
}
?>