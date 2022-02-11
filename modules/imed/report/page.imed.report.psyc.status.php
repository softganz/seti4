<?php
/**
* iMed :: Psyc Status Report
* Created 2021-08-29
* Modify  2021-08-29
*
* @param Array $_REQUEST
* @return Widget
*
* @usage imed/report/psyc/status
*/

$debug = true;

import('widget:imed.admit.status');

class ImedReportPsycStatus extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สถานะผู้ป่วยจิตเวช',
			]), // AppBar
			'body' => new Container([
				'children' => [
					new ImedAdmitStatusWidget([]),
				], // children
			]), // Container
		]);
	}
}
?>