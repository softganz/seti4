<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_forcast($self) {
	$self->theme->title='Rain Forcast';

	$isAdmin = user_access('access administrator pages,administrator floods');

	$ui=new Ui(NULL, 'ui-menu');
	$ui->add('<a href="'.url('flood/forcast/hr3').'">Rain forcast : ปริมาณฝนเฉลี่ยลุ่มน้ำคลองอู่ตะเภา hr3</a>');

	if ($isAdmin) {
		$ui->add('<a href="'.url('flood/forcast/avg').'">Rain forcast : ปริมาณฝนเฉลี่ยลุ่มน้ำคลองอู่ตะเภา (ยกเลิกการพัฒนา)</a>');
		$ui->add('<a href="'.url('flood/forcast/show').'">Rain forcast : ภาพถ่าย (ยกเลิกการพัฒนา)</a>');
		$ui->add('<a href="'.url('flood/forcast/gmap').'">Rain forcast : Google Map (ยกเลิกการพัฒนา)</a>');
		$ui->add('<a href="'.url('flood/forcast/gmapd03').'">Rain forcast d03 : Google Map (ยกเลิกการพัฒนา)</a>');
		$ui->add('<a href="http://tiservice.hii.or.th/wrf-roms/ascii/" target="_blank">ESRI Data (WRF-ROMS)</a>');
	}

	$ret.=$ui->build();

	return $ret;
}
?>