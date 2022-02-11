<?php
/**
* imed :: Elderly
* Created 2018-01-22
* Modify  2020-09-23
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function imed_elderly($self, $psnid = NULL) {
	$zones = imed_model::get_user_zone(i()->uid,'imed.poorman');

	$ret .= '<section>';

	$mainUi = new Ui(NULL, NULL);
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
	$mainUi->header('<h3>จัดการข้อมูล</h3>');
	if ($isLocalHost) {
		$mainUi->add('<a class="sg-action" href="'.url('imed/app').'"><i class="icon -material">home</i><span>หน้าแรก</span></a>');
	}
	$mainUi->add('<a class="sg-action" href="'.url('imed/app/poorman/list').'" data-webview="บันทึกข้อมูล"><i class="icon -material">list</i><span>บันทึกข้อมูล</span></a>');

	//$ret .= $mainUi->build();




	$mainUi = new Ui(NULL, NULL);
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
	$mainUi->header('<h3>รายงาน</h3>');

	$mainUi->add('<a class="sg-action" href="'.url('imed/report/elderarea').'" data-webview="จำแนกตามพื้นที่"><i class="icon -material">public</i><span>พื้นที่</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/map/elder').'" data-webview="แผนที่ผู้สูงอายุ"><i class="icon -material">place</i><span>แผนที่ผู้สูงอายุ</span></a>');

	$ret .= $mainUi->build();

	/*

	foreach ($zones as $zone) {
		if ($zone->right == 'admin') {
			$isAdmin = true;
			break;
		}
	}




	if ($isAdmin || is_admin()) {
		$mainUi = new Ui(NULL, NULL);
		$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
		$mainUi->header('<h3>ผู้จัดการระบบ</h3>');

		$mainUi->add('<a class="sg-action" href="'.url('imed/app/poorman/admin/memberqt').'" data-webview="แบบสอบถามของสมาชิก"><i class="icon -material">people</i><span>แบบสอบถามของสมาชิก</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/app/poorman/admin/summary').'" data-webview="จำนวนแแบบสอบถาม"><i class="icon -material">people</i><span>จำนวนแแบบสอบถาม</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/app/poorman/admin/cleardata').'" data-webview="CLEAR EMPTY DATA!!!"><i class="icon -material">cancel</i><span>CLEAR EMPTY DATA!!!</span></a>');

		$ret .= $mainUi->build();
	}
	*/
	
	$ret .= '</section>';
	return $ret;
}
?>