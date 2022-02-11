<?php
function flood_monitor_showradar($self) {
	$ret.='<h3>รายงานสภาพอากาศล่าสุด</h3><ul class="flood-monitor-center-expand"><li><a href="'.url('flood/monitor/center',array('show'=>'radar')).'" target="_blank"><i class="icon -material">play_circle_outline</i></a></li><li><a href="'.url('flood/monitor/center',array('show'=>'radar','layout'=>'2')).'" target="_blank"><i class="icon -material">search</i></a></li></ul>'._NL.'
<div class="flood__slide">
<ul>
<li class="flood__m3--radar"><h4>เรดาร์สทิงพระ</h4><p>ที่มา : ศูนย์อุตุนิยมวิทยาภาคใต้ฝั่งตะวันออก</p><img class="" src="http://weather.tmd.go.th/stp/stp240Loop.gif" height="100%" width="100%" /></li>
<li class="flood__m3--rf"><h4>ปริมาณฝนออนไลน์</h4><p>ที่มา : ศูนย์อุตุนิยมวิทยาภาคใต้ฝั่งตะวันออก</p><iframe width="100%" height="100%" src="http://www.songkhla.tmd.go.th/RF/Monitor/" frameborder="0" scrolling="yes" style="margin:0;overflow:auto;"></iframe></li>
<li class="flood__m3--sat"><h4>ภาพถ่ายดาวเทียมกรมอุตุนิยมวิทยา</h4><p>ที่มา : <a href="http://www.sattmet.tmd.go.th/newversion/index3Frame-2.htm" target="_blank">The Weather Channel</a></p><a href="http://www.sattmet.tmd.go.th/disk2/Olddata/remapdata/forweb/SEch2.jpg" class="sg-action" data-rel="img"><img src="http://www.sattmet.tmd.go.th/disk2/Olddata/remapdata/forweb/SEch2.jpg" width="100%" height="100%" alt="ภาพถ่ายดาวเทียม" /></a></li>
<li class="flood__m3--sat"><h4>ภาพถ่ายดาวเทียม</h4><p>ที่มา : <a href="http://image.weather.com/images/sat/asiasat_720x486.jpg" target="_blank">The Weather Channel</a></p><a href="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg" class="sg-action" data-rel="img"><img src="http://hatyaicityclimate.org/file/fl/weather/lastphoto.jpg" width="100%" height="100%" alt="ภาพถ่ายดาวเทียม" /></a></li>
<li class="flood__m3--weather"><h4>แผนที่อากาศกรมอุตุนิยมวิทยา</h4><p>ที่มา : ศูนย์อุตุนิยมวิทยาภาคใต้ฝั่งตะวันออก</p><a href="https://tiwrm.hii.or.th/TyphoonTracking/wxImages/lastest_wc.jpg" class="sg-action" data-rel="img"><img src="https://tiwrm.hii.or.th/TyphoonTracking/wxImages/lastest_wc.jpg" width="100%" height="100%" alt="แผนที่อากาศกรมอุตุนิยมวิทยา" /></a><a href="http://www.tmd.go.th/weather_map.php">Source</a></li>
</ul>
</div>';
	$ret.='<script type="text/javascript">
			$(".flood__center--layout1 .flood__slide").easySlider({
			auto: true,
			continuous: true,
			pause: 5000,
			speed: 500,
			controlsShow: true,
			hoverpause : true,
			debug : false,
			onBeginSlide : function(slideID) {},
		});
		</script>';
	return $ret;
}
?>