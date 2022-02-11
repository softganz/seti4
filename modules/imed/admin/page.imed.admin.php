<?php
function imed_admin($self,$func=NULL) {
	if (!user_access('administer imeds')) return message('error','access denied');

	$ret.='<h3>ผู้จัดการระบบ</h3>';
	$ret.='<form id="search-member" class="sg-form search-box" method="get" action="'.url('imed/admin/member').'" role="search" data-rel="#imed-app"><input type="hidden" name="sid" id="sid" /><input class="sg-autocomplete" type="text" name="q" id="search-box" size="30" value="'.$_GET['q'].'" placeholder="Username or Name or Email" data-query="'.url('admin/get/username').'" data-callback="submit" data-xxx="'.url('imed/admin/user/').'" data-rel="#imed-app"><button><i class="icon -search"></i></button></form>';

	$stmt = 'SELECT `appsrc`,`appagent`,COUNT(*) `total` FROM %imed_service% GROUP BY `appsrc`,`appagent` ORDER BY `appsrc`, `appagent`';
	$appServiceCount = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('App Src', 'App Agent', 'amt -total'=>'Amount');
	foreach ($appServiceCount->items as $rs) {
		$tables->rows[] = array($rs->appsrc, $rs->appagent, $rs->total);
	}
	$ret .= $tables->build();

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -app-menu"}');
	$ui->add('<a class="sg-action" href="'.url('imed/admin/activity').'" data-rel="#imed-app"><i class="icon -material">playlist_add_check</i><span>กิจกรรมการแก้ไขข้อมูล</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/admin/visit').'" data-rel="#imed-app"><i class="icon -material">chat</i><span>บันทึกการเยี่ยมบ้าน</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/admin/member').'" data-rel="#imed-app"><i class="icon -material">people</i><span>จัดการสมาชิก-สิทธิ์การเข้าถึง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/admin/checkqt').'" data-rel="#imed-app"><i class="icon -material">playlist_add</i><span>ตรวจสอบแบบสอบถาม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/newperson').'" data-rel="#imed-app">รายชื่อมาใหม่</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/newdisability').'" data-rel="#imed-app">รายชื่อคนพิการใหม่</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/haveqt').'" data-rel="#imed-app">รายชื่อคนพิการป้อนแบบสอบถาม</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/addqtbyuser').'" data-rel="#imed-app">รายชื่อคนพิการป้อนแบบสอบถามตามผู้ป้อน</a>');


	$ui->add('<a class="sg-action" href="'.url('imed/admin/member/zone').'" data-rel="#imed-app">ผู้มีสิทธิ์เข้าถึงข้อมูลพื้นที่</a>');

	$ui->add('<a class="sg-action" href="'.url('imed/admin/stkcode').'" data-rel="#imed-app">รหัสกายอุปกรณ์ / แผนการดูแล</a>');
	$ret.=$ui->build();

	return $ret;
}
?>