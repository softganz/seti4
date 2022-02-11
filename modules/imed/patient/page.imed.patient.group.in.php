<?php
/**
* iMed :: Patient In Group List
* Created 2021-08-17
* Modify  2021-08-17
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/patient/{id}/ingroup
*/

$debug = true;

class ImedPatientGroupIn extends Page {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมาชิกของกลุ่ม',
			]),
			'body' => new Widget([
				'children' => [
					'<header class="header -box -hidden">'._HEADER_BACK.'<h3>สมาชิกของกลุ่ม</h3></header>',
					new Table([
						'thead' => ['กลุ่ม', 'เพิ่มโดย', 'create -date' => 'วันที่'],
						'children' => (function() {
							$rows = [];
							foreach (mydb::select('SELECT p.`orgid`, o.`name` `orgName`, u.`name` `ownerName` FROM %imed_socialpatient% p LEFT JOIN %db_org% o ON o.`orgid` = p.`orgid` LEFT JOIN %users% u ON u.`uid` = p.`addby` WHERE p.`psnid` = :psnId', ':psnId', $this->psnId)->items as $item) {
								$rows[] = [
									'<a href="'.url('imed/social/'.$item->orgid).'">'.$item->orgName.'</a>',
									$item->ownerName,
									sg_date($item->created, 'ว ดด ปปปป'),
								];
							}
							return $rows;
						})(), // children
					]), // Container
				],
			]), // Widget
		]);
	}
}
?>