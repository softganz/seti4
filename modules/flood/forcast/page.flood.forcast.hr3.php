<?php
/**
* Rain Forcast Average
*
* @param Object $self
* @return String
*
* Download rain forcast file from http://tiservice.hii.or.th/wrf-roms/ascii/
*/

function flood_forcast_hr3($self) {
	$self->theme->title = 'ฝนคาดการณ์เฉลี่ย';
	set_time_limit(0);
	$timer = -microtime(true);

	$dateForcast = post('d');
	$folder = post('f');
	$basinCode = SG\getFirst(post('shp'),'UPT');
	$shapeType = post('type');
	$shpPoint = SG\getFirst(post('shppoint'),20);
	$dayNo = SG\getFirst(post('day'),1);

	$basinList = cfg('basin');

	$basinName = $basinList[$basinCode];

	$isAdmin = user_access('access administrator pages,administrator floods');
	$forcastFolder = cfg('flood.forcast.folder');

	$result = array();
	$polygon = array();

	$folderList = R::Model('flood.forcast.folder', cfg('flood.forcast.folder'));


	if (empty($dateForcast)) {
		$stmt = 'SELECT * FROM %flood_f3day% WHERE `basincode` = :basincode ORDER BY `forid` DESC LIMIT 1';
		$lastForcast = mydb::select($stmt, ':basincode', $basinCode);
		//$ret .= print_o($lastForcast, '$lastForcast');
		$dateForcast = $lastForcast->dateforcast;
		$folder = $lastForcast->folder;
	}

	$utc = intval(substr($folder,0,2))+7;
	$dayRain = 0;
	$fileCount = 0;
	$dayFileCount = 72;
	list($utcName,$rainhrName,$dnoName) = explode('_',$folder);
	$hourName = 'hour';
	if ($rainhrName == '24hr') {
		$hourName = 'day';
		$dayFileCount = 1;
	}

	if ($dateForcast) {
		$dataFolder = $forcastFolder.'/'.$dateForcast.'/'.$folder;
		$fileCount = __flood_forcast_avg_filecount($dataFolder);
	}


	/*
	// Load data , calculate and return
	if (post('act') && $dateForcast) {
		// Get UTP shape
		$shapeInfo->coordinates[0]->path = array();
		$shapeFile = $forcastFolder.'/shape.'.$basinCode.'.json';
		$shapeLines = file($shapeFile);
		$shapeInfo = sg_json_decode($shapeLines[0]);

		unset($shapeInfo->coordinates[1]);
		//print_o($shapeInfo,'$shapeInfo',1);
		//$result['shapeInfo'] = $shapeInfo;
		//return $result;

		//$result['table'] .= 'BASIN '.$basinCode;


		// Generate boundery coordinates by box or step for rain area calculate
		if ($shapeType == 'box') {
	    $xmin = $shapeInfo->coordinates[0]->bounding_box->xmin;
	    $ymin = $shapeInfo->coordinates[0]->bounding_box->ymin;
	    $xmax = $shapeInfo->coordinates[0]->bounding_box->xmax;
	    $ymax = $shapeInfo->coordinates[0]->bounding_box->ymax;

			$polygon=array(	array('x'=>$xmin,'y'=>$ymin),
											array('x'=>$xmax,'y'=>$ymin),
											array('x'=>$xmax,'y'=>$ymax),
											array('x'=>$xmin,'y'=>$ymax),
											array('x'=>$xmin,'y'=>$ymin),
											);
		} else {
			$step = $shpPoint > 0 && $shpPoint <= count($shapeInfo->coordinates[0]->path) ? round(count($shapeInfo->coordinates[0]->path)/$shpPoint) : 1;
			if ($step < 1) $step = 1;
			foreach ($shapeInfo->coordinates[0]->path as $i => $coor) {
				if (($i == 0 || $i % $step == 0)) {
					$polygon[] = array('x' => $coor[0], 'y' => $coor[1]);
				}
			}

			$polygon[] = array(
										'x' => $shapeInfo->coordinates[0]->path[0][0],
										'y' => $shapeInfo->coordinates[0]->path[0][1],
									);
		}


		//$ret.='$rainhrName='.$rainhrName.'<br />';
		$rainInArea = $rainPeriodSum = $rainPeriodAvg = 0;
		$rainIn24hr = $rainIn48hr = $rainIn72hr = 0;
		$rainSumByPeriod = 3;
		$digit = 2;

		$tables = new Table();
		$tables->addClass('-rainall');
		//$tables->thead=array('Date','Hour','Rain Summary','Area (block)','Rain Average','ฝนสะสม');
		$tables->thead=array('วันที่','เวลา (น.)','เวลา (ชั่วโมง)','ฝนเฉลี่ย (mm.)','ฝนสะสม (mm.)');


		// Read each forcast file and calculete average rain
		for($hr = 1; $hr <= $dayFileCount; $hr++) {
			$file = $dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%03d',($dayNo-1)*24+$hr).'.asc';
			if ($rainhrName == '24hr') {
				$file = $dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%01d',($dayNo)*$hr).'.asc';
			}
			//$result['table'].='Filename : '.$file.'<br />';


			// Start read rain forcast data
			$data = R::Page('flood.forcast.read',$self, $file, $basinCode ? $polygon : array());


			$dayRain += $data['avg'];
			$startTimeForcast = strtotime($dateForcast.' +'.intval(($dayNo-1)*$dayFileCount+$hr+$utc-3).' hour');
			$dateShow = date('Y-m-d',$startTimeForcast);
			if (!$cellSize && $data['cellsize']) $cellSize = $data['cellsize'];

			$rainPeriodAvg += $data['avg'];
			$rainPeriodSum += $data['avg'];
			$rainInArea += $data['sum'];

			if ($hr <= 24) $rainIn24hr += $data['avg'];
			if ($hr <= 48) $rainIn48hr += $data['avg'];
			if ($hr <= 72) $rainIn72hr += $data['avg'];

			if ($hr % $rainSumByPeriod) continue;

			//$rainPeriodSum+=$rainPeriodAvg/$rainSumByPeriod;

			unset($sqlData);
			$sqlData->dateforcast = $dateForcast;
			$sqlData->folder = $folder;
			$sqlData->basincode = $basinCode;
			$sqlData->basinname = $basinName;
			$sqlData->dateshow = $dateShow;
			$sqlData->timeshow = date('H:i', $startTimeForcast);
			$sqlData->hourshow = ($hr-$rainSumByPeriod+1).'-'.$hr;
			$sqlData->rainavg = number_format($rainPeriodAvg,$digit, '.', '');
			$sqlData->rainsum = number_format($rainPeriodSum,$digit, '.', '');
			$sqlData->created = date('U');

			$stmt = 'INSERT INTO %flood_forcast%
							(`dateforcast`, `folder`, `basincode`, `basinname`, `dateshow`, `timeshow`, `hourshow`, `rainavg`, `rainsum`, `created`)
							VALUES
							(:dateforcast, :folder, :basincode, :basinname, :dateshow, :timeshow, :hourshow, :rainavg, :rainsum, :created)';
			mydb::query($stmt, $sqlData);
			$result['table'] .= print_o($sqlData,'$sqlData').mydb()->_query;

			$tables->rows[] = array(
												$dateShow,
												date('H:i', $startTimeForcast),
												($hr-$rainSumByPeriod+1).'-'.$hr,
												//number_format($rainInArea,$digit),
												//$data['areaCount'],
												//number_format($data['avg'],$digit),
												number_format($rainPeriodAvg,$digit),
												number_format($rainPeriodSum,$digit),
												);
			$rainPeriodAvg = $rainInArea = 0;
			//$ret.='<p>Day rain avg'.$dateForcast.'_'.$timeForcast.'_'.$hr.'='.$data['avg'].'</p>';

			//if ($hr==1) $ret.=print_o($data,'$data');
			unset($data);
		}

		unset($sqlData);
		$sqlData->dateforcast = $dateForcast;
		$sqlData->folder = $folder;
		$sqlData->basincode = $basinCode;
		$sqlData->basinname = $basinName;
		$sqlData->rainin24hr = number_format($rainIn24hr,$digit, '.', '');
		$sqlData->rainin48hr = number_format($rainIn48hr,$digit, '.', '');
		$sqlData->rainin72hr = number_format($rainIn72hr,$digit, '.', '');
		$sqlData->created = date('U');

		$stmt = 'INSERT INTO %flood_f3day%
						( `dateforcast`, `folder`, `basincode`, `basinname`, `rainin24hr`, `rainin48hr`, `rainin72hr`, `created` )
						VALUES
						( :dateforcast, :folder, :basincode, :basinname, :rainin24hr, :rainin48hr, :rainin72hr, :created )';
		mydb::query($stmt, $sqlData);
		$result['table'] .= print_o($sqlData,'$sqlData').mydb()->_query;

		$result['table'] .= '<h3>ฝนคาดการณ์เฉลี่ยใน '.$basinName.'</h3>';
		$sumTable = new Table();
		$sumTable->addClass('-rain3day');
		$sumTable->thead = array('เวลา (ชั่วโมง)','24','48','72');
		$sumTable->rows[] = array(
												'ฝนคาดการณ์เฉลี่ยใน'.$basinName.' (mm.)',
												number_format($rainIn24hr,2),
												number_format($rainIn48hr,2),
												number_format($rainIn72hr,2),
												);
		$result['table'] .= $sumTable->build();
		$result['table'] .= $tables->build();



		//$ret.=print_o($areaRainMarker,'$areaRainMarker');

		$timer += microtime(true);
		$result['remark'] .= '<p>Execute time '.$timer.' seconds.</p>';
		$result['remark'] .= '<p>fileCount='.$fileCount.'</p>';
		$result['remark'] .= '<p>Cell size = '.$cellSize.'</p>';
		if ($basinCode) {
			$result['remark'] .= '<p>Polygon '.count($polygon).' point = ';
			foreach ($polygon as $coor)
				$result['remark'] .= '('.$coor['x'].','.$coor['y'].') ';
		}
		return $result;
	}
	*/




	$stmt = 'SELECT * FROM %flood_f3day% WHERE `dateforcast` IN (:dateforcast)';
	$dbs = mydb::select($stmt, ':dateforcast', 'SET-STRING:'.implode(',',array_keys($folderList)));
	$readyCalculate = array();
	foreach ($dbs->items as $rs) $readyCalculate[$rs->dateforcast.':'.$rs->folder.':'.$rs->basincode] = $rs;

	// Show HTML & Script
	$ret.='<div class="info">'._NL;
	$ret.='<h3><a href="'.url('flood/forcast/hr3').'">ฝนคาดการณ์เฉลี่ย</a></h3>';

	$form = new Form(NULL, url('flood/forcast/hr3'));
	$form->addConfig('method', 'GET');
	$form->addField('d', array('type'=>'hidden','value'=>$dateForcast));
	$form->addField('f', array('type'=>'hidden','value'=>$folder));
	$form->addField(
		'shp',
		array(
			'type' => 'select',
			'label' => 'พื้นที่ :',
			'class' => '-fill',
			'options' => $basinList,
			'value' => $basinCode,
			'attr' => array('onChange'=>'this.form.submit()'),
		)
	);

	$ret .= $form->build();

	$ret.='<p><b>เลือกวันที่และชุดข้อมูล :</b></p><ul>';
	foreach ($folderList as $dateFolder => $mainFolder) {
		$ret.='<li><a href="javascript:void(0)">'.$dateFolder.'</a>';
		$ret.='<ul>';
		foreach ($mainFolder as $key => $subFolder) {
			if (substr($subFolder,-9)!='C_1hr_d03') continue;
			$isReadyCalculate = array_key_exists($dateFolder.':'.$subFolder.':'.$basinCode, $readyCalculate);
			$ret .= '<li><a class="forcast-get'.($isReadyCalculate ? '' : ' -notready').'" href="'.url('flood/forcast/hr3',array('d'=>$dateFolder,'f'=>$subFolder,'shp'=>$basinCode)).'">'.$key.'</a></li>';
		}
		$ret.='</ul>';
		$ret.='</li>';
	}
	$ret.='</ul>';

	if ($isAdmin) {
		$adminUi = new Ui(NULL,'ui-menu');
		$adminUi->add('<a href="'.url('flood/forcast/hr3/map').'" target="_blank">แผนที่ลุ่มน้ำ</a>');
		$adminUi->add('<a href="http://tiservice.hii.or.th/wrf-roms/ascii/" target="_blank">Check ESRI Rain</a>');
		$adminUi->add('<a href="'.url('forcast.src/downloadrain.php').'" target="_blank">Download Forcast Rain</a>');
		$adminUi->add('<a href="'.url('flood/forcast/createimg').'" target="_blank">Create Forcast Image</a>');
		$ret.='<nav class="nav"><h3>ADMIN MENU</h3>'.$adminUi->build().'</nav>';
	}

	//$ret.=print_o($folderList,'$folderList');
	$ret.='</div><!-- info -->';

	$ret.='<div class="result">'._NL;
	$ret.='<h3>ฝนคาดการณ์เฉลี่ยใน '.$basinName.' <span>'.$dateForcast.' : '.$folder.'</span></h3>';


	$ret.='<div class="map-canvas" id="map-canvas">';
	if ($dateForcast) {
		/*
		$ret.='<p class="forcast-day">';
		for ($i=1;$i<=$fileCount/$dayFileCount;$i++) {
			$ret.='<a class="button'.($i==$dayNo?' -active':'').'" href="'.url('flood/forcast/hr3',array('d'=>$dateForcast,'f'=>$folder,'shp'=>$basinCode,'type'=>$shapeType,'shppoint'=>$shpPoint,'day'=>$i)).'">Day '.$i.'</a> ';
		}
		$ret.='</p>';
		*/

		/*
		$startTimeForcast=strtotime($dateForcast.' +'.intval(($dayNo-1)*$dayFileCount+$hr+$utc).' hour');
		$dateShow=date('Y-m-d H:i',$startTimeForcast);
		*/

		$ret.='<div class="timebar">'._NL;
		$hrCount=0;
		$ret.='<ul>';
		for($day=1;$day<=3;$day++) {
			$ret.='<li class="day">';
			$ret.='<span class="title">Day '.$day./*date('Y-m-d',strtotime($dateForcast.' +'.intval($day-1).' day')).*/'</span>';
			for ($j=0;$j<24;$j++) {
				$hrCount++;
				$ret.='<span id="hr-'.$hrCount.'" class="hour" data-hour="'.$hrCount.'"></span>';
			}
			$ret.='</li>'._NL;
		}
		$ret.='</ul>';
		$ret.='</div>';
	}
	$ret.='<div class="datebar"><span id="bigdate" class="bigdate">00</span><span id="bigtime" class="bigtime">00:00</span></div>';
	$ret.='<img id="forcast-photo" src="http://www.nadrec.psu.ac.th/forcast/map_thai.jpg" />';
	$ret.='</div>'._NL;
	$ret.='<div class="forcast-rainsum">';
	$ret.=$dateForcast?'<p class="forcast-loading"><span class="loading -small"></span> กำลังโหลดข้อมูลปริมาณฝนเฉลี่ยใน <b>'.$basinName.'</b><br />กรุณารอสักครู่...</p>':'<p class="notify">กรุณาเลือกชุดข้อมูลสำหรับแสดง</p>';
	$ret.='</div>';
	$ret.='<div class="forcast-remark"></div>';
	$ret.='</div>'._NL;




	$ret.='<style type="text/css">
	body#flood #main {margin:0;}
	h2.title {display:none;}
	.toolbar {margin-bottom:20px;}
	.result {background:#fff; position:relative;}
		.result h3 {background: #50c300; color: #004203; padding: 4px; font-weight: normal;}
	.info {max-height: 200px; overflow: scroll; clear:both;}
		.info h3 {font-weight:normal; padding:4px 0; margin:0 0; background: #50c300; color: #004203; text-align: center;}
		.info h3>a {color:#004203;}
		.info ul {margin:0; font-weight: normal;}
		.info>ul {margin:0 0 16px 0; padding:0; list-style-type:none; font-weight: bold;}
	.map-canvas {width:100%;height:auto; position:relative;}
	.map-canvas>img {width:100%;}
	.colorbar {margin:2px 0 0 0; padding:0; list-style-type:none; position: absolute; z-index: 99999; right:2px; background:#fff;}
	.colorbar>li {font-size:10px; line-height:12px;}
	.colorbar>li>span {width:15px;height:12px;margin:0 4px 0 0;border:1px #999 solid;display:inline-block;border-bottom:none;}
		.colorbar>li:last-child>span {border-bottom:1px #999 solid;}
	.timebar {position: absolute; width:100%; bottom:0;}
		.timebar ul>li {width: 30%;}
		.timebar .day {margin:0 1px 2px 0; display: block; float: left; border:1px #ddd solid;}
			.timebar .title {margin:0; padding: 4px; display: block; text-align:center; background:#eee;}
			.timebar .hour {display: inline-block; width:4.1667%; height:10px; cursor: pointer; background: #CCCDCC;}
			.timebar .hour:hover {background:#ddd;}
			.timebar .active {background:#666; border-radius:4px;}
	.datebar {position:absolute; bottom:50px; right:2px; border-radius:8px;text-align:center;}
		.datebar .bigdate {font-size:32px; display:block;color:green;}
		.datebar .bigtime {font-size:24px; display:block; color:#f60;}
	#forcast-time {font-size:4em; display: block;}
	.forcast-day a {margin:0 4px 4px 0;}
	.forcast-rainsum {}
	.forcast-rainsum .item {margin:0;}
	.item td {text-align:center;}
	.button.-active {background:#0065bd; color:#fff; border-color: #357ebd;}
	.forcast-loading {text-align: center;}
	.loading.-small {margin:0 auto; padding:0; width:24px; height:24px; border: none; position: relative;		background-position:center center; display: block;}
	#forcast-notify {position: absolute; width: 100%; background:#fff; text-align: center;}

	.item.-rain3day>thead>tr>th {padding:8px;}
	.item.-rain3day>tbody>tr>td {padding:8px;}
	.item.-rain3day>tbody>tr>td:first-child {text-align: left; white-space: nowrap;}
	.item.-rain3day>tbody>tr.-active>td {font-weight: bold;background-color: #ffdec8;}

	.forcast-get.-notready {color: gray;}

	@media (min-width:30em){    /* 480/16 = 30 */
		.info {width:200px; float:left; overflow:auto; max-height: none;}
		.result {margin-left:208px;}
		.map-canvas {width:100%;}
		.forcast-rainsum {margin:0;}
	}

	@media (min-width:56.25em){    /* 900/16 = 56.25 */
		.map-canvas {width:403px;height:759px; float:left;}
		.forcast-rainsum {margin:0 0 0 440px;}
		.colorbar {left:387px; right: auto;}
		#forcast-notify {width: 405px;}
	}
	</style>';



	$ret.='<script type="text/javascript"><!--
	$(document).ready(function() {
		var forcastIdx = 1
		var fileCount = '.$fileCount.'
		var dateForcast = "'.$dateForcast.'"
		var esriFolder = "'.$folder.'"
		var basinCode = "'.$basinCode.'"
		var utcName = "'.$utcName.'"
		var utcTime = parseInt(utcName.substr(0,2))+7
		var timePhotoRefresh = 1*1*1000
		var photoUrl = ""
		var uploadUrl = "'.cfg('url.abs').cfg('upload_folder').'"

		/*
		console.log("utcTime="+utcTime)
		console.log("fileCount="+fileCount)
		console.log(dateForcast)
		var d1 = new Date(dateForcast+" 00:00:00");
		var d2 = new Date ( d1 );
		d2.setHours ( d1.getHours() + utcTime + 10);
		console.log(d1.getDate()+" "+d1.getHours())
		console.log(d2.getDate()+" "+d2.getHours())
		*/

		// Get UTP Data
		if (dateForcast != "") {

			//alert("Get data")
			console.log("Get data")
			$.get("'.url('flood/forcast/hr3/get').'",{d: dateForcast, f: esriFolder, shp: basinCode},function(html) {
				console.log("Data return")
				//console.log(html)
				$(".forcast-rainsum").html(html);
			});

			var startDate = new Date(dateForcast+"T00:00:00.0");

			//console.log("dateForcast = "+dateForcast+"T00:00:00.0")
			//console.log("startDate = "+startDate)
			function pollServerForImg() {
				photoUrl=uploadUrl+"forcast/forcast-"+dateForcast+"-"+utcName+"-hr"+(1e15+forcastIdx+"").slice(-3)+".jpg";
				$("#forcast-photo").attr("src",photoUrl);
				//console.log($("#forcast-photo").attr("src"))
				//console.log(photoUrl);
				//console.log(forcastIdx)


				$(".hour").removeClass("active");
				$("#hr-"+forcastIdx).addClass("active");

				var bigDate = new Date (startDate);
				//console.log(startDate.getHours() + utcTime + forcastIdx - 1)
				bigDate.setHours (startDate.getHours() + utcTime + forcastIdx - 1);
				$("#bigdate").text(bigDate.toString().split(" ")[0]+" "+bigDate.toString().split(" ")[2])
				$("#bigtime").text(bigDate.toString().split(" ")[4].substring(0,5))

				forcastIdx++;
				if (forcastIdx>fileCount) forcastIdx=1;
				setTimeout(pollServerForImg, timePhotoRefresh);
			};
			pollServerForImg();
		}

		$(".hour").click(function() {
			forcastIdx=$(this).data("hour");
			$(this).addClass("active");
		});

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