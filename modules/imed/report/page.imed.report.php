<?php
function imed_report($self) {
	$isAdmin=user_access('administer imeds');

	$ret = '';
	$self->theme->title='รายงาน';


	$ret .= '<div><h3>รายงานตามพื้นที่</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-rel="#imed-app">รายงานคนพิการแยกตามพื้นที่ <img src="/library/img/new.1.gif" alt="มาใหม่" /></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/elderarea').'" data-rel="#imed-app">รายงานผู้สูงอายุแยกตามพื้นที่ <img src="/library/img/new.1.gif" alt="มาใหม่" /></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/rehabarea').'" data-rel="#imed-app">รายงานผู้ป่วยติดเตียงแยกตามพื้นที่ <img src="/library/img/new.1.gif" alt="มาใหม่" /></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/poormanarea').'" data-rel="#imed-app">รายงานคนยากลำบากแยกตามพื้นที่ <img src="/library/img/new.1.gif" alt="มาใหม่" /></a>');
	$ret.=$ui->build();
	$ret.='</div>';


	$ret.='<div><h3>รายงานคนพิการ</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-rel="#imed-app">รายงานคนพิการแยกตามพื้นที่</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/homevisit').'" data-rel="#imed-app">รายงานการเยี่ยมบ้าน</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/disabilityinfo').'" data-rel="#imed-app">รายงานข้อมูลคนพิการ</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/newdisability').'" data-rel="#imed-app">รายชื่อคนพิการรายใหม่</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/prosthetic').'" data-rel="#imed-app">รายงานการได้รับกายอุปกรณ์</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/regexpire').'" data-rel="#imed-app">รายงานวันที่บัตรหมดอายุ</a>');
	//$ui->add('<a class="sg-action" href="'.url('imed/report/localmoney').'" data-rel="#imed-app">รายงานได้รับเบี้ยยังชีพคนพิการ</a>');
	$ret.=$ui->build();
	$ret.='</div>';


	$ret.='<div><h3>รายงานคนยากลำบาก</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/report/poormanarea').'" data-rel="#imed-app">รายงานคนยากลำบากแยกตามพื้นที่</a>');
	$ret.=$ui->build();
	$ret.='</div>';

	$ret.='<div><h3>รายงานผู้สูงอายุ</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/report/elderarea').'" data-rel="#imed-app">รายงานผู้สูงอายุแยกตามพื้นที่</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/elderllst').'" data-rel="#imed-app">รายชื่อผู้สูงอายุ</a>');
	$ret.=$ui->build();
	$ret.='</div>';

	$ret.='<div><h3>แผนที่สมาชิก - Human Mapping</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/map/patient').'" data-rel="#imed-app" data-done="moveto: 0,0">แผนที่ภาพรวม</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/map/defect').'" data-rel="#imed-app" data-done="moveto: 0,0">แผนที่ประเภทคนพิการ</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/map/helper').'" data-rel="#imed-app" data-done="moveto: 0,0">แผนที่ผู้ดูแลคนพิการ</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/map/elder').'" data-rel="#imed-app" data-done="moveto: 0,0">แผนที่ผู้สูงอายุ</a>');
	$ret.=$ui->build();
	$ret.='</div>';



	$ret.='<div><h3>รายงานทั่วไป</h3>';
	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/report/dupname').'" data-rel="#imed-app">รายชื่อซ้ำ</a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report/newperson').'" data-rel="#imed-app">รายชื่อเพิ่มใหม่</a>');
	$ret.=$ui->build();
	$ret.='</div>';

	if ($isAdmin) {
		$ret.='<div><h3>รายงานผู้จัดการระบบ</h3>';
		$ui = new Ui(NULL, 'ui-menu');
		//$ui->add('<a class="sg-action" href="'.url('imed/admin/report/disabled/list').'" data-rel="#imed-app">ส่งออกรายชื่อ</a>');
		$ret.=$ui->build();
		$ret.='</div>';
	}

	$ret .= '<style type="text/css">
	.imed-app .ui-menu {margin-bottom: 36px;}
	</style>';
	return $ret;
}
?>