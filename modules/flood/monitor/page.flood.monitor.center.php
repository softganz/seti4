<?php
/**
* flood_monitor_main
*
* @param Object $self
* @return String
*/
function flood_monitor_center($self) {
	$self->theme->option->fullpage=true;
	$self->theme->toolbar='';
	cfg('page_id','flood-center');
	$basin=post('basin');
	$show=post('show');
	$layout=SG\getFirst(post('layout'),1);
	$self->theme->title='ศูนย์บัญชาการ';

	if ($action=post('action')) {
		switch($action) {
			case 'flag' :
				if (post('id') && post('flag')) {
					mydb::query('UPDATE %flood_station% SET `manualflag`=:flag WHERE `station`=:station LIMIT 1',
						':station',post('id'),':flag',post('flag')=='auto'?NULL:post('flag'));
				} else if (post('id')) {
					$rs=flood_model::get_station(post('id'));
					$ret.='<div class="flood__setflag flood--setflag"><h3>กำหนดการปักธง - สถานี '.$rs->title.' ('.post('id').')</h3>';
					$ret.='<h4>การยกธงโดยเจ้าหน้าที่</h4>';
					$ret.='<ul class="flood__setflag--flag">';
					$ret.='<li><a class="sg-action" href="'.url('flood/monitor/center',array('action'=>'flag','id'=>post('id'),'flag'=>'green')).'" data-rel="reload" data-confirm="ยืนยันการปักธงเขียว ใช่ไหรือไม่?" data-done="close"><img src="'._URL.'file/flood/site/flag-green.jpg" alt="ธงเขียว" width="32" /> <big>ธงเขียว</big></a></li>'._NL;
					$ret.='<li><a class="sg-action" href="'.url('flood/monitor/center',array('action'=>'flag','id'=>post('id'),'flag'=>'yellow')).'" data-rel="reload" data-confirm="ยืนยันการปักธงเหลือง ใช่ไหรือไม่?" data-done="close"><img src="'._URL.'file/flood/site/flag-yellow.jpg" alt="ธงเหลือง" width="32" /> <big>ธงเหลือง</big></a></li>'._NL;
					$ret.='<li><a class="sg-action" href="'.url('flood/monitor/center',array('action'=>'flag','id'=>post('id'),'flag'=>'red')).'" data-rel="reload" data-confirm="ยืนยันการปักธงแดง ใช่ไหรือไม่?" data-done="close"><img src="'._URL.'file/flood/site/flag-red.jpg" alt="ธงแดง" width="32" /> <big>ธงแดง</big></a></li>'._NL;
					$ret.='</ul>';
					$ret.='<h4>การยกธงโดยอัตโนมัต</h4>';
					$ret.='<ul class="flood__setflag--flag">';
					$ret.='<li><a class="sg-action" href="'.url('flood/monitor/center',array('action'=>'flag','id'=>post('id'),'flag'=>'auto')).'"data-rel="reload" data-confirm="ยืนยันการปักธงอัตโนมัติ ใช่ไหรือไม่?" data-done="close">เกณฑ์การยกธงมาตรฐานโดยใช้ระดับน้ำ - ค่าแนะนำ</a></li>'._NL;
					$ret.='</ul>';
					$ret.='</div>';
				} else {
					$ret .= 'Error!!!';
				}
				return $ret;
				break;

		}
	}
	//$ret.=print_o($basinDbs);

	$ret.='<div class="flood__center'.($basin?' flood__center--show':'').(' flood__center--layout'.$layout).'">'._NL;

	if (!$basin || $basin=='UPT') $ret.='<div class="flood-refresh flood__center--UPT" data-url="'.url('flood/monitor/showbasin',array('basin'=>'UPT')).'"><h3>Loading...</h3></div>'._NL;
	if (!$basin || $basin=='NWT') $ret.='<div class="flood-refresh flood__center--NWT" data-url="'.url('flood/monitor/showbasin',array('basin'=>'NWT')).'"><h3>Loading...</h3></div>'._NL;
	if (!$basin || $basin=='radar') $ret.='<div class="flood-refresh flood__center--radar" data-url="'.url('flood/monitor/showradar').'"><h3>Loading...</h3></div>'._NL;
	if (!$basin || $basin=='PMT') $ret.='<div class="flood-refresh flood__center--PMT" data-url="'.url('flood/monitor/showbasin',array('basin'=>'PMT')).'"><h3>Loading...</h3></div>'._NL;
	if (!$basin || $basin=='MBT') $ret.='<div class="flood-refresh flood__center--MBT" data-url="'.url('flood/monitor/showbasin',array('basin'=>'MBT')).'"><h3>Loading...</h3></div>'._NL;
	if (!$basin || $basin=='map') $ret.='<div class="flood-refresh flood__center--map" data-url="'.url('flood/monitor/showmap').'"><h3>Loading...</h3></div>'._NL;
	$ret.='</div>';


	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('<script type="text/javascript" src="/flood/js.flood.markerwithlabel.js"></script>');

	// ทุก ๆ 5 นาที ให้ไปดึงภาพแต่ละจุดสำคัญมาแสดง
	head('
	<script type="text/javascript">
	var refreshTime=300000; // 5 minute = 5*60*1000=300000 milisecond
	var isRefresh=true;
	var refreshActive=1;
	$(document).ready(function() {
		(function request() {
			console.log("Refresh Timer Start =========>");
	 		if (refreshActive) {
	 			console.log("Refresh start");

				$(".flood-refresh").each(function(index) {
					var $this=$(this);
					//console.log($this.data("url"));
					loadFlood($this);
					//updatePhotoHeight($this.attr("class"))
				});
			}

			//calling the anonymous function after refreshTime milli seconds
			console.log("Set refresh active");
			refreshActive=setTimeout(request, refreshTime);  //second
		})(); //self Executing anonymous function

		function loadFlood($this) {
			var url=$this.data("url");
			$this.addClass("-loading");
			$.get(url,function(html) {
				if (!html) {
					console.log(":: Refresh error ::");
					notify("อุ๊บ!!! เน็ตมีปัญหาซะแล้ว สักแป๊บจะลองติดต่อใหม่นะ",refreshTime/2);return false;
				} else {
					console.log("Refresh complete ::");
					$this.html(html);
					updatePhotoHeight()
				}
				$this.removeClass("-loading");
			});
		}

		function updatePhotoHeight(ele) {
			var maxPhotoHeight = 60
			//console.log("UPDATE HEIGHT ",ele)
			$(".flood-refresh .cctv").each(function(index){
				$(this).height("auto")
				maxPhotoHeight = $(this).height() > maxPhotoHeight ? $(this).height() : maxPhotoHeight
				//console.log("Height "+$(this).height()+" Max = "+maxPhotoHeight)
			})
			//console.log("MAX "+maxPhotoHeight)
			$(".flood-refresh .cctv").each(function(index){
				$(this).height(maxPhotoHeight+"px")
				//console.log("SET Height "+$(this).height()+" Max = "+maxPhotoHeight)
			})
		}

		updatePhotoHeight()
	});
	
	/*
	$(".flood-waterchange").each(function(index) {
		//console.log("Load data");
		var $this=$(this);
		var uri=$this.data("url");
		$.get(uri,function(html) {
			//console.log(html)
			$this.html(html);
		});
	});
	*/

	</script>
	<style>
	.notify-main {border-radius:40px;padding:0 !Important;background:#fff;}
	.-loading 
	</style>');

	return $ret;
}
?>