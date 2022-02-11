<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/

function flood_forcast_avg($self) {
	$self->theme->title='Rain Forcast';

	$colors24hr=array(
							0=>'#FFFFFF',
							0.4=>'#22C597',
							1=>'#22C069',
							2=>'#20C83F',
							5=>'#21BD24',
							7=>'#40BF25',
							10=>'#6DC828',
							15=>'#9CC92B',
							20=>'#D0C92E',
							25=>'#C9B229',
							30=>'#CC9C26',
							40=>'#DC8E26',
							50=>'#E47925',
							70=>'#F06323',
							90=>'#E94621',
							120=>'#CF121D',
							150=>'#F2194D',
							200=>'#E81B7C',
							250=>'#C01B94',
							300=>'#BE21BE',
							350=>'#6C158D',
							400=>'#29094E',
							);

	$colors1hr=array(
							90=>'#29094E',
							80=>'#6C158D',
							70=>'#BE21BE',
							60=>'#C01B94',
							50=>'#E81B7C',
							40=>'#F2194D',
							35=>'#CF121D',
							30=>'#E94621',
							25=>'#F06323',
							20=>'#E47925',
							15=>'#DC8E26',
							10=>'#CC9C26',
							'7.5'=>'#C9B229',
							5=>'#D0C92E',
							'2.5'=>'#9CC92B',
							'1.5'=>'#6DC828',
							1=>'#40BF25',
							'0.8'=>'#21BD24',
							'0.6'=>'#20C83F',
							'0.4'=>'#22C069',
							'0.2'=>'#22C597',
							'0'=>'#FFFFFF',
							);

	$dateForcast='2016-04-28';
	$hr=SG\getFirst(post('hr'),1);
	$hrCount=168;
	if ($hr>$hrCount) $hr=$hrCount;
	if ($hr<1) $hr=1;
	$dateShow=date('Y-m-d H:i',strtotime($dateForcast.' +'.intval($hr-1).' hour'));
	$self->theme->title.=' : '.$dateShow.' UTC';
	$file=dirname(__FILE__).'/2016-04-28/2016-04-28_00UTC_esri_rain1hr_d02_asc/esri_rain1hr_d02_hour'.sprintf('%03d',$hr).'.asc';
	//$file=dirname(__FILE__).'/day/esri_rain24hr_d02_day'.sprintf('%01d',$hr).'.asc';


	$ret.='<div class="timebar">'._NL;
	$hrCount=0;
	$ret.='<ul>';
	for($day=1;$day<=7;$day++) {
		$ret.='<li class="day">';
		$ret.='<span class="title">'.date('Y-m-d',strtotime($dateForcast.' +'.intval($day-1).' day')).'</span>';
		for ($j=0;$j<24;$j++) {
			$hrCount++;
			$ret.='<span id="hr-'.$hrCount.'" class="hour" data-hour="'.$hrCount.'"></span>';
		}
		$ret.='</li>'._NL;
	}
	$ret.='</ul>';
	$ret.='</div>';

	$ret.='<div class="info">';
	$ret.='<h2 id="forcast-title">'.$self->theme->title.'</h2>';
	$self->theme->title='';
	$self->theme->option->header=false;
	$self->theme->option->title=false;
	//$ret.=$file.'<br />';
	//$ret.=print_o($cells,'$cells');
	$ret.='<span id="forcast-time">00:00</span>';
	$ret.='Date forcast <span id="forcast-date">'.$dateForcast.' +'.intval($hr).' hour is '.$dateShow.'</span><br />';

	$ret.='Max value = <span id="forcast-max">'.$max.'</span><br />';

	$ui=new ui();
	$ui->add('<a href="'.url('flood/forcast').'">Rain forcast : หน้าหลัก</a>');
	$ui->add('<a href="'.url('flood/forcast/show',array('hr'=>$hr)).'">Rain forcast : ภาพถ่าย</a>');
	$ui->add('<a href="'.url('flood/forcast/avg',array('hr'=>$hr)).'">Rain forcast : Google Map</a>');
	$ret.=$ui->build('ul');

	//$ret.=print_o(array_reverse($colors1hr,true),'$colors1hr');
	$ret.='<ul class="colorbar">';
	foreach ($colors1hr as $ci=>$color) {
		$ret.='<li><span style="background:'.$color.';">&nbsp;</span>'.$ci.'</li>';
	}
	$ret.='</ul>';
	$ret.='</div>';

	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.cfg('gmapkey').'&amp;language=th"></script>');
	

	$ret.='<div class="map-output result">'._NL;
	$ret.='<div class="map-canvas" id="map-canvas" width="600" height="800" style="width:100%;height:100%;">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>'._NL;


	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var map;
		var cellSize=0.0813616161616162;
		var imgSize = new google.maps.Size(16, 16);
		var gis={};
		var is_point=false;
		var forcastIdx=1;
		var polygonList={};
		var maxForcast=168;

initMap();



function initMap() {
  // Create a map object and specify the DOM element for display.
  var map = new google.maps.Map(document.getElementById("map-canvas"), {
    center: {lat: -34.397, lng: 150.644},
    scrollwheel: false,
    zoom: 8
  });
}




/*
		map = new GMaps({
				el: "#map-canvas",
				lat: 13.5000,
				lng: 101.4000,
				zoom: 6,
			});


		(function pollServerForNewMail() {
			//notify(url+"get"+"?hr="+forcastIdx)
			$.getJSON(url+"flood/forcast/get",{"hr":forcastIdx},function(data) {
				if (data.title) {
				}

				$("#forcast-title").html(data.title);
				$("#forcast-date").html(data.date);
				$("#forcast-time").html(data.time);
				$("#forcast-max").html(data.max);

				// Clear polygon
				for (var poly in polygonList){
					map.removePolygon(polygonList[poly]);
				}
				polygonList={}

				$(".hour").removeClass("active");
				$("#hr-"+forcastIdx).addClass("active");
				// Draw new polygon
				drawForcast(data.gis);

				forcastIdx++;
				if (forcastIdx>maxForcast) forcastIdx=1;
				setTimeout(pollServerForNewMail, 500);
			});
		}());


		function getForcast(idx) {
			notify(url+"get"+"?hr="+idx)
			$.getJSON(url+"flood/forcast/get",{"hr":idx},function(data) {
				//alert("complate "+data)
				//alert(data.gis)
				$("#forcast-title").html(data.title);
				$("#forcast-date").html(data.date);
				$("#forcast-max").html(data.max);
				drawForcast(data.gis);
			});
		}

		function drawForcast(gis) {
			$.each( gis.markers, function(i, marker) {
				//notify(marker.latitude+","+marker.longitude)
				if (marker.level>0) {
					var path=[
										[marker.latitude,marker.longitude],
										[marker.latitude,marker.longitude+cellSize],
										[marker.latitude+cellSize,marker.longitude+cellSize],
										[marker.latitude+cellSize,marker.longitude],
										]
					polygon = map.drawPolygon({
											paths: path, // pre-defined polygon shape
											strokeColor: "#FF0000",
											strokeOpacity: 0,
											strokeWeight: 1,
											fillColor: marker.color,
											fillOpacity: 0.8,
											clickable: false,
										});
					polygonList[++i]=polygon
				}
			});
		}

		$(".hour").click(function() {
			forcastIdx=$(this).data("hour");
			$(this).addClass("active");
		});
*/
	});

	--></script>';


	$ret.='<style type="text/css">
	body#flood #main {margin:0;}
	.toolbar {margin-bottom:20px;}
	.result {width:400px; height:780px; float:left;}
	.info {width:300px; float:left;}
	.map {width:434px; position:absolute;border:1px #333 solid;opacity:.5;pointer-events: none;}
	.colorbar {margin:0; padding:0; list-style-type:none;}
	.colorbar>li>span {width:20px;height:20px;margin:0 10px 0 0;border:1px #999 solid;display:inline-block;border-bottom:none;}
		.colorbar>li:last-child>span {border-bottom:1px #999 solid;}
	.timebar {}
		.timebar .day {margin:0 1px 2px 0; display: block; width: 13.5%; float: left; border:1px #ddd solid;}
			.timebar .title {margin:0; padding: 4px; display: block; text-align:center; background:#eee;}
			.timebar .hour {display: inline-block; width:4.1667%; height:10px; cursor: pointer; background: #CCCDCC;}
			.timebar .hour:hover {background:#ddd;}
			.timebar .active {background:#666; border-radius:4px;}
	#forcast-time {font-size:4em; display: block;}
	</style>';
	return $ret;
}
?>