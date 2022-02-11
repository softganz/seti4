<?php
/**
* Rain Forcast Average
*
* @param Object $self
* @return String
*
* Download rain forcast file from http://tiservice.hii.or.th/wrf-roms/ascii/
*/

function flood_forcast_hr3_get($self) {
	$self->theme->title = 'Rain Forcast Average';

	$dateForcast = post('d');
	$esriFolder = post('f');
	$basinCode = post('shp');
	$digit = 2;

	$isAdmin = user_access('access administrator pages,administrator floods');

	$ret = '';

	$basinList = cfg('basin');


	$basinName = $basinList[$basinCode];

	$isRightToCalculate = user_access('access administrator pages,administrator floods');

	//$ret .= '<h3>ฝนคาดการณ์เฉลี่ยใน '.$basinName.'</h3>';


	$stmt = 'SELECT * FROM %flood_f3day% WHERE `dateforcast` = :dateforcast AND `folder` = :folder; -- {key: "basincode"}';
	$rain3Days = mydb::select($stmt, ':basincode', $basinCode, ':dateforcast', $dateForcast, ':folder', $esriFolder);
	//$ret .= print_o($rain3Days,'$rain3Days',1);

	$isNotCalculate = !array_key_exists($basinCode, $rain3Days->items);

	if ($isNotCalculate) {
		$ret .= '<div>';
		$ret .= message('notify','ไม่มีข้อมูลฝนคาดการณ์เฉลี่ย');
		if ($isRightToCalculate) {
			$ret .= '<a class="sg-action btn -primary" href="'.url('flood/forcast/hr3/process',array('d'=>$dateForcast,'f'=>$esriFolder,'shp'=>$basinCode,'type'=>$shapeType,'shppoint'=>$shpPoint)).'" data-rel="parent" style="width: 13em; display: block; margin:32px auto;"><i class="icon -add -white"></i><span>คำนวณฝนคาดการณ์เฉลี่ย</span></a>';
		}
		$ret .= '</div>';
		return $ret;
	}




	$tables3Days = new Table();
	$tables3Days->addClass('-rain3day');
	$tables3Days->colgroup = array('','','','hr72 -hover-parent' => '');
	$tables3Days->thead = '<tr><th rowspan="2">ลุ่มน้ำ</th><th colspan="3">ฝนคาดการณ์เฉลี่ย (mm.) ใน เวลา (ชั่วโมง)</th></tr><tr><th>24</th><th>48</th><th>72</th></tr>';
	foreach ($rain3Days->items as $rs) {
		$config = array();
		if ($rs->basincode == $basinCode) $config['class'] = '-active';
		//$ret .= $rs->basincode.' : '.$basinCode.print_o($config, '$config');

		$ui = new Ui();
		$ui->addConfig('nav', '{class: "nav -icons -hover"}');
		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('flood/forcast/hr3/process', array('d' => $dateForcast, 'f' => $esriFolder, 'shp' => $rs->basincode, 'delete' => 'only')).'" data-rel="notify" data-done="load" data-title="ลบการคำนวณ" data-confirm="ต้องการลบการคำนวน กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$tables3Days->rows[] = array(
			$rs->basinname,
			is_null($rs->rainin24hr) ? '-' : number_format($rs->rainin24hr,2),
			is_null($rs->rainin48hr) ? '-' : number_format($rs->rainin48hr,2),
			is_null($rs->rainin72hr ) ? '-' : number_format($rs->rainin72hr,2)
			. $ui->build(),
			'config' => $config,
		);
	}
	$ret .= $tables3Days->build();

	$stmt = 'SELECT * FROM %flood_forcast% WHERE `basincode` = :basincode AND `dateforcast` = :dateforcast AND `folder` = :folder ORDER BY `forid` ASC';
	$dbs = mydb::select($stmt, ':basincode', $basinCode, ':dateforcast', $dateForcast, ':folder', $esriFolder);
	//$ret .= print_o($dbs,'$dbs',1);

	$tables3Hr = new Table();
	$tables3Hr->addClass('-rainall');
	//$tables3Hr->thead=array('Date','Hour','Rain Summary','Area (block)','Rain Average','ฝนสะสม');
	$tables3Hr->thead=array('date -date'=>'วันที่','เวลา (น.)','เวลา (ชั่วโมง)','ฝนเฉลี่ย (mm.)','ฝนสะสม (mm.)');


	// Read each forcast file and calculete average rain
	foreach ($dbs->items as $rs) {
		$tables3Hr->rows[] = array(
											$rs->dateshow,
											substr($rs->timeshow,0,5),
											$rs->hourshow,
											number_format($rs->rainavg,$digit),
											number_format($rs->rainsum,$digit),
											);
	}

	$ret .= $tables3Hr->build();

	return $ret;
}
?>