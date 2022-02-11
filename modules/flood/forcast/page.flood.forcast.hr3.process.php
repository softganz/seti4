<?php
/**
* Rain Forcast Average
*
* @param Object $self
* @return String
*
* Download rain forcast file from http://tiservice.hii.or.th/wrf-roms/ascii/
*/

function flood_forcast_hr3_process($self) {
	$self->theme->title = 'Rain Forcast Average';
	set_time_limit(0);
	$timer = -microtime(true);

	$dateForcast = post('d');
	$folder = post('f');
	$basincode = SG\getFirst(post('shp'),'UPT');
	$shapeType = post('type');
	$shpPoint = SG\getFirst(post('shppoint'),1000);
	$dayNo = SG\getFirst(post('day'),1);
	$getDelete = post('delete');

	$basinList = cfg('basin');

	$basinName = $basinList[$basincode];

	$isRightToCalculate = true;// user_access('access administrator pages,administrator floods');

	if ($getDelete) {
		mydb::query('DELETE FROM %flood_f3day% WHERE `dateforcast` = :dateForcast AND `basincode` = :basincode', ':dateForcast',$dateForcast, ':basincode',$basincode);
		//$ret .= mydb()->_query.'<br />';
		mydb::query('DELETE FROM %flood_forcast% WHERE `dateforcast` = :dateForcast AND `basincode` = :basincode', ':dateForcast',$dateForcast, ':basincode',$basincode);
		//$ret .= mydb()->_query.'<br />';
		if ($getDelete == 'only') return $ret;
	}

	$forcastFolder = cfg('flood.forcast.folder');

	$stmt = 'SELECT * FROM %flood_f3day% WHERE `dateforcast` = :dateforcast AND `folder` = :folder AND `basincode` = :basincode LIMIT 1';
	$rs = mydb::select($stmt, ':dateforcast', $dateForcast, ':folder', $folder, ':basincode', $basincode);
	//$ret .= print_o($rs);

	if ($rs->_empty) {
		// Start calculate rain average on empty
		$result = R::Model('flood.forcast.hr3.calculate',$dateForcast, $folder, $basincode, $shapeType, $shpPoint);
		$ret .= $result['table'];
		$ret .= $result['remark'];
		//$ret .= print_o($result, '$result');
	} else {
		$ret .= message('notify', 'ข้อมูลชุดนี้ได้สร้างไว้เรียบร้อยแล้ว');
		$sumTable = new Table();
		$sumTable->addClass('-rain3day');
		$sumTable->thead = array('เวลา (ชั่วโมง)','24','48','72');

		$sumTable->rows[] = array(
			'ฝนคาดการณ์เฉลี่ยใน'.$rs->basinname.' (mm.)',
			$rs->_num_rows ? number_format($rs->rainin24hr,2) : '-',
			$rs->_num_rows ? number_format($rs->rainin48hr,2) : '-',
			$rs->_num_rows ? number_format($rs->rainin72hr,2) : '-',
		);

		$ret .= $sumTable->build();
	}

	return $ret;
}
?>