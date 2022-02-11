<?php
function org_seedfund_toolbar($self) {
	//$ret.='<form method="get" action="'.url('org/member').'" id="search" class="search-box" data-query="'.url('org/api/person').'" role="search"><input type="text" name="qn" id="search-box" class="form-text" size="20" value="'.post('qn').'" placeholder="ป้อน ชื่อ นามสกุล หรือ เบอร์โทร"><input type="submit" class="button" value="ค้นหาสมาชิก" /></form>';
	$ui=new ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('org/seedfund').'"><span>หน้าหลัก</span><b>กองทุนเมล็ดพันธุ์</b></a>');
	$ui->add('<a class="sg-action" href="'.url('org/seedfund/need').'" '.(q()=='org/seedfund'?'data-rel="#org-seedfund-info"':'').'><span>ผู้ประสบภัย</span><b>แจ้งความต้องการ</b></a>');
	$ui->add('<a class="-disabled" href="'.url('org/seedfund/give').'"><span>ผู้ให้ความช่วยเหลือ</span><b>แจ้งบริจาคเมล็ดพันธุ์</b></a>');
	$ui->add('<a class="" href="'.url('org/seedfund/report').'"><span>รายงาน</span><b>สถานการณ์</b></a>');
	$ui->add('<a class="-disabled" href="'.url('org/seedfund/admin').'"><span>ผู้จัดการระบบ</span><b>จัดการข้อมูล</b></a>');

	$ret.='<nav class="nav -module -org-seedfund">'.$ui->build().'</nav>';

	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');
	head('js.org.seedfund.js','<script type="text/javascript" src="org/js.org.seedfund.js"></script>');
	head('js.map.js','<script type="text/javascript" src="/map/js.map.js"></script>');
	head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	$self->theme->toolbar=$ret;
}
?>