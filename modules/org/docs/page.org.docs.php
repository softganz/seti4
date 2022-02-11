<?php
/**
* Org :: Docs Home Page
* Created 2018-09-07
* Modify  2021-08-14
*
* @return Widget
*
* @usage org/docs
*/

$debug = true;

class OrgDocs extends Page {
	/**
		หนังสือเข้า
		- เลขที่หนังสือ
		- ลงวันที่
		- จากหน่วยงาน
		- เรียน
		- เรื่อง
		- รายละเอียดอย่างย่อ
		- ไป/ไม่ไป
		- ใครไป
		- ไฟล์

		หนังสือออก
		- เลขที่หนังสือ (Running)
		- ลงวันที่
		- ถึงหน่วยงาน
		- เรียน
		- เรื่อง
		- รายละเอียดอย่างย่อ
		- ไฟล์
	*/
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบงานสารบัญองค์กร',
			]),
			'body' => new Container([
				'children' => [
					new Table([
						'thead' => ['องค์กร', 'amt -out' => 'หนังสือออก', 'amt -in' => 'หนังสือเข้า'],
						'children' => (function() {
							$stmt = 'SELECT
								o.*
								, COUNT(IF(d.`doctype`="IN",1,NULL)) `docin`
								, COUNT(IF(d.`doctype`="OUT",1,NULL)) `docout`
								FROM %db_org% o
									LEFT JOIN %org_doc% d USING(`orgid`)
								GROUP BY `orgid`
								';
							$rows = [];
							foreach (mydb::select($stmt)->items as $rs) {
								$rows[] = [
									'<a href="'.url('org/docs/o/'.$rs->orgid).'">'.$rs->name.'</a>',
									$rs->docout,
									$rs->docin
								];
							}
							return $rows;
						})(),
					]),
				],
			]), // Container
		]);
	}
}
?>