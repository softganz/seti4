<?php
/**
* Project :: U2T Job Type Report
* Created 2022-01-30
* Modify  2022-01-30
*
* @return Widget
*
* @usage project/report/u2t/jobtype
*/

class ProjectReportU2tTraining extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'การอบรม',
				'leading' => '<i class="icon -material">insights</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => ['ทักษะในด้าน', 'total -amt -nowrap' => 'จำนวนครั้ง'],
						'children' => array_map(
							function ($item) {
								$trainTypeList = [
									1 => 'Digital Literacy',
									2 => 'English Competency',
									3 => 'Financial Literacy',
									4 => 'Social Literacy',
									99 => 'อื่นๆ',
								];

								return [
									$trainTypeList[$item->trainType],
									number_format($item->trainCount)
								];
							},
							ActionModel::trainType()
						),
					]), // Table
					// new DebugMsg(mydb()->_query),
				], // children
			]), // Widget
		]);
	}
}

class ActionModel {
	public static function trainType() {
		mydb::where('a.`formId` = "info" AND a.`part` = "train"');
		mydb::where('a.`refid` IS NOT NULL');
		return mydb::select(
			'SELECT
			a.`refid` `trainType`, COUNT(*) `trainCount`
			FROM %project_tr% a
			%WHERE%
			GROUP BY `trainType`
			'
		)->items;
	}
}
?>