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

function r_flood_forcast_hr3_calculate($dateForcast, $folder, $basinCode, $shapeType, $shpPoint = 20, $options = '{}') {
	set_time_limit(0);
	$timer = -microtime(true);
	$result = array();

	if (empty($dateForcast) || empty($folder) || empty($basinCode)) {
		return NULL;
	}



	//Check if already calculate

	$stmt = 'SELECT * FROM %flood_f3day% WHERE `dateforcast` = :dateforcast AND `folder` = :folder AND `basincode` = :basincode LIMIT 1';
	$rs = mydb::select($stmt, ':dateforcast', $dateForcast, ':folder', $folder, ':basincode', $basinCode);

	// If not calculate, create new record in flood_f3day table to prevent multiple calculate
	if ($rs->_empty) {
		$stmt = 'INSERT INTO %flood_f3day%
			( `dateforcast`, `folder`, `basincode`, `created` )
			VALUES
			( :dateforcast, :folder, :basincode, :created )';

		mydb::query($stmt, ':dateforcast', $dateForcast, ':folder', $folder, ':basincode', $basinCode, ':created', date('U'));
		$forcastId = mydb()->insert_id;

		$result['remark'] = 'Start New Calculate id = '.$forcastId;
	} else {
		$result['remark'] = 'Already calculate @'.sg_date($rs->created,'Y-m-d H:i:s');
		return $result;
	}



	$polygon = array();


	$basinList = cfg('basin');
	$basinName = $basinList[$basinCode];
	$forcastFolder = cfg('flood.forcast.folder');


	$utc = intval(substr($folder,0,2))+7;
	$dayNo = 1;
	$dayRain = 0;
	$fileCount = 0;
	$dayFileCount = 72;
	list($utcName,$rainhrName,$dnoName) = explode('_',$folder);
	$hourName = 'hour';
	if ($rainhrName == '24hr') {
		$hourName = 'day';
		$dayFileCount = 1;
	}

	$dataFolder = $forcastFolder.'/'.$dateForcast.'/'.$folder;
	$fileCount = __flood_forcast_avg_filecount($dataFolder);

	$result['folder']['name'] = $dataFolder;
	$result['folder']['count'] = $fileCount;




	// Load data , calculate and return

	// Get UTP shape
	$shapeInfo->coordinates[0]->path = array();
	$shapeFile = $forcastFolder.'/shape.'.$basinCode.'.json';
	$shapeLines = file($shapeFile);
	$shapeInfo = json_decode($shapeLines[0]);

	unset($shapeInfo->coordinates[1]);
	//print_o($shapeInfo,'$shapeInfo',1);
	//$result['shape']['file'] = $shapeFile;
	//$result['shape']['line'] = $shapeLines;
	//$result['shape']['info'] = $shapeInfo;
	//return $result;

	//$result['table'] .= 'BASIN '.$basinCode;


	// Generate boundery coordinates by box or step for rain area calculate
	$xmin = $shapeInfo->coordinates[0]->bounding_box->xmin;
	$ymin = $shapeInfo->coordinates[0]->bounding_box->ymin;
	$xmax = $shapeInfo->coordinates[0]->bounding_box->xmax;
	$ymax = $shapeInfo->coordinates[0]->bounding_box->ymax;

	$result['shape']['box-co'] = '('.$xmin.','.$ymin.'), ('.$xmax.','.$ymin.'), ('.$xmax.','.$ymax.'), ('.$xmin.','.$ymax.'), ('.$xmin.','.$ymin.')';


	$shapeBox = (object) array('xmin'=>$xmin,'ymin'=>$ymin,'xmax'=>$xmax,'ymax'=>$ymax);

	$result['shape']['box'] = $shapeBox;

	if ($shapeType == 'box') {
		$polygon = array(
			array('x'=>$xmin,'y'=>$ymin),
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
	$tables->thead=array('date -date'=>'วันที่','เวลา (น.)','เวลา (ชั่วโมง)','ฝนเฉลี่ย (mm.)','ฝนสะสม (mm.)');

	$inPolygon = array();

	// Read each forcast file and calculete average rain
	for($hr = 1; $hr <= $dayFileCount; $hr++) {
		$file = $dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%03d',($dayNo-1)*24+$hr).'.asc';
		if ($rainhrName == '24hr') {
			$file = $dataFolder.'/esri_rain'.$rainhrName.'_'.$dnoName.'_'.$hourName.sprintf('%01d',($dayNo)*$hr).'.asc';
		}

		$result['folder']['file'][] = $file;


		// Start read rain forcast data
		$data = R::Model('flood.forcast.hr3.read', $file, $polygon, $shapeBox, $inPolygon);
		$inPolygon = $data['inpolygon'];


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

		//debugMsg($data,'$data');

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
		$result['forcast'][] = $sqlData;

		$stmt = 'INSERT INTO %flood_forcast%
						(`dateforcast`, `folder`, `basincode`, `basinname`, `dateshow`, `timeshow`, `hourshow`, `rainavg`, `rainsum`, `created`)
						VALUES
						(:dateforcast, :folder, :basincode, :basinname, :dateshow, :timeshow, :hourshow, :rainavg, :rainsum, :created)';
		mydb::query($stmt, $sqlData);
		$result['query'][] = mydb()->_query;

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
		//unset($data);
	}

	unset($sqlData);
	$sqlData->forid = $forcastId;
	$sqlData->dateforcast = $dateForcast;
	$sqlData->folder = $folder;
	$sqlData->basincode = $basinCode;
	$sqlData->basinname = $basinName;
	$sqlData->rainin24hr = number_format($rainIn24hr,$digit, '.', '');
	$sqlData->rainin48hr = number_format($rainIn48hr,$digit, '.', '');
	$sqlData->rainin72hr = number_format($rainIn72hr,$digit, '.', '');
	$sqlData->created = date('U');


	$result['3day'][] = $sqlData;

	$stmt = 'UPDATE %flood_f3day% SET
		`basinname` = :basinname
		, `rainin24hr` = :rainin24hr
		, `rainin48hr` = :rainin48hr
		, `rainin72hr` = :rainin72hr
		WHERE `forid` = :forid LIMIT 1';

	mydb::query($stmt, $sqlData);

	$result['query'][] = mydb()->_query;

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
	//$result['remark'] .= print_o($inPolygon,'$inPolygon');
	if ($basinCode) {
		$result['remark'] .= '<p>Polygon '.count($polygon).' point = ';
		foreach ($polygon as $coor)
			$result['remark'] .= '('.$coor['x'].','.$coor['y'].') ';
	}
	//$result['remark'] .= $inBlockStr;
	return $result;
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