<?php
/*
 * Private - Show patient vitalsign
 *
 * @return String
 */
function view_imed_patientmenu() {
	$ret.='<ul id="patient-info">';
	$ret.='<li class="patient--type--info"><a href="'.url('imed/patient/individual').'" title="ข้อมูลส่วนบุคคลของสมาชิก"><i class="icon -person"></i><span class="-sg-is-desktop">บุคคล</span></a></li>';
	$ret.='<li class="patient--type--chronic"><a href="'.url('imed/patient/health').'" title="ข้อมูลด้านสุขภาพของสมาชิก"><i class="icon -heart"></i><span class="-sg-is-desktop">สุขภาพ</span></a></li>';
	$ret.='<li class="patient--type--disabled"><a href="'.url('imed/patient/disabled').'" title="ข้อมูลลักษณะการพิการของคนพิการ"><i class="icon -disabled-people"></i><span class="-sg-is-desktop">คนพิการ</span></a></li>';
	$ret.='<li class="patient--type--elder"><a href="'.url('imed/patient/rehab').'" title="ผู้ป่วยรอการฟื้นฟู"><i class="icon -rehabilitation"></i><span class="-sg-is-desktop">ผู้ป่วยฟื้นฟู</span></a></li>';
	$ret.='<li class="patient--type--elder"><a href="'.url('imed/patient/elder').'" title="ข้อมูลผู้สูงอายุ"><i class="icon -elder"></i><span class="-sg-is-desktop">ผู้สูงอายุ</span></a></li>';
	$ret.='<li class="patient--type--visit"><a href="'.url('imed/patient/history').'" title="รวมบันทึกการรักษาและการเยี่ยมบ้าน"><i class="icon -doctor"></i><span class="-sg-is-desktop">เยี่ยมบ้าน</span></a></li>';
	$ret.='<li class="patient--type--map"><a href="'.url('imed/patient/map').'" title="แผนที่บ้าน"><i class="icon -pin"></i><span class="-sg-is-desktop">แผนที่</span></a></li>';
	$ret.='<li class="patient--type--poorman"><a href="'.url('imed/poorman/info').'" title="ข้อมูลคนยากลำบาก"><i class="icon -rehabilitation"></i><span class="-sg-is-desktop">ยากลำบาก</span></a></li>';
	$ret.='</ul>';

	return $ret;
}
?>