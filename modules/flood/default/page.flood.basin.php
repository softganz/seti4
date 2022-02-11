<?php
function flood_basin($self,$basin=NULL) {
	$view=post('v');
	$basins=array('utapao'=>'ลุ่มน้ำคลองอู่ตะเภา','natawee'=>'ลุ่มน้ำคลองนาทวี','phumee'=>'ลุ่มน้ำคลองภูมี','mumbang'=>'ลุ่มน้ำคลองมำบัง');
	$views=array('weathermap'=>'แผนที่อากาศ','sat'=>'ภาพถ่ายดาวเทียม','radar'=>'เรดาห์','rain'=>'ปริมาณน้ำฝนออนไลน์','cctv'=>'CCTV','forcast'=>'สภาพอากาศ');

	$ret.='<div class="basininfo">'._NL;
	$ret.='<h2 class="basininfo__header'.($view?' basininfo__header--'.$basin:'').'">เฝ้าระวัง '.$basins[$basin].'</h2>';
	$ret.='<ul class="step'.($view?' step--basin':'').'">
<li class="step__box step--1"><div class="step__no">1</div><h3 class="step__header">ข้อมูลลุ่มน้ำ</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'info')).'" title="ข้อมูลลุ่มน้ำ"><img class="step__photo" src="http://southwarning.com/themes/default/basin-'.$basin.'.jpg" /></a></li>
<li class="step__box step--2"><div class="step__no">2</div><h3 class="step__header">สภาพอากาศ</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'forcast')).'" title="สภาพอากาศ"><img class="step__photo" src="http://southwarning.com/upload/pics/forcast.jpg" /></a></li>
<li class="step__box step--3"><div class="step__no">3</div><h3 class="step__header">แผนที่อากาศ</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'weathermap')).'" title="แผนที่อากาศ"><img class="step__photo" src="https://tiwrm.hii.or.th/TyphoonTracking/wxImages/lastest_wc.jpg" /></a></li>
<li class="step__box step--4"><div class="step__no">4</div><h3 class="step__header">ภาพถ่ายดาวเทียม</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'sat')).'" title="ภาพถ่ายดาวเทียม"><img class="step__photo" src="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg" /></a></li>
<li class="step__box step--5"><div class="step__no">5</div><h3 class="step__header">เรดาห์</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'radar')).'" title="เรดาห์"><img class="step__photo" src="http://hatyaicityclimate.org/file/fl/sathingphra/lastphoto.jpg" /></a></li>
<li class="step__box step--6"><div class="step__no">6</div><h3 class="step__header">ปริมาณน้ำฝนออนไลน์</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'rain')).'" title="ปริมาณน้ำฝนออนไลน์"><img class="step__photo" src="http://southwarning.com/upload/pics/rainonline.jpg" /></a></li>
<li class="step__box step--7"><div class="step__no">7</div><h3 class="step__header">CCTV</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'cctv')).'" title="CCTV"><img class="step__photo" src="http://hatyaicityclimate.org/file/fl/hatyainai/lastphoto.jpg" /></a></li>
<li class="step__box step--8"><div class="step__no">8</div><h3 class="step__header">อื่น ๆ</h3><a href="'.url('flood/basin/'.$basin,array('v'=>'etc')).'" title="ข้อมูลอื่น ๆ"><img class="step__photo" src="http://southwarning.com/themes/default/basin-'.$basin.'.jpg" /></a></li>
</ul>';
	$ret.='<br clear="all" /><h4 class="clear">'.$views[$view].'</h4>';
	$ret.='<div class="flood__basin">';
	switch ($view) {
		case 'info':
			$ret.='<h2>ข้อมูลลุ่มน้ำ '.$basins[$basin].'</h2><p class="notify">กำลังอยู่ระหว่างการดำเนินการ</p>';
			$ret.='<img class="" src="http://southwarning.com/themes/default/basin-'.$basin.'.jpg" class="left" />';
			break;

		case 'weathermap':
			$ret.='<img class="step__photo" src="https://tiwrm.hii.or.th/TyphoonTracking/wxImages/lastest_wc.jpg" />';
			break;

		case 'sat':
			$ret.='<img class="step__photo" src="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg" />';
			break;

		case 'radar':
			$ret.='<img class="step__photo" src="http://hatyaicityclimate.org/file/fl/sathingphra/lastphoto.jpg" />';
			$ret.='<div class="widget" style="width:220px;float:left;">
<h2>ภาพถ่ายจากดาวเทียมปัจจุบัน</h2>
<ul class="thumb">
<li><a href="http://hatyaicityclimate.org/flood/cam/11"><img src="http://image.weather.com/images/sat/asiasat_720x486.jpg"></a></li>
<li><a href="http://www.songkhla.tmd.go.th/satellite/digital-se.html"><img src="http://agora.ex.nii.ac.jp/digital-typhoon/region/SEasia/1/images/640x480/latest.jpg"></a></li>
<li><a href="http://www.songkhla.tmd.go.th/satellite/kochi-se.html"><img src="http://weather.is.kochi-u.ac.jp/SE/00Latest.jpg"></a></li>
<li><a href="http://www.songkhla.tmd.go.th/satellite/digital-large.html"><img src="http://agora.ex.nii.ac.jp/digital-typhoon/latest/globe/2048x2048/ir.jpg"></a></li>
</ul>
ที่มา <a href="http://www.songkhla.tmd.go.th/satellite/satellite.html">ศูนย์รวมภาพถ่ายดาวเทียม</a>
</div>

<!-- radar by image map -->
<div class="widget" style="width:670px;float:right;overflow:hidden;">
<h2><a href="http://www.songkhla.tmd.go.th/RadarSat/stp.html">ภาพเรดาร์ฯ สทิงพระ เวลาล่าสุด</a> </h2>
<iframe name="stp-last" src="http://www.songkhla.tmd.go.th/RadarSat/radar/stp_last.html" height="550px" width="670px" align="middle" scrolling="no" frameborder="0"></iframe>
<iframe name="stp-loop" src="'.cfg('flood.monitor.radar.gif').'" height="550px" width="670px" align="middle" scrolling="no" frameborder="0"> </iframe>
<p>สนับสนุนภาพถ่ายเรดาร์จาก <a href="http://www.songkhla.tmd.go.th">ศูนย์อุตุนิยมวิทยาภาคใต้ฝั่งตะวันออก</a> สามารถ<a href="http://www.songkhla.tmd.go.th/RadarSat/stp.html">ดูรายละเอียดภาพถ่ายเรดาร์พร้อมแผนที่</a>ได้</p>
<p>เวลาที่ปรากฏบนภาพเรดาร์เป็นเวลา UTC ซึ่งจะต้อง +7 ชั่วโมงเพื่อให้เป็นเวลาของประเทศไทย เช่น เวลาในภาพเรดาร์ = 1.00 เวลาในประเทศไทยจะเป็น 1.00+7 = 8.00 น.</p>
<p>ที่มา <a href="http://www.songkhla.tmd.go.th/RadarSat/stp.html">สถานีเรดาร์สทิงพระ</a></p>
</div>
<br clear="all" />

<style type="text/css">
ul.thumb {margin:0;padding:0;list-style-type:none;}
ul.thumb>li {margin:10px;float:none;}
ul.thumb>li img {width:100%;}
</style>';
			break;

		case 'rain':
			$ret.='<iframe width="100%" height="740" src="http://www.songkhla.tmd.go.th/RF/Monitor/" frameborder="0" scrolling="no" style="margin:0;overflow:hidden;"></iframe><h3>โปรแกรมแสดงผลและรายงานออนไลน์ปริมาณน้ำฝนโดยศูนย์อุตุนิยมวิทยาภาคใต้ฝั่งตะวันออก กรมอุตุนิยมวิทยา <a href="http://www.songkhla.tmd.go.th/RF/Monitor/">www.songkhla.tmd.go.th</a></h3>';
			break;

		case 'cctv':
			$ret.='<ul class="flood__cctv">
<!-- <li><h3>ม่วงก็อง</h3></li> -->
<li><h3>บางศาลา</h3><a href="'.url('flood/basin/camera',array('c'=>'3')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/bangsala/lastphoto.jpg" /></a></li>
<li><h3>คลอง ร.1</h3><a href="'.url('flood/basin/camera',array('c'=>'10')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/r1/lastphoto.jpg" /></a><p></p><p></p></li>
<li><h3>จันทร์วิโรจน์</h3><a href="'.url('flood/basin/camera',array('c'=>'2')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/janvirot/lastphoto.jpg" /></a></li>
<li><h3>ที่ว่าการอำเภอหาดใหญ่</h3><a href="'.url('flood/basin/camera',array('c'=>'1')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/hatyainai/lastphoto.jpg" /></a></li>
<li><h3>แก้มลิงคลองเรียน</h3><a href="'.url('flood/basin/camera',array('c'=>'5')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/gamling2/lastphoto.jpg" /></a></li>
<li><h3>ต้นคลอง ร.6</h3><a href="'.url('flood/basin/camera',array('c'=>'4')).'" class="sg-action" data-rel="#flood__cctv__photo"><img class="flood__cctv__photo" src="http://hatyaicityclimate.org/file/fl/gamling1/lastphoto.jpg" /></a></li>
</ul><div id="flood__cctv__photo"><img src="http://hatyaicityclimate.org/file/fl/bangsala/lastphoto.jpg" /></div>';
			break;
		case 'forcast':
			$ret.='<h2>พยากรณ์อากาศภาคใต้ฝั่งตะวันออก <!-- <a href="http://www.songkhla.tmd.go.th/detail/s_e.html"> --><a href="http://www.songkhla.tmd.go.th/index.php?modules=forecast" />www.songkhla.tmd.go.th</a></h2>
<iframe width="100%" height="840" src="http://www.songkhla.tmd.go.th/index.php?modules=forecast" frameborder="0" scrolling="yes" style="margin:0;overflow:auto;"></iframe>';
			break;

		case 'camera':
			break;

		case 'etc':
			$ret.='<h2>ข้อมูลอื่น</h2><p class="notify">กำลังอยู่ระหว่างการดำเนินการ</p>';
			break;

		default:
			# code...
			break;
	}
	$ret.='</div>'._NL;
	$ret.='</div><!-- basin__info -->'._NL;
	return $ret;
}
?>