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

class ProjectReportU2tJobtype extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประเภทงาน',
				'leading' => '<i class="icon -material">insights</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => ['ประเภทงาน', 'total -amt -nowrap' => 'จำนวนครั้ง'],
						'children' => array_map(
							function ($item) {
								$jobTypeList = [
									'การวิเคราะห์ข้อมูล (Data Analytics)',
									'การเฝ้าระวัง ประสานงานและติดตามข้อมูลสถานการณ์การระบาดของ COVID-19 และโรคระบาดใหม่',
									'การจัดทำข้อมูลราชการในพื้นที่เป็นข้อมูลอิเล็กทรอนิกส์ (Digitalizing Government Data)',
									'การพัฒนาสัมมาชีพและสร้างอาชีพใหม่ (การยกระดับสินค้า OTOP/อาชีพอื่นๆ) การสร้างและพัฒนา Creative Economy (การยกระดับการท่องเที่ยว) การนำองค์ความรู้ไปช่วยบริการชุมชน (Health Care/เทคโนโลยีด้านต่างๆ) และการส่งเสริมด้านสิ่งแวดล้อม/Circular Economy (การเพิ่มรายได้หมุนเวียนให้แก่ชุมชน) ให้แก่ชุมชน',
									'อื่นๆ',
								];

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
		mydb::where('a.`formId` = "activity" AND a.`part` = "owner"');
		mydb::where('a.`detail4` IS NOT NULL');
		return mydb::select(
			'SELECT
			a.`detail4` `jobType`, COUNT(*) `typeCount`
			FROM %project_tr% a
			%WHERE%
			GROUP BY `jobType`
			'
		)->items;
	}
}
?>