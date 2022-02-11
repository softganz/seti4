<?php
function school_summary_weight($self,$orgid) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('school/summary/weight/add/'.$orgid).'"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}


	$ret.='<h2>สถานการณ์ภาวะโภชนาการนักเรียน</h2>';

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);










	$tpid=mydb::select('SELECT `tpid` FROM %topic% t WHERE `type`="project" AND `orgid`=:orgid LIMIT 1',':orgid',$orgid)->tpid;

	$qtvalue->getweight=$qtarray['thin']+$qtarray['ratherthin']+$qtarray['willowy']+$qtarray['plump']+$qtarray['gettingfat']+$qtarray['fat'];

	$weightSchool=R::model('project.weight.get',$tpid);

	$no=0;
	$tables = new Table();
	$tables->caption='บันทึกสรุปสถานการณ์ภาวะโภชนาการนักเรียน';
	$tables->thead=array('ปีการศึกษา','นักเรียน','ชั่ง');

	foreach ($weightSchool as $rs) {
		$tables->rows[]=array(
											($rs->year+543).' '.$rs->term.'/'.$rs->period,
											number_format($rs->total),
											number_format($rs->getweight),
											);
	}
	$self->theme->sidebar.=$tables->build();


	$tablesFat=new table('item -center -weightform');
	$tablesFat->caption='สถานการณ์ภาวะโภชนาการนักเรียน - น้ำหนักตามเกณฑ์ส่วนสูง';
	$tablesFat->thead='<tr><th rowspan="2">ปีการศึกษา</th><th rowspan="2">ภาคการศึกษา</th><th rowspan="2">วันที่ชั่ง/วัด</th><th>จำนวนนักเรียนทั้งหมด</th><th colspan="2">จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง</th><th colspan="2">ผอม</th><th colspan="2">ค่อนข้างผอม</th><th colspan="2">สมส่วน</th><th colspan="2">ท้วม</th><th colspan="2">เริ่มอ้วน</th><th colspan="2">อ้วน</th><th colspan="2">เริ่มอ้วน+อ้วน</th><th rowspan="2" class="col-icons -c2"></th></tr><tr><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th></tr>';

	$chartThin=new Table('item -center');
	$chartThin->thead=array('ปี พ.ศ.','amt -thin'=>'ผอม','','อ้วน','','amt -fat'=>'อ้วน+เริ่มอ้วน(%)','','เป้าหมาย(%)','');

	$chartYear=new Table('item -center');
	//$chartYear->thead=array('ภาวะ','amt -thin'=>'ผอม','amt -fat'=>'อ้วน+เริ่มอ้วน(%)','เริ่มอ้วน+อ้วน','เป้าหมาย(%)');

	$no=0;
	foreach ($weightSchool as $rs) {
		$xAxis=($rs->year+543).' '.$rs->term.'/'.$rs->period;
		$percentThin=$rs->thin*100/$rs->getweight;
		$percentFat=$rs->fat*100/$rs->getweight;
		$percentGettingFat=($rs->gettingfat+$rs->fat)*100/$rs->getweight;
		$tablesFat->rows[]=array(
											$rs->year+543,
											$rs->term.'/'.$rs->period,
											sg_date($rs->dateinput,'ว ดด ปป'),
											number_format($rs->total),
											number_format($rs->getweight),
											round($rs->getweight*100/$rs->total,$percentDigit).'%',
											number_format($rs->thin),
											round($percentThin,$percentDigit).'%',
											number_format($rs->ratherthin),
											round($rs->ratherthin*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->willowy),
											round($rs->willowy*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->plump),
											round($rs->plump*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->gettingfat),
											round($rs->gettingfat*100/$rs->getweight,$percentDigit).'%',
											number_format($rs->fat),
											round($percentFat,$percentDigit).'%',
											number_format($rs->gettingfat+$rs->fat),
											round($percentGettingFat,$percentDigit).'%',
											'<span style="white-space:nowrap">'
											.'<a class="sg-action -no-print" href="'.url('paper/'.$tpid.'/situation/weight/view/'.$rs->trid).'" data-rel="box" title="รายละเอียด"><icon class="icon -viewdoc"></i></a>'
											.($isEdit?'<a class="noprint" href="'.url('paper/'.$tpid.'/situation/weight/modify/'.$rs->trid).'" title="แก้ไข"><icon class="icon -edit"></i></a>':'')
											.'</span>',
											);
		$chartThin->rows[]=array(
											'string:Year'=>($rs->year+543).':'.$rs->term.'/'.$rs->period,
											'number:ผอม'=>round($percentThin,2),
											'string:ผอม:role'=>number_format($percentThin,2).'%',
											'number:อ้วน'=>round($percentFat,2),
											'string:อ้วน:role'=>number_format($percentFat,2).'%',
											'number:อ้วน+เริ่มอ้วน'=>round($percentGettingFat,2),
											'string:อ้วน+เริ่มอ้วน:role'=>number_format($percentGettingFat,2).'%',
											'number:เป้าหมาย 7%'=>7,
											);
		//$chartYear->rows['ผอม']['string:Year']=$xAxis;
		$chartYear->thead['title']='ภาวะ';
		$chartYear->thead[$xAxis]=$xAxis;
		$chartYear->thead[$xAxis.':role']='';
		$chartYear->rows['เตี้ย']['string:0']='เตี้ย';
		$chartYear->rows['ผอม']['string:0']='ผอม';
		$chartYear->rows['อ้วน']['string:0']='อ้วน';
		$chartYear->rows['เริ่มอ้วน+อ้วน']['string:0']='เริ่มอ้วน+อ้วน';
		$chartYear->rows['เตี้ย']['number:'.$xAxis]=0;
		$chartYear->rows['เตี้ย']['string:'.$xAxis.':role']='0%';
		$chartYear->rows['ผอม']['number:'.$xAxis]=round($percentThin,2);
		$chartYear->rows['ผอม']['string:'.$xAxis.':role']=number_format($percentThin,2).'%';
		//$chartYear->rows['ผอม']['number:'.$xAxis.'role']='{annotation:"Text"}';
		$chartYear->rows['อ้วน']['number:'.$xAxis]=round($percentFat,2);
		$chartYear->rows['อ้วน']['string:'.$xAxis.':role']=number_format($percentFat,2).'%';
		$chartYear->rows['เริ่มอ้วน+อ้วน']['number:'.$xAxis]=round($percentGettingFat,2);
		$chartYear->rows['เริ่มอ้วน+อ้วน']['string:'.$xAxis.':role']=number_format($percentGettingFat,2).'%';
	}




	$heightSchool=R::model('project.height.get',$tpid);


	$tablesShort=new table('item -center -weightform');
	$tablesShort->caption='สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูงตามเกณฑ์อายุ';
	$tablesShort->thead='<tr><th rowspan="2">ปีการศึกษา</th><th rowspan="2">ภาคการศึกษา</th><th rowspan="2">วันที่ชั่ง/วัด</th><th>จำนวนนักเรียนทั้งหมด</th><th colspan="2">จำนวนนักเรียนที่วัดส่วนสูง</th><th colspan="2">เตี้ย</th><th colspan="2">ค่อนข้างเตี้ย</th><th colspan="2">เตี้ย+ค่อนข้างเตี้ย</th><th colspan="2">สูงตามเกณฑ์</th><th colspan="2">ค่อนข้างสูง</th><th colspan="2">สูง</th><th rowspan="2"></th></tr><tr><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th></tr>';

	$chartShort=new Table('item -center');
	$chartShort->thead=array('ปี พ.ศ.','amt -short'=>'เตี้ย(%)','','amt -rathershort'=>'ค่อนข้างเตี้ย+เตี้ย(%)','','เป้าหมาย(%)');

	$no=0;
	foreach ($heightSchool as $rs) {
		$xAxis=($rs->year+543).' '.$rs->term.'/'.$rs->period;
		$percentShort=$rs->short*100/$rs->getheight;
		$percentRatherShort=($rs->short+$rs->rathershort)*100/$rs->getheight;

		$tablesShort->rows[]=array(
			$rs->year+543,
			$rs->term.'/'.$rs->period,
			sg_date($rs->dateinput,'ว ดด ปป'),
			number_format($rs->total),
			number_format($rs->getheight),
			round($rs->getheight*100/$rs->total,$percentDigit).'%',
			number_format($rs->short),
			round($percentShort,$percentDigit).'%',
			number_format($rs->rathershort),
			round($rs->rathershort*100/$rs->getheight,$percentDigit).'%',
			number_format($rs->short+$rs->rathershort),
			round($percentRatherShort,$percentDigit).'%',
			number_format($rs->standard),
			round($rs->standard*100/$rs->getheight,$percentDigit).'%',
			number_format($rs->ratherheight),
			round($rs->ratherheight*100/$rs->getheight,$percentDigit).'%',
			number_format($rs->veryheight),
			round($rs->veryheight*100/$rs->getheight,$percentDigit).'%',
			'<span style="white-space:nowrap">'
			.'<a class="sg-action -no-print" href="'.url('paper/'.$tpid.'/situation/weight/view/'.$rs->trid).'" data-rel="box" title="รายละเอียด"><icon class="icon -viewdoc"></i></a>'
			.($isEdit?'<a class="noprint" href="'.url('paper/'.$tpid.'/situation/weight/modify/'.$rs->trid).'" title="แก้ไข"><i class="icon -edit"></i></a>':'')
			.'</span>',
		);

		$chartYear->rows['เตี้ย']['number:'.$xAxis]=round($percentShort,2);
		$chartYear->rows['เตี้ย']['string:'.$xAxis.':role']=number_format($percentShort,2).'%';

		$chartShort->rows[]=array(
			'string:Year'=>($rs->year+543).':'.$rs->term.'/'.$rs->period,
			'number:เตี้ย'=>number_format($percentShort,2),
			'string:เตี้ย:role'=>number_format($percentShort,2).'%',
			'number:ค่อนข้างเตี้ย+เตี้ย'=>number_format($percentRatherShort,2),
			'string:ค่อนข้างเตี้ย+เตี้ย:role'=>number_format($percentRatherShort,2).'%',
			'number:เป้าหมาย 7%'=>7,
		);
	}



	if (empty($action)) {
		$ret.='<div id="year-all" class="sg-chart -all" data-chart-type="col" data-image="year-all-image"><h3>สถานการณ์ภาวะโภชนาการนักเรียน (ปีการศึกษา)</h3>'.$chartYear->build().'</div>';

		$ret.='<div id="year-fat" class="sg-chart -fat" data-chart-type="line" data-image="year-fat-image"><h3>สถานการณ์ภาวะโภชนาการนักเรียน-ผอม,อ้วน+เริ่มอ้วน</h3>'.$chartThin->build().'</div>';

		//$ret.=$chartThin->build();
		//$ret.=print_o($chartThin,'$chartThin');

		//ภาวะค่อนข้างเตี้ยและเตี้ยลด
		$ret.='<div id="year-short" class="sg-chart -short" data-chart-type="line" data-image="year-short-image"><h3>สถานการณ์ภาวะโภชนาการนักเรียน-ค่อนข้างเตี้ย+เตี้ย</h3>'.$chartShort->build().'</div>';
		//$ret.=$chartShort->build();

		$ret.='<div style="text-align:center;"><img id="year-all-image" class="chart-img" src="/library/img/none.gif" width="100" height="100" /> <img id="year-fat-image" class="chart-img" src="/library/img/none.gif" width="100" height="100" /> <img id="year-short-image" class="chart-img" src="/library/img/none.gif" width="100" height="100" /></div>';

		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	}

	$ret.='<style type="text/css">
	.sg-chart {height:400px;}
	.chart-img {border:1px green solid; z-index:1;}
	</style>';

	$ret.=$tablesFat->build();
	$ret.='<hr class="pagebreak" />';





	$ret.=$tablesShort->build();

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o($topic,'$topic').print_o($para,'$para');

	$ret.='<style type="text/css">
	.item.-weightform {margin-bottom:80px;}
	.item.-weightform caption {background:#FFAE00; color:#000; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef;}
	.item.-weightform td {width:50px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.graph {width:150px;height:150px; margin:0 auto;}
	.toolbar.-graphtype {text-align: right; margin:0 0 10px 0;}
	.toolbar .active {background:#84CC00;}
	.item tr.subfooter.-sub2 td {background-color:#d0d0d0;}
	.item tr.subfooter.-sub3 td {background-color:#c0c0c0;}
	</style>';


	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>