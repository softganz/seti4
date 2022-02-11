<?php
/**
* Rain Forcast Average
*
* @param Object $self
* @return String
*
* Download rain forcast file from http://tiservice.hii.or.th/wrf-roms/ascii/
*/
require_once('class.pointlocation.php');

function flood_forcast_avg($self) {
	$self->theme->title='Rain Forcast Average';
	set_time_limit(0);
	$timer = -microtime(true);

	$dateForcast=post('d');
	$folder=post('f');
	$shape=post('shp');
	$shapeType=post('type');
	$shpPoint=SG\getFirst(post('shppoint'),20);
	$dayNo=SG\getFirst(post('day'),1);

	$isAdmin=user_access('access administrator pages,administrator floods');
	$forcastFolder=cfg('flood.forcast.folder');
	$dataFolder=$forcastFolder.'/'.$dateForcast.'/'.$folder;

	$cellSize=0;
	$zoom=6;
	$center=array('y'=>13.0427,'x'=>101.3887);
	$areaRain=array();
	$areaRainMarker=array();

	$result=array();

	$colors24hr=array(
							400=>'#29094E',
							350=>'#6C158D',
							300=>'#BE21BE',
							250=>'#C01B94',
							200=>'#E81B7C',
							150=>'#F2194D',
							120=>'#CF121D',
							90=>'#E94621',
							70=>'#F06323',
							50=>'#E47925',
							40=>'#DC8E26',
							30=>'#CC9C26',
							25=>'#C9B229',
							20=>'#D0C92E',
							15=>'#9CC92B',
							10=>'#6DC828',
							7=>'#40BF25',
							5=>'#21BD24',
							2=>'#20C83F',
							1=>'#22C069',
							//'0.4'=>'#22C597',
							0=>'#FFFFFF',
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
	/*
	$pointLocation = new pointLocation();
	$points = array("50 70","70 40","-20 30","100 10","-10 -10","40 -20","110 -20");
	$polygon = array("-50 30","50 70","100 50","80 10","110 -10","110 -30","-20 -50","-30 -40","10 -10","-10 10","-30 -20","-50 30");
	// The last point's coordinates must be the same as the first one's, to "close the loop"
	foreach($points as $key => $point) {
		$ret.= "point " . ($key+1) . " ($point): " . $pointLocation->pointInPolygon($point, $polygon) . "<br>";
	}
	*/

	// Get UTP shape
	$polygon=array();
	$shapeInfo->coordinates[0]->path=array();
	$shapeFile=$forcastFolder.'/shape.'.$shape.'.json';
	$shapeLines=file($shapeFile);
	$shapeInfo=sg_json_decode($shapeLines[0]);
	//$ret.=print_o($shapeInfo,'$shapeInfo');

	if ($shapeType=='box') {
    $xmin=$shapeInfo->coordinates[0]->bounding_box->xmin;
    $ymin=$shapeInfo->coordinates[0]->bounding_box->ymin;
    $xmax=$shapeInfo->coordinates[0]->bounding_box->xmax;
    $ymax=$shapeInfo->coordinates[0]->bounding_box->ymax;

		$polygon=array(	array('x'=>$xmin,'y'=>$ymin),
										array('x'=>$xmax,'y'=>$ymin),
										array('x'=>$xmax,'y'=>$ymax),
										array('x'=>$xmin,'y'=>$ymax),
										array('x'=>$xmin,'y'=>$ymin),
										);
	} else {
		$step=$shpPoint>0 && $shpPoint<=count($shapeInfo->coordinates[0]->path)?round(count($shapeInfo->coordinates[0]->path)/$shpPoint):1;
		if ($step<1) $step=1;
		foreach ($shapeInfo->coordinates[0]->path as $i=>$coor) {
			if (($i==0 || $i%$step==0)) {
				$polygon[]=array('x'=>$coor[0],'y'=>$coor[1]);
			}
		}
		$polygon[]=array('x'=>$shapeInfo->coordinates[0]->path[0][0],'y'=>$shapeInfo->coordinates[0]->path[0][1]);
	}

	if ($dateForcast) {
		$dayRain=0;
		$dayFileCount=24;
		list($utcName,$rainhrName,$dnoName)=explode('_',$folder);
		$hourName='hour';
		if ($rainhrName=='24hr') {
			$hourName='day';
			$dayFileCount=1;
		}
	}

	if (post('act') && $dateForcast) {
		//$ret.='$rainhrName='.$rainhrName.'<br />';
		$tables = new Table();
		$tables->thead=array('Date','Hour','Rain Summary','Area (block)','Rain Average');
		for($hr=1;$hr<=$dayFileCount;$hr++) {
			$file=$dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%03d',($dayNo-1)*24+$hr).'.asc';
			if ($rainhrName=='24hr') {
				$file=$dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%01d',($dayNo)*$hr).'.asc';
			}
			//$result['table'].='Filename : '.$file.'<br />';
			$data=R::Page('flood.forcast.read',$self,$file,$shape?$polygon:array());
			$dayRain+=$data['avg'];
			$dateShow=date('Y-m-d',strtotime($dateForcast.' +'.intval(($dayNo-1)*$dayFileCount+$hr-1).' hour'));
			if (!$cellSize && $data['cellsize']) $cellSize=$data['cellsize'];
			$tables->rows[]=array(
												$dateShow,
												$hr,
												number_format($data['sum'],2),
												$data['areaCount'],
												number_format($data['avg'],2),
												);
			//$ret.='<p>Day rain avg'.$dateForcast.'_'.$timeForcast.'_'.$hr.'='.$data['avg'].'</p>';

			// rain summary on area
			foreach ($data['gis']['markers'] as $dataItem) {
				$areaRain[$dataItem['longitude'].','.$dataItem['latitude']]+=$dataItem['value'];
			}
			//if ($hr==1) $ret.=print_o($data,'$data');
			unset($data);
		}

		$result['table'].='<p><strong><big>Day rain average summary = '.number_format($dayRain,2).' m.m.</big></strong></p>';
		$result['table'].=$tables->build();



		// Create rain summary area color
		foreach ($areaRain as $areaRainCoor=>$areaRainValue) {
			foreach ($colors24hr as $ci=>$color) {
				if ($areaRainValue>=$ci) {
					$color=$colors24hr[$ci];
					break;
				}
			}
			list($x,$y)=explode(',', $areaRainCoor);
			$areaRainMarker[]=array(
														'latitude'=>(double)$y,
														'longitude'=>(double)$x,
														'value'=>$areaRainValue,
														'level'=>$ci,
														'color'=>$color,
														);
		}
		$result['cellSize']=(double)$cellSize;
		$result['marker']=$areaRainMarker;

		//$ret.=print_o($areaRainMarker,'$areaRainMarker');

		$timer += microtime(true);
		$result['remark'].='<p class="clear">Execute time '.$timer.' seconds.</p>';
		$result['remark'].='<p>fileCount='.$fileCount.'</p>';
		$result['remark'].='<p>shapeFile='.($isAdmin?$shapeFile:basename($shapeFile)).'</p>';
		$result['remark'].='<p>Cell size = '.$cellSize.'</p>';
		if ($shape) {
			$result['remark'].='<p>Polygon '.count($polygon).' point = ';
			foreach ($polygon as $coor) $result['remark'].='('.$coor['x'].','.$coor['y'].') ';
		}
		return $result;
	}




	$folderList = R::Model('flood.forcast.folder', cfg('flood.forcast.folder'));



	$ret.='<div class="info">'._NL;
	$ret.='<h3><a href="'.url('flood/forcast/avg').'">Rain Forcast Average</a></h3>';
	$ret.='<form method="get" action="'.url('flood/forcast/avg').'">';
	$ret.='<input type="hidden" name="d" value="'.$dateForcast.'" />';
	$ret.='<input type="hidden" name="f" value="'.$folder.'" />';
	$ret.='<input type="hidden" name="day" value="'.$dayNo.'" />';
	$ret.='พื้นที่ :<br /><input type="radio" name="shp" value="" '.($shape==''?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"/> ทั้งประเทศ <input type="radio" name="shp" value="utp" '.($shape=='utp'?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;" /> ลุ่มน้ำคลองอู่ตะเภา <input type="radio" name="shp" value="satingphra" '.($shape=='satingphra'?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;" /> คาบสมุทรสทิงพระ<br />';
	$ret.='ขอบเขต :<br /><input type="radio" name="type" value="box" '.($shapeType=='box'?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;" /> Box <input type="radio" name="type" value="" '.($shapeType==''?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"/> Shape : ';
	$ret.=' <input type="text" name="shppoint" value="'.$shpPoint.'" size="1" style="text-align:center;" /> จุด<br />';
	$ret.='</form>';
	$ret.='<ul>';
	foreach ($folderList as $dateFolder => $mainFolder) {
		$ret.='<li><a href="javascript:void(0)">'.$dateFolder.'</a>';
		$ret.='<ul>';
		foreach ($mainFolder as $key => $subFolder) {
			$ret.='<li><a class="forcast-get" href="'.url('flood/forcast/avg',array('d'=>$dateFolder,'f'=>$subFolder,'shp'=>$shape,'type'=>$shapeType,'shppoint'=>$shpPoint)).'">'.$key.'</a>';
		}
		$ret.='</ul>';
		$ret.='</li>';
	}
	$ret.='</ul>';

	if ($isAdmin) {
		$ret.='<p><a href="http://tiservice.hii.or.th/wrf-roms/ascii/" target="_blank">ดาวน์โหลด esri_rain</a></p>';
	}

	//$ret.=print_o($folderList,'$folderList');
	$ret.='</div>';

	$ret.='<div class="result">'._NL;
	$ret.='<h3>Rain Forcast Average <span>'.$dateForcast.' : '.$folder.'</span></h3>';
	if ($dateForcast) {
		$fileCount=__flood_forcast_avg_filecount($dataFolder);
		$ret.='<p class="forcast-day">';
		for ($i=1;$i<=$fileCount/$dayFileCount;$i++) {
			$ret.='<a class="button'.($i==$dayNo?' -active':'').'" href="'.url('flood/forcast/avg',array('d'=>$dateForcast,'f'=>$folder,'shp'=>$shape,'type'=>$shapeType,'shppoint'=>$shpPoint,'day'=>$i)).'">Day '.$i.'</a> ';
		}
		$ret.='</p>';
	}

	$ret.='<ul class="colorbar">';
	foreach ($colors24hr as $ci=>$color) {
		$ret.='<li><span style="background:'.$color.';">&nbsp;</span>'.$ci.'</li>';
	}
	$ret.='</ul>';
	$ret.='<div class="map-canvas" id="map-canvas" width="600" height="800">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='<span id="forcast-notify">'.($dateForcast?'<span class="loading -small"></span>กำลังโหลดข้อมูลแผนที่':'').'</span>';
	$ret.='<div class="forcast-rainsum">';
	$ret.=$dateForcast?'<p class="forcast-loading"><span class="loading -small"></span> กำลังโหลดข้อมูลปริมาณฝนเฉลี่ยในลุ่มน้ำคลองอู่ตะเภา</p>':'<p class="notify">กรุณาเลือกชุดข้อมูลสำหรับแสดง</p>';
	$ret.='</div>';
	$ret.='<div class="forcast-remark"></div>';
	$ret.='</div>'._NL;




	$ret.='<style type="text/css">
	body#flood #main {margin:0;}
	#header-wrapper {height:130px; overflow: hidden;}
	h2.title {display:none;}
	.toolbar {margin-bottom:20px;}
	.result {background:#fff; position:relative;}
	.info {max-height: 200px; overflow: scroll; clear:both;}
		.info h3 {font-weight:normal; padding:3px 0; margin:0 0 10px 0; background: green; text-align: center;}
		.info h3>a {color:#fff;}
		.info ul {margin:0; font-weight: normal;}
		.info>ul {margin:0; padding:0; list-style-type:none; font-weight: bold;}
	.map-canvas {width:100%;height:300px;}
	.colorbar {margin:2px 0 0 0; padding:0; list-style-type:none; position: absolute; z-index: 99999; right:2px; background:#fff;}
	.colorbar>li {font-size:10px; line-height:12px;}
	.colorbar>li>span {width:15px;height:12px;margin:0 4px 0 0;border:1px #999 solid;display:inline-block;border-bottom:none;}
		.colorbar>li:last-child>span {border-bottom:1px #999 solid;}
	.timebar {}
		.timebar .day {margin:0 1px 2px 0; display: block; width: 13.5%; float: left; border:1px #ddd solid;}
			.timebar .title {margin:0; padding: 4px; display: block; text-align:center; background:#eee;}
			.timebar .hour {display: inline-block; width:4.1667%; height:10px; cursor: pointer; background: #CCCDCC;}
			.timebar .hour:hover {background:#ddd;}
			.timebar .active {background:#666; border-radius:4px;}
	#forcast-time {font-size:4em; display: block;}
	.forcast-day a {margin:0 4px 4px 0;}
	.forcast-rainsum {}
	.forcast-rainsum .item {margin:0;}
	.item td {text-align:center;}
	.button.-active {background:#0065bd; color:#fff; border-color: #357ebd;}
	.forcast-loading {text-align: center;}
	.loading.-small {margin:0 auto; padding:0; width:24px; height:24px; border: none; position: relative;		background-position:center center; display: block;}
	#forcast-notify {position: absolute; width: 100%; background:#fff; text-align: center;}

	@media (min-width:30em){    /* 480/16 = 30 */
		.info {width:200px; float:left; overflow:auto; max-height: none;}
		.result {margin-left:208px;}
		.map-canvas {width:100%;height:300px;}
		.forcast-rainsum {margin:0;}
	}

	@media (min-width:56.25em){    /* 900/16 = 56.25 */
		.map-canvas {width:405px;height:750px; float:left;}
		.forcast-rainsum {margin:0 0 0 410px;}
		.colorbar {left:363px; right: auto;}
		#forcast-notify {width: 405px;}
	}
	</style>';

	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.cfg('gmapkey').'&amp;language=th"></script>');
	head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');

	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var map;
		var cellSize='.$cellSize.';
		var zoom='.$zoom.';
		var imgSize = new google.maps.Size(16, 16);
		var gis={};
		var center='.json_encode($center).';
		var getShape='.json_encode($polygon).';
		var fullShape='.json_encode($shapeInfo->coordinates[0]->path).';
		var arrayRainMarker='.json_encode($areaRainMarker).';
		var is_point=false;
		var forcastIdx=1;
		var polygonList={};
		var maxForcast=168;

		/*
		initMap();

		function initMap() {
		  // Create a map object and specify the DOM element for display.
		  var map = new google.maps.Map(document.getElementById("map-canvas"), {
		    center: {lat: -34.397, lng: 150.644},
		    scrollwheel: false,
		    zoom: 8
		  });
		}
		*/

		map = new GMaps({
				el: "#map-canvas",
				lat: center.y,
				lng: center.x,
				zoom: '.$zoom.',
			});

		var path=[];

		$.each( getShape, function(i, marker) {
			path.push([marker.x,marker.y]);
		});

		if (fullShape) {
			polygon = map.drawPolygon({
									paths: fullShape, // pre-defined polygon shape
									strokeColor: "#001DFF",
									strokeOpacity: 1,
									strokeWeight: 1,
									fillColor: "#FFFFFF",
									fillOpacity: 0.0,
									clickable: false,
								});
		}

		if (path) {
			polygon = map.drawPolygon({
										paths: path, // pre-defined polygon shape
										strokeColor: "#FF0000",
										strokeOpacity: 1,
										strokeWeight: 1,
										fillColor: "#FFFFFF",
										fillOpacity: 0.0,
										clickable: false,
									});
		}

		// Get UTP Data
		$.getJSON("?flood/forcast/avg",{act: "get", d: "'.$dateForcast.'", f: "'.$folder.'", day: "'.$dayNo.'", shp: "'.$shape.'", shppoint: "'.$shpPoint.'"},function(data) {
			//notify("Get data")
			//alert(data)
			$(".forcast-rainsum").html(data.table);
			$(".forcast-remark").html(data.remark);


			//cellSize=data.cellSize;
			//drawForcast(data.marker);


			/*
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
			*/
		});

		// Get all data
		$.getJSON("?flood/forcast/avg",{act: "get", d: "'.$dateForcast.'", f: "'.$folder.'", day: "'.$dayNo.'"},function(data) {
			//notify("Get all data")
			//alert(data)
			cellSize=data.cellSize;
			$("#forcast-notify").html("<span class=\"loading -small\"></span>กำลังวาดแผนที่");
			drawForcast(data.marker);
		});

		//drawForcast(arrayRainMarker);

		function drawForcast(gis) {
			var minLevel=0;
			if (gis.length>200000) minLevel=10;
			else if (gis.length>100000) minLevel=5;
			else minLevel=1;
			var notifyMsg="Draw map "+gis.length+" points on rain level >= "+minLevel+" m.m.";
			notify(notifyMsg);
			var allDraw=0;
			$.each( gis, function(i, marker) {
				if (marker.level>=minLevel && allDraw<=100000) {
					//notifyMsg=notifyMsg+marker.level+" "+marker.latitude+","+marker.longitude+" "+marker.color+" ";
					//notify(marker.latitude+","+marker.longitude+" "+marker.color);
					var latLeft=marker.latitude-cellSize/2;
					var lngLeft=marker.longitude-cellSize/2
					var path=[
										[latLeft,lngLeft],
										[latLeft,lngLeft+cellSize],
										[latLeft+cellSize,lngLeft+cellSize],
										[latLeft+cellSize,lngLeft],
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
					allDraw++;
					//polygonList[++i]=polygon
				}
			});
			notify(notifyMsg+" Draw only "+allDraw+" points completed.");
			$("#forcast-notify").html("");
		}

	});
	--></script>';
	return $ret;
}

function __flood_forcast_avg_filecount($folder) {
	$fileCount=0;
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;
			if (is_file($folder.'/'.$entry)) $fileCount++;
		}
		closedir($handle);
	}
	return $fileCount;
}
?>