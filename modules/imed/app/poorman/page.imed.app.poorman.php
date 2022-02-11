<?php
function imed_app_poorman($self,$psnid=NULL) {
	R::View('imed.toolbar',$self,'คนยากลำบาก','app.poorman');
	$isAdmin=user_access('administer imeds');

	$ui=new Ui(NULL,'ui-menu -main -poorman');
	$ui->add('<a class="btn -primary -fill" href="'.url('imed/app/poorman/list').'"><i class="icon -rehabilitation"></i><span>ฐานข้อมูลคนยากลำบาก</span></a>');
	$ui->add('<a class="btn -primary -fill" href="'.url('imed/app/poorman/report').'"><i class="icon -report"></i><span>วิเคราะห์</span></a>');

	$zones=imed_model::get_user_zone(i()->uid,'imed.poorman');
	foreach ($zones as $zone) {
		if ($zone->right=='admin') {
			$isAdmin=true;
			break;
		}
	}
	if ($isAdmin) $ui->add('<a class="btn -primary -fill" href="'.url('imed/app/poorman/admin').'"><i class="icon -setting"></i><span>ผู้จัดการระบบ</span></a>');
	$ret.=$ui->build();

	$ret.='<div class="policy" style="padding:16px;background:#fff;">ข้อมูลคนยากลำบากเป็นการเก็บข้อมูลโดยเครือข่ายจิตอาสาของแต่ละจังหวัด โดยเก็บข้อมูลส่วนบุคคลของผู้ยากลำบาก <b>ห้ามมิให้ผู้ใดนำข้อมูลส่วนบุคคลจากการสำรวจไปใช้เพื่อผลประโยชน์ทางธุรกิจหรือนำไปเผยแพร่ต่อสาธารณะโดยเด็ดขาด</b></div>';
	return $ret;
}
?>