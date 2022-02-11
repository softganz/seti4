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

function flood_forcast_createimg($self) {
	$self->theme->title='Create Image for Rain Forcast';
	set_time_limit(0);
	$timer = -microtime(true);

	$dateForcast=post('d');
	$folder=post('f');
	$shape=post('shp');
	$shapeType=post('type');
	$shpPoint=SG\getFirst(post('shppoint'),20);
	$startHr=SG\getFirst(post('shr'),1);
	$countHr=post('chr');
	$drawValueMoreThan=SG\getFirst(post('lev'),2.5);

	$isSimulate=post('simu');

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
							//0=>'#FFFFFF',
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

	$colorsBarTemplate=$colors1hr;
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
	$shapeFile=$forcastFolder.'/shape.utp.json';
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
		$fileCount=__flood_forcast_avg_filecount($dataFolder);
		$endHr=$startHr+SG\getFirst($countHr,$fileCount)-1;
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
		$rainInArea=$rainPeriodSum=$rainPeriodAvg=0;
		$rainSumByPeriod=3;
		$digit=2;

		$result['table']='<h2>@'.date('Y-m-d H:i:s').'. Process '.($isSimulate?'SIMULATE':'').' $startHr='.$startHr.' $endHr='.$endHr.'</h2>'._NL;


		$tables = new Table();
		$tables->thead=array('latitude','longitude','Rain');
		//for($hr=1;$hr<=$dayFileCount;$hr++) {
		for($hr=$startHr;$hr<=$endHr;$hr++) {
			$fileHrName=sprintf('%03d',$hr);
			$file=$dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.$fileHrName.'.asc';
			//$result['table'].='Filename : '.$file.'<br />';

			$imgInitMapFilename=$forcastFolder.'/map_thai.jpg';
			$saveImgUrl='upload/forcast/forcast-'.$dateForcast.'-'.$utcName.'-hr'.$fileHrName.'.jpg';
			$saveImgFile=cfg('folder.abs').$saveImgUrl;

			$imgUrl=url($saveImgUrl);
			$result['img'].='<a class="sg-action" data-group="photo" href="'.$imgUrl.'" data-rel="img"><img src="'.$imgUrl.'" width="128" /></a> '._NL;

			$result['table'].='data file : '.$file.'<br />';
			$result['table'].='saveImgFile : '.$saveImgFile.'<br />';
			$result['table'].='imgUrl : '.$imgUrl.'<br />';

			if ($isSimulate) continue;

			// Load data
			$data = R::Page('flood.forcast.read',$self,$file);

			//$result['table'].=print_o($data,'$data');
			$dayRain+=$data['avg'];
			$dateShow=date('Y-m-d',strtotime($dateForcast.' +'.intval(($dayNo-1)*$dayFileCount+$hr-1).' hour'));
			if (!$cellSize && $data['cellsize']) $cellSize=$data['cellsize'];

			//$ret.='<p>Day rain avg'.$dateForcast.'_'.$timeForcast.'_'.$hr.'='.$data['avg'].'</p>';

			// rain summary on area
			/* Attempt to open */
			$im = @imagecreatefromjpeg($imgInitMapFilename);

	    $width = imagesx( $im );
	    $height = imagesy( $im );
	    $tc  = imagecolorallocate($im, 0, 0, 0);

	    $hex="#0000ff";
	    list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

	    $bgc = imagecolorallocate($im, $r, $g, $b);

			$lat=4.9030191919192;
			$long=98.94785959596;

			$imageNorthLat=4.24756+$cellSize*$data['nrows'];
			$imageSouthLat=4.24756-$cellSize/2;

			$imageWestLong=96.5075-$cellSize/2;
			$imageEastLong=96.5075+$cellSize*$data['ncols'];

			$imageLongPixels=$width;
			$imageLatPixels=$height;

			$pixelsPerLat=$imageLatPixels/($imageNorthLat-$imageSouthLat);
			$pixelsPerLong=$imageLongPixels/($imageEastLong-$imageWestLong);

			$xPixelPosition=($long-$imageWestLong)*$pixelsPerLong;
			$yPixelPosition=abs($lat-$imageNorthLat)*$pixelsPerLat;

			$pixelSize=2;

			//imagefilledrectangle($im, $xPixelPosition, $yPixelPosition, $xPixelPosition+$pixelSize, $yPixelPosition+$pixelSize, $bgc);

			//echo 'Create image '.$width.' : '.$height.'<br />';
			//echo '$xPixelPosition='.$xPixelPosition.' $yPixelPosition='.$yPixelPosition.'<br />';
			//echo '$pixelsPerLat='.$pixelsPerLat.' $pixelsPerLong='.$pixelsPerLong.'<br />';

			//$result['table'].=$imgInitMapFilename.' : '.$saveImgFile.'<br />';


			foreach ($data['gis']['markers'] as $dataItem) {
				$areaRainValue=$dataItem['value'];
				if ($areaRainValue<$drawValueMoreThan) continue;
				$color='';
				foreach ($colorsBarTemplate as $ci=>$color) {
					if ($areaRainValue>=$ci) {
						$color=$colorsBarTemplate[$ci];
						break;
					}
				}
				list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
				$bgc = imagecolorallocate($im, $r, $g, $b);

				$xPixelPosition=($dataItem['longitude']-$imageWestLong)*$pixelsPerLong;
				$yPixelPosition=abs($dataItem['latitude']-$imageNorthLat)*$pixelsPerLat;
				imagefilledrectangle($im, $xPixelPosition, $yPixelPosition, $xPixelPosition+$pixelSize, $yPixelPosition+$pixelSize, $bgc);
			}
	    imagestring($im, 1, 5, 5, 'DATE forcast-'.$dateForcast.'-hr'.$fileHrName, $tc);
			imagejpeg($im,$saveImgFile,90);
			imagedestroy($im);
			chmod($saveImgFile,0666);

			//if ($hr==1) $ret.=print_o($data,'$data');
			unset($data);
		}
		$result['table'].='<p><strong><big>Day rain average summary = '.number_format($dayRain,2).' m.m.</big></strong></p>'._NL;
		//$result['table'].=$tables->build();



		// Create rain summary area color
		foreach ($areaRain as $areaRainCoor=>$areaRainValue) {
			foreach ($colorsBarTemplate as $ci=>$color) {
				if ($areaRainValue>=$ci) {
					$color=$colorsBarTemplate[$ci];
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
		$result['remark'].='<p>Cell size = '.$cellSize.'</p>';
		if ($shape) {
			$result['remark'].='<p>Polygon '.count($polygon).' point = ';
			foreach ($polygon as $coor) $result['remark'].='('.$coor['x'].','.$coor['y'].') ';
		}
		return $result;
	}




	$folderList = R::Model('flood.forcast.folder', cfg('flood.forcast.folder'));



	$ret.='<div class="info">'._NL;
	$ret.='<h3><a href="'.url('flood/forcast/createimg').'">Rain Forcast Create Image</a></h3>';
	$ret.='<form method="get" action="'.url('flood/forcast/createimg').'">';
	$ret.='<input type="hidden" name="d" value="'.$dateForcast.'" />';
	$ret.='<input type="hidden" name="f" value="'.$folder.'" />';
	$ret.='<input type="hidden" name="day" value="'.$dayNo.'" />';
	$ret.='พื้นที่ :<br /><input type="radio" name="shp" value="" '.($shape==''?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"/> ทั้งประเทศ <input type="radio" name="shp" value="utp" '.($shape=='utp'?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;" /> ลุ่มน้ำคลองอู่ตะเภา<br />';
	$ret.='ขอบเขต :<br /><input type="radio" name="type" value="box" '.($shapeType=='box'?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;" /> Box <input type="radio" name="type" value="" '.($shapeType==''?'checked="checked"':'').' onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"/> Shape : ';
	$ret.=' <input type="text" name="shppoint" value="'.$shpPoint.'" size="1" style="text-align:center;" /> จุด<br />';
	$ret.='</form>';
	$ret.='<ul>';
	foreach ($folderList as $dateFolder => $mainFolder) {
		$ret.='<li><a href="javascript:void(0)">'.$dateFolder.'</a>';
		$ret.='<ul>';
		foreach ($mainFolder as $key => $subFolder) {
			if (substr($subFolder,-9)!='C_1hr_d03') continue;
			$ret.='<li><a class="forcast-get" href="'.url('flood/forcast/createimg',array('d'=>$dateFolder,'f'=>$subFolder,'shp'=>$shape,'type'=>$shapeType,'shppoint'=>$shpPoint)).'">'.$key.'</a>';
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
	$ret.='<h3>Rain Forcast Create Image <span>'.$dateForcast.' : '.$folder.'</span></h3>';

	/*
	if ($dateForcast) {
		$fileCount=__flood_forcast_avg_filecount($dataFolder);
		$ret.='<p class="forcast-day">';
		for ($i=1;$i<=$fileCount/$dayFileCount;$i++) {
			$ret.='<a class="button'.($i==$dayNo?' -active':'').'" href="'.url('flood/forcast/createimg',array('d'=>$dateForcast,'f'=>$folder,'shp'=>$shape,'type'=>$shapeType,'shppoint'=>$shpPoint,'day'=>$i)).'">Day '.$i.'</a> ';
		}
		$ret.='</p>';
	}
	*/

	$ret.='<div class="forcast-rainsum">';
	$ret.=$dateForcast?'<!-- <p class="forcast-loading"><span class="loading -small"></span> กำลังดำเนินการประมวลผล</p> -->':'<p class="notify">กรุณาเลือกชุดข้อมูลสำหรับประมวลผล</p>';
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
	.map-canvas>img {width:100%;}
	.colorbar {margin:2px 0 0 0; padding:0; list-style-type:none; position: absolute; z-index: 99999; right:2px; background:#fff;}
	.colorbar>li {font-size:10px; line-height:12px;}
	.colorbar>li>span {width:15px;height:12px;margin:0 4px 0 0;border:1px #999 solid;display:inline-block;border-bottom:none;}
		.colorbar>li:last-child>span {border-bottom:1px #999 solid;}
	.timebar {position: absolute; width:100%;}
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
		.map-canvas {width:429px;height:660px; float:left;}
		.forcast-rainsum {margin:0;}
		.colorbar {left:387px; right: auto;}
		#forcast-notify {width: 405px;}
	}
	</style>';


	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var forcastIdx=1;
		var maxForcast=168;
		var startHr='.$startHr.';
		var endHr='.($endHr?$endHr:0).';
		var i=0;



		// Get UTP Data
		var url="?flood/forcast/createimg";
		var param={};
		param.act="create";
		param.d="'.$dateForcast.'";
		param.f="'.$folder.'";
		param.day="'.$dayNo.'";
		param.lev="'.$drawValueMoreThan.'";
		param.chr=1;

		for (i=startHr; i<=endHr; i++) {
			param.shr=i;
			//$(".forcast-rainsum").prepend(i+"<br />");
			$.ajax({
							type: "GET",
							url: url,
							data: param,
							async: false,
							dataType: "json",
							success: function(data) {
													//var result=jQuery.parseJSON(data);
													$(".forcast-rainsum").prepend(data.table+data.img);
													//$("#result").prepend(result.html);
													//$("#loginfo").html(result.info);
												},
						});

			/*
			$.getJSON("?flood/forcast/createimg",{act: "get", d: "'.$dateForcast.'", f: "'.$folder.'", day: "'.$dayNo.'", lev:"'.$drawValueMoreThan.'", shr:"'.$startHr.'", chr: "'.$countHr.'"},function(data) {
				//notify("Get data")
				//alert(data)
				$(".forcast-rainsum").html(data.table);
				$(".forcast-remark").html(data.remark);

			});
			*/
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