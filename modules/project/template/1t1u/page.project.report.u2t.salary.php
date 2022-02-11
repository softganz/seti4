<?php
/**
* Project :: U2T Salary Report
* Created 2022-01-30
* Modify  2022-01-30
*
* @return Widget
*
* @usage project/report/u2t/salary
*/

class ProjectReportU2tSalary extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'การรับเงินเดือน',
				'leading' => '<i class="icon -material">insights</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						// 'thead' => ['ประเภทงาน', 'total -amt -nowrap' => 'จำนวนครั้ง'],
						'children' => array_map(
							function ($item) {
								$jobTypeList = [];

								return [
									$jobTypeList[$item->jobType],
									number_format($item->typeCount)
								];
							},
							ActionModel::jobType()
						),
					]), // Table
					// new DebugMsg(mydb()->_query),
				], // children
			]), // Widget
		]);
	}
}

class ActionModel {
	public static function jobType() {
		return [];
		// mydb::where('a.`formId` = "activity" AND a.`part` = "owner"');
		// mydb::where('a.`detail4` IS NOT NULL');
		// return mydb::select(
		// 	'SELECT
		// 	a.`detail4` `jobType`, COUNT(*) `typeCount`
		// 	FROM %project_tr% a
		// 	%WHERE%
		// 	GROUP BY `jobType`
		// 	'
		// )->items;
	}
}
?>