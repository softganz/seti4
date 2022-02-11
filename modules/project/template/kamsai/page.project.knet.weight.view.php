<?php
/**
 * แบบฟอร์มรายงานภาวะโภชนาการนักเรียน
 *
 * @param Object $topic
 * @param Object $para
 */
define(_KAMSAIINDICATOR,'weight');
define(_INDICATORHEIGHT,'height');

function project_knet_weight_view($self, $orgId, $tranId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER'))) && post('mode') != 'view';

	$percentDigit = 2;
	$classLevelList = explode(',',$orgInfo->info->classlevel);


	$weightInfo = R::Model('project.knet.weight.get', $tranId);


	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย ปีการศึกษา : <strong>'.($weightInfo->year+543).'</strong> เทอม <strong>'.$weightInfo->term.'/'.$weightInfo->period.'</strong></h3></header>';
	$ret .= '<p>ผู้ประเมิน : <strong>'.$weightInfo->postby.'</strong> วันที่ชั่ง/วัด : <strong>'.sg_date($weightInfo->dateinput,'ว ดด ปป').'</strong></p>';

	$weightTotal=$weightGetweight=$weightThin=$weightRatherthin=$weightWillowy=$weightPlump=$weightGettingfat=$weightFat=0;

	$tables = new Table([
		'class' => '-weightform',
		'showHeader' => false,
		'caption' => 'น้ำหนักตามเกณฑ์ส่วนสูง',
		'colgroup' => [
			'',
			'student -amt'=>'',
			'weighted -amt'=>'',
			'bad -amt'=>'',
			'badpercent -amt'=>'',
			'fair -amt'=>'',
			'fairpercent -amt'=>'',
			'good -amt'=>'',
			'goodpercent -amt'=>'',
		],
		'thead' => '<tr>'
			. '<th rowspan="3">ชั้น</th><th colspan="16">จำนวนนักเรียน</th>'
			. '</tr>'
			. '<tr>'
			. '<th>ทั้งหมด</th><th>ชั่งน้ำหนัก</th><th colspan="2">ผอม</th><th colspan="2">ค่อนข้างผอม</th><th colspan="2">สมส่วน</th><th colspan="2">ท้วม</th><th colspan="2">เริ่มอ้วน</th><th colspan="2">อ้วน</th><th colspan="2">เริ่มอ้วน+อ้วน</th>'
			. '</tr>'
			. '<tr>'
			. '<th>คน</th><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th>'
			. '</tr>',
		]);

	foreach ($weightInfo->weight as $rs) {
		if (substr($rs->qtno,0,1) == '1' && !in_array('อนุบาล', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '2' && !in_array('ประถม', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '3' && !in_array('มัธยม', $classLevelList)) continue;


		$totalError=$rs->total<$rs->getweight;
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="17"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
			$subWeightTotal=$subWeightGetweight=$subWeightThin=$subWeightRatherthin=$subWeightWillowy=$subWeightPlump=$subWeightGettingfat=$subWeightFat=0;
		}
		$tables->rows[] = [
			$rs->question,
			number_format($rs->total).($totalError?'!':''),
			number_format($rs->getweight).($totalError?'!':''),
			number_format($rs->thin),
			$rs->total > 0 ? number_format($rs->thin*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->ratherthin),
			$rs->total > 0 ? number_format($rs->ratherthin*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->willowy),
			$rs->total > 0 ? number_format($rs->willowy*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->plump),
			$rs->total > 0 ? number_format($rs->plump*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->gettingfat),
			$rs->total > 0 ? number_format($rs->gettingfat*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->fat),
			$rs->total > 0 ? number_format($rs->fat*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->gettingfat+$rs->fat),
			$rs->total > 0 ? number_format(($rs->gettingfat+$rs->fat)*100/$rs->total,$percentDigit).'%' : '-',
			'config' => ['class'=>$totalError?'error -weight':''],
		];

		$subWeightTotal+=$rs->total;
		$subWeightGetweight+=$rs->getweight;
		$subWeightThin+=$rs->thin;
		$subWeightRatherthin+=$rs->ratherthin;
		$subWeightWillowy+=$rs->willowy;
		$subWeightPlump+=$rs->plump;
		$subWeightGettingfat+=$rs->gettingfat;
		$subWeightFat+=$rs->fat;

		if (in_array($rs->qtno,array(13,26,33))) {
			$tables->rows[] = [
				'รวมช่วงชั้น',
				$subWeightTotal,
				$subWeightGetweight,
				$subWeightThin,
				number_format($subWeightThin*100/$subWeightTotal,$percentDigit).'%',
				$subWeightRatherthin,
				number_format($subWeightRatherthin*100/$subWeightTotal,$percentDigit).'%',
				$subWeightWillowy,
				number_format($subWeightWillowy*100/$subWeightTotal,$percentDigit).'%',
				$subWeightPlump,
				number_format($subWeightPlump*100/$subWeightTotal,$percentDigit).'%',
				$subWeightGettingfat,
				number_format($subWeightGettingfat*100/$subWeightTotal,$percentDigit).'%',
				$subWeightFat,
				number_format($subWeightFat*100/$subWeightTotal,$percentDigit).'%',
				$subWeightGettingfat+$subWeightFat,
				number_format(($subWeightGettingfat+$subWeightFat)*100/$subWeightTotal,$percentDigit).'%',
				'config' => ['class'=>'subfooter']
			];
		}

		$weightTotal+=$rs->total;
		$weightGetweight+=$rs->getweight;
		$weightThin+=$rs->thin;
		$weightRatherthin+=$rs->ratherthin;
		$weightWillowy+=$rs->willowy;
		$weightPlump+=$rs->plump;
		$weightGettingfat+=$rs->gettingfat;
		$weightFat+=$rs->fat;
	}
	//$tables->rows[]=array('รวมชั้นอนุบาล','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นประถมศึกษาปีที่ 1-6','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นมัธยมศึกษาปีที่ 1-3','','','','','','','','','','','','','','');
	$tables->tfoot[] = [
		'ภาพรวมโรงเรียน',
		$weightTotal,
		$weightGetweight,
		$weightThin,
		number_format($weightThin*100/$weightTotal,$percentDigit).'%',
		$weightRatherthin,
		number_format($weightRatherthin*100/$weightTotal,$percentDigit).'%',
		$weightWillowy,
		number_format($weightWillowy*100/$weightTotal,$percentDigit).'%',
		$weightPlump,
		number_format($weightPlump*100/$weightTotal,$percentDigit).'%',
		$weightGettingfat,
		number_format($weightGettingfat*100/$weightTotal,$percentDigit).'%',
		$weightFat,
		number_format($weightFat*100/$weightTotal,$percentDigit).'%',
		$weightGettingfat+$weightFat,
		number_format(($weightGettingfat+$weightFat)*100/$weightTotal,$percentDigit).'%',
	];
	$ret.=$tables->build();








	$heightTotal=$heightGetheight=$heightShort=$heightRathershort=$heightStandard=$heightRatherheight=$heightVeryheight=0;

	$tables = new Table([
		'class' => '-weightform',
		'showHeader' => false,
		'caption' => 'ส่วนสูงตามเกณฑ์อายุ',
		'colgroup' => [
			'',
			'student -amt'=>'',
			'heighted -amt',
			'bad -amt'=>'',
			'badpercent -amt'=>'',
			'fair -amt'=>'',
			'fairpercent -amt'=>'',
			'good -amt'=>'',
			'goodpercent -amt'=>'',
		],
		'thead' => '<tr>'
			. '<th rowspan="3">ชั้น</th><th colspan="12">จำนวนนักเรียน</th><th colspan="4" rowspan="3"></th>'
			. '</tr>'
			. '<tr>'
			. '<th>ทั้งหมด</th><th>วัดส่วนสูง</th><th colspan="2">เตี้ย</th><th colspan="2">ค่อนข้างเตี้ย</th><th colspan="2">สูงตามเกณฑ์</th><th colspan="2">ค่อนข้างสูง</th><th colspan="2">สูง</th>'
			. '</tr>'
			. '<tr>'
			. '<th>คน</th><th>คน</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th>'
			. '</tr>',
	]);
	foreach ($weightInfo->height as $rs) {
		if (substr($rs->qtno,0,1) == '1' && !in_array('อนุบาล', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '2' && !in_array('ประถม', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '3' && !in_array('มัธยม', $classLevelList)) continue;

		$totalError=$rs->total<$rs->getheight;
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="17"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
			$subHeightTotal=$subHeightGetheight=$subHeightShort=$subHeightRathershort=$subHeightStandard=$subHeightRatherheight=$subHeightVeryheight=0;
		}
		$tables->rows[] = [
			$rs->question,
			number_format($rs->total).($totalError?'!':''),
			number_format($rs->getheight).($totalError?'!':''),
			number_format($rs->short),
			$rs->total > 0 ? number_format($rs->short*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->rathershort),
			$rs->total > 0 ? number_format($rs->rathershort*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->standard),
			$rs->total > 0 ? number_format($rs->standard*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->ratherheight),
			$rs->total > 0 ? number_format($rs->ratherheight*100/$rs->total,$percentDigit).'%' : '-',
			number_format($rs->veryheight),
			$rs->total > 0 ? number_format($rs->veryheight*100/$rs->total,$percentDigit).'%' : '-',
			'<td colspan="4"></td>',
			'config' => ['class'=>$totalError?'error -weight':''],
		];

		$subHeightTotal+=$rs->total;
		$subHeightGetheight+=$rs->getheight;
		$subHeightShort+=$rs->short;
		$subHeightRathershort+=$rs->rathershort;
		$subHeightStandard+=$rs->standard;
		$subHeightRatherheight+=$rs->ratherheight;
		$subHeightVeryheight+=$rs->veryheight;

		if (in_array($rs->qtno,array(13,26,33))) {
			$tables->rows[] = [
				'รวมช่วงชั้น',
				$subHeightTotal,
				$subHeightGetheight,
				$subHeightShort,
				number_format($subHeightShort*100/$subHeightTotal,$percentDigit).'%',
				$subHeightRathershort,
				number_format($subHeightRathershort*100/$subHeightTotal,$percentDigit).'%',
				$subHeightStandard,
				number_format($subHeightStandard*100/$subHeightTotal,$percentDigit).'%',
				$subHeightRatherheight,
				number_format($subHeightRatherheight*100/$subHeightTotal,$percentDigit).'%',
				$subHeightVeryheight,
				number_format($subHeightVeryheight*100/$subHeightTotal,$percentDigit).'%',
				'',
				'',
				'',
				'',
				'config' => ['class'=>'subfooter']
			];
		}

		$heightTotal+=$rs->total;
		$heightGetheight+=$rs->getheight;
		$heightShort+=$rs->short;
		$heightRathershort+=$rs->rathershort;
		$heightStandard+=$rs->standard;
		$heightRatherheight+=$rs->ratherheight;
		$heightVeryheight+=$rs->veryheight;
	}

	$tables->tfoot[] = [
		'ภาพรวมโรงเรียน',
		$heightTotal,
		$heightGetheight,
		$heightShort,
		number_format($heightShort*100/$heightTotal,$percentDigit).'%',
		$heightRathershort,
		number_format($heightRathershort*100/$heightTotal,$percentDigit).'%',
		$heightStandard,
		number_format($heightStandard*100/$heightTotal,$percentDigit).'%',
		$heightRatherheight,
		number_format($heightRatherheight*100/$heightTotal,$percentDigit).'%',
		$heightVeryheight,
		number_format($heightVeryheight*100/$heightTotal,$percentDigit).'%',
		'','',
		'','',
	];
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.item.-weightform caption {background:#FFAE00; color:#333; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef; font-weight: bold;}
	.item.-weightform td:nth-child(n+2) {width:70px;}
	.item.-weightform td:nth-child(n+4) {width:50px;}
	.item.-weightform td:first-child {white-space: nowrap;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td:nth-child(n+2) {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.item .error td:nth-child(n+1) {background:red; color:#333;}
	.item .error td:nth-child(2),.item .error td:nth-child(3) {text-decoration:underline;}
	.item .subheader th {background:#fff;padding:0;}
	.item .header>th {}
	</style>';

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>