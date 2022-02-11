<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_monitor_links($self) {
	$self->theme->title.=' : ข้อมูลภัยพิบัติทั่วโลก';

	R::View('flood.monitor.toolbar',$self);

	$link1=array(
					array('url'=>'tmd.go.th','title'=>'กรมอุตุนิยมวิทยา','photo'=>'http://tmd.go.th/images/logo.gif'),
					array('url'=>'hydro-8.com','title'=>'ศูนย์อุทกวิทยาและบริหารน้ำภาคใต้','photo'=>'http://hydro-8.com/main/images/RID.png'),
					array('url'=>'www.satda.tmd.go.th','title'=>'วิเคราะห์ภาพดาวเทียม','photo'=>'http://www.satda.tmd.go.th/monitoring/persiann-24latest.gif'),
					);
	$link2=array(
					array('url'=>'noaa.gov','title'=>'NOAA - National Oceanic and Atmosphereic Administration','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-noaa.png'),
					array('url'=>'wunderground.com','title'=>'Weather Underground','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-wu.png'),
					array('url'=>'westernpacificweather.com','title'=>'Western Pacific Weather','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-westernpacificwx.png'),
					/*'nrimry.navy.mil','usno.navi.mil',*/
					array('url'=>'wx.aerisweather.com','title'=>'AERIS Weather','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-aeris.png'),
					array('url'=>'gdacs.org','title'=>'GDACS Global Disaster Alert and Coordination System','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-gdacs.png'),
					array('url'=>'www.pdc.org','title'=>'Pacific Disaster Center','photo'=>'http://www.nadrec.psu.ac.th/file/ad/logo-pdc.png'),
					);

	$ret.='<div class="flood__links">'._NL;
	$ret.='<h3>ข้อมูลภัยพิบัติของเว็บไซต์ประเทศไทย</h3>';
	$ret.=__showlink($link1);

	$ret.='<h3>ข้อมูลภัยพิบัติของเว็บไซต์ต่างประเทศ</h3>';
	$ret.=__showlink($link2);
	$ret.='</div>';
	return $ret;
}

function __showlink($link) {
	$ret.='<ul>';
	foreach ($link as $value) {
		$ret.='<li>';
		$ret.='<a href="http://'.$value['url'].'" target="_blank">';
		if ($value['photo']) $ret.='<img src="'.$value['photo'].'" width="200" height="200" />';
		$ret.=$value['title'].'</a></li>';
	}
	$ret.='</ul>';
	return $ret;
}
?>