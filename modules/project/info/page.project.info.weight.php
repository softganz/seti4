<?php
/**
 * แบบฟอร์มรายงานภาวะโภชนาการนักเรียน
 *
 * @param Object $topic
 * @param Object $para
 */
define(_KAMSAIINDICATOR,'weight');
define(_INDICATORHEIGHT,'height');

function project_info_weight($self, $tpid, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');


	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin = $projectIn->RIGHT & _IS_ADMIN;

	$percentDigit=2;

	$ret = '';

	//$ret.='trid='.$tranId.print_o($para,'$para');

	if ($tranId) $currentRs=__project_form_weight_gettitle($tranId);

	$navbar='<!--navbar start-->';
	$navbar.='<h3>สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย</h3>';

	$ui=new ui();
	$subui=new ui(NULL,'ui-menu');

	if ($isEdit && (empty($action) || $action == 'list')) {
		$ret .= R::View('button.floating',url('project/'.$tpid.'/info.weight/create'));
	}
	$ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.weight').'"><i class="icon -report"></i><span>สถานการณ์</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.weight/list').'"><i class="icon -list"></i><span>รายการบันทึก</span></a>');
	if ($isEdit) {
		//$ui->add('<a class="btn" href="'.url('project/'.$tpid.'/info.weight/create').'"><i class="icon -add"></i><span>เพิ่มบันทึกสถานการณ์</span></a>');
		$ui->add('<a class="btn -link" href="'.url('project/'.$tpid.'/info.weight/list').'"><i class="icon -edit"></i><span>แก้ไขบันทึกสถานการณ์เดิม</span></a>');
	}
	//if ($tranId) $ui->add('<a class="btn" href="'.url('project/'.$tpid.'/info.weight/view/'.$tranId).'">สถานการณ์ '.($currentRs->year+543).' '.$currentRs->term.'/'.$currentRs->period.'</a>');
	$ui->add('<a class="btn -link" href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์</span></a>');

	if ($tranId) {
		if ($isEdit) {
			$subui->add('<a href="'.url('project/'.$tpid.'/info.weight/view/'.$tranId).'"><i class="icon -view"></i>ดูรายละเอียดบันทึกสถานการณ์</a>');
			$subui->add('<a href="'.url('project/'.$tpid.'/info.weight/modify/'.$tranId).'"><i class="icon -edit"></i>แก้ไขบันทึกสถานการณ์เดิม</a>');
			$subui->add('<sep>');
			$subui->add('<a href="'.url('project/'.$tpid.'/info.weight/create').'"><i class="icon -add"></i>เพิ่มบันทึกสถานการณ์</a>');
			$subui->add('<sep>');
			$subui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.weight/remove/'.$tranId).'" data-title="ลบบันทึกสถานการณ์" data-confirm="ต้องการลบบันทึกสถานการณ์นี้ กรุณายืนยัน"><i class="icon -delete"></i>ลบบันทึกสถานการณ์</a>');
		}
		if ($isAdmin) $subui->add('<a href="'.url('project/'.$tpid.'/info.weight/view/'.$tranId).'"><i class="icon -refresh"></i>รีเฟรช</a>');
		$ui->add(sg_dropbox($subui->build('ul')));
	}

	$navbar .= '<nav class="nav -page -no-print">'
					.$ui->build('ul')
					.($subui->count() ? sg_dropbox($subui->build('ul'),'{class:"leftside -atright"}') : '')
					.'</nav><!-- nav -->'._NL;

	$self->theme->navbar=$navbar;


	switch ($action) {
		case 'create' :
			if ($isEdit) {
				if (post('checkdup')) {
					$r['isDup']=__project_form_weight_duplicate($tpid,NULL,post('year'),post('termperiod'));
					$r['msg']='OK';
					$r['para']=print_o(post(),'post');
					die(json_encode($r));
				} else {
					$ret.=__project_form_weight_create($tpid);
				}
			}
			return $ret;
			break;

		case 'modify' :
			if ($isEdit) {
				if (post('checkdup')) {
					$r['isDup']=__project_form_weight_duplicate($tpid,$tranId,post('year'),post('termperiod'));
					$r['msg']='OK';
					$r['para']=print_o(post(),'post');
					$r['stmt']=mydb()->_query;
					die(json_encode($r));
				} else {
					$ret.=__project_form_weight_create($tpid,$tranId);
				}
			}
			return $ret;
			break;

		case 'view' :
			$ret.=__project_form_weight_view($tpid,$tranId,$isEdit);
			return $ret;
			break;

		case 'remove' :
			if ($isEdit) $ret.=__project_form_weight_remove($tpid,$tranId);
			location('project/'.$tpid.'/info.weight');
			return $ret;
			break;

	}





	$qtvalue->getweight=$qtarray['thin']+$qtarray['ratherthin']+$qtarray['willowy']+$qtarray['plump']+$qtarray['gettingfat']+$qtarray['fat'];

	$weightSchool=R::model('project.weight.get',$tpid);

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
											.'<a class="noprint" href="'.url('project/'.$tpid.'/info.weight/view/'.$rs->trid).'" title="รายละเอียด"><icon class="icon -viewdoc"></i></a>'
											.($isEdit?'<a class="noprint" href="'.url('project/'.$tpid.'/info.weight/modify/'.$rs->trid).'" title="แก้ไข"><icon class="icon -edit"></i></a>':'')
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
			.'<a class="noprint" href="'.url('project/'.$tpid.'/info.weight/view/'.$rs->trid).'" title="รายละเอียด"><icon class="icon -viewdoc"></i></a>'
			.($isEdit?'<a class="noprint" href="'.url('project/'.$tpid.'/info.weight/modify/'.$rs->trid).'" title="แก้ไข"><i class="icon -edit"></i></a>':'')
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
	return $ret;
}

function __project_form_weight_duplicate($tpid,$tranId,$year,$termperiod) {
	list($term,$period)=explode(':',$termperiod);
	$stmt='SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" AND `detail1`=:year AND `detail2`=:term AND `period`=:period '.($tranId ? 'AND `trid`!=:trid':'').' LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid, ':trid',$tranId, ':formid',_KAMSAIINDICATOR, ':area',$area, ':year',$year, ':term',$term, ':period',$period);
	$isDup=$rs->trid?$rs->trid:false;
	return $isDup;
}

function __project_form_weight_create($tpid,$tranId = NULL) {
	$post=(object)post('weight');
	if ((array)$post) {
		if (!($tpid && $post->year && $post->termperiod && $post->postby && $post->dateinput)) {
			return message('error','ข้อมูลไม่ครบถ้วน');
		} else if (__project_form_weight_duplicate($tpid,$tranId,$post->year,$post->termperiod)) {
			return message('error','ข้อมูลของปีการศึกษา '.($post->year+543)." ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!");
		};

		$post->tpid=$tpid;
		$post->trid=$tranId;
		$post->formid=_KAMSAIINDICATOR;
		list($post->term,$post->period)=explode(':',$post->termperiod);
		$post->uid=i()->uid;
		$post->dateinput=sg_date($post->dateinput,'Y-m-d 00:00:00');
		$post->order=mydb::select('SELECT MAX(`sorder`) maxorder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" LIMIT 1',':tpid',$tpid,':formid',_KAMSAIINDICATOR)->maxorder+1;
		//$ret.=mydb()->_query.'<br />';
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
						(
						`trid`, `tpid`, `uid`, `formid`, `part`, `sorder`
						, `detail1`, `detail2`, `period`, `detail4` , `date1`
						, `created`
						)
						VALUES
						(
						:trid, :tpid, :uid, :formid, "title", :order
						, :year, :term, :period, :postby, :dateinput
						, :created
						)
						ON DUPLICATE KEY UPDATE
						`detail1`=:year
						, `detail2`=:term
						, `period`=:period
						, `detail4`=:postby
						, `date1`=:dateinput';
		mydb::query($stmt,$post);
		if (!$tranId) $tranId=mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';

		$qt=post('qt');
		$qttrid=array();
		$stmt='SELECT `trid`,`sorder`,`part`
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `parent`=:trid
							AND `formid`=:formid AND `part`=:formid
						ORDER BY `sorder` ASC';
		foreach (mydb::select($stmt,':tpid',$tpid, ':trid',$tranId,':formid',_KAMSAIINDICATOR)->items as $item) {
			$qttrid[$item->sorder]=$item->trid;
		}
		//$ret.=mydb()->_query;
		//$ret.=print_o($qttrid,'$qttrid');
		foreach ($qt as $qtno => $qtarray) {
			unset($qtvalue);
			$qtvalue->trid=$qttrid[$qtno];
			$qtvalue->tpid=$tpid;
			$qtvalue->parent=$tranId;
			$qtvalue->uid=i()->uid;
			$qtvalue->sorder=$qtno;
			$qtvalue->formid=_KAMSAIINDICATOR;
			$qtvalue->part=_KAMSAIINDICATOR;
			$qtvalue->total=$qtarray['total'];
			$qtvalue->getweight=$qtarray['thin']+$qtarray['ratherthin']+$qtarray['willowy']+$qtarray['plump']+$qtarray['gettingfat']+$qtarray['fat'];
			$qtvalue->choice1=$qtarray['thin'];
			$qtvalue->choice2=$qtarray['ratherthin'];
			$qtvalue->choice3=$qtarray['willowy'];
			$qtvalue->choice4=$qtarray['plump'];
			$qtvalue->choice5=$qtarray['gettingfat'];
			$qtvalue->choice6=$qtarray['fat'];
			$qtvalue->created=date('U');
			$stmt='INSERT INTO %project_tr%
						(
							`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`,
							`num1`, `num2`, `num5`, `num6`, `num7`, `num8`, `num9`, `num10`, `created`
						)
						VALUES
						(
							:trid, :tpid, :parent, :uid, :sorder, :formid, :part,
							:total, :getweight, :choice1, :choice2, :choice3, :choice4, :choice5, :choice6, :created
						)
						ON DUPLICATE KEY UPDATE
							`num1`=:total, `num2`=:getweight,
							`num5`=:choice1, `num6`=:choice2, `num7`=:choice3,
							`num8`=:choice4, `num9`=:choice5, `num10`=:choice6';
			mydb::query($stmt,$qtvalue);
			//$ret.=mydb()->_query.'<br />';
		}

		$qt=post('height');
		$qttrid=array();
		$stmt='SELECT `trid`,`sorder`,`part`
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `parent`=:trid
							AND `formid`=:formid AND `part`=:formid
						ORDER BY `sorder` ASC';
		foreach (mydb::select($stmt,':tpid',$tpid, ':trid',$tranId,':formid',_INDICATORHEIGHT)->items as $item) {
			$qttrid[$item->sorder]=$item->trid;
		}
		//$ret.=mydb()->_query;
		//$ret.=print_o($qttrid,'$qttrid');
		foreach ($qt as $qtno => $qtarray) {
			unset($qtvalue);
			$qtvalue->trid=$qttrid[$qtno];
			$qtvalue->tpid=$tpid;
			$qtvalue->parent=$tranId;
			$qtvalue->uid=i()->uid;
			$qtvalue->sorder=$qtno;
			$qtvalue->formid=_INDICATORHEIGHT;
			$qtvalue->part=_INDICATORHEIGHT;
			$qtvalue->total=$qtarray['total'];
			$qtvalue->getheight=$qtarray['short']+$qtarray['rathershort']+$qtarray['standard']+$qtarray['ratherheight']+$qtarray['veryheight'];
			$qtvalue->choice1=$qtarray['short'];
			$qtvalue->choice2=$qtarray['rathershort'];
			$qtvalue->choice3=$qtarray['standard'];
			$qtvalue->choice4=$qtarray['ratherheight'];
			$qtvalue->choice5=$qtarray['veryheight'];
			$qtvalue->created=date('U');
			$stmt='INSERT INTO %project_tr%
						(
							`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`,
							`num1`, `num2`, `num5`, `num6`, `num7`, `num8`, `num9`, `created`
						)
						VALUES
						(
							:trid, :tpid, :parent, :uid, :sorder, :formid, :part,
							:total, :getheight, :choice1, :choice2, :choice3, :choice4, :choice5, :created
						)
						ON DUPLICATE KEY UPDATE
							`num1`=:total, `num2`=:getheight,
							`num5`=:choice1, `num6`=:choice2, `num7`=:choice3,
							`num8`=:choice4, `num9`=:choice5';
			mydb::query($stmt,$qtvalue);
			//$ret.=mydb()->_query.'<br />';
		}

		//$ret.=print_o($post,'$post');
		//$ret.=print_o($qt,'$qt');
		location('project/'.$tpid.'/info.weight'.($tranId?'/view/'.$tranId:''));
	} else if ($tranId) {
		$post=__project_form_weight_gettitle($tranId);
	}

	$form = new Form([
		'variable' => 'weight',
		'action' => url('project/'.$tpid.'/info.weight/'.($tranId?'modify/'.$tranId:'create')),
		'id' => 'weight-add',
		'class' => 'container',
		'title' => '<h3>สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย</h3>',
		'children' => [
			'<div class="row -flex">',
			'year' => [
				'type' => 'radio',
				'label' => 'ปีการศึกษา :',
				'require' => true,
				'options' => (function() {
					$options = [];
					for ($i = 2015; $i <= date('Y'); $i++) $options[$i] = $i+543;
					if (date('m') >= 10) $options[date('Y')] = date('Y')+543;
					return $options;
				})(),
				'value' => $post->year,
				'container' => '{class: "col -md-4"}',
			],
			'termperiod' => [
				'type' => 'radio',
				'label' => 'ภาคการศึกษา :',
				'require' => true,
				'options' => [
					'1:1'=>'ภาคการศึกษา 1 ต้นเทอม',
					'1:2'=>'ภาคการศึกษา 1 ปลายเทอม',
					'2:1'=>'ภาคการศึกษา 2 ต้นเทอม',
					'2:2'=>'ภาคการศึกษา 2 ปลายเทอม',
				],
				'value' => $post->termperiod,
				'container' => '{class: "col -md-4"}',
			],
			// 'period' => [
			// 	'type' => 'radio',
			// 	'label' => 'ช่วงเวลา :',
			// 	'require' => true,
			// 	'options' => ['1'=>'ก่อนทำโครงการ','2'=>'ระหว่างทำโครงการ','3'=>'หลังทำโครงการ'],
			// 	'value' => $post->period,
			// ],

			'<div class="form-item col -md-4">',
			'postby' => [
				'type' => 'text',
				'label' => 'ผู้ประเมิน',
				'require' => true,
				'value' => $post->postby,
			],
			'dateinput' => [
				'type' => 'text',
				'label' => 'วันที่ชั่ง/วัด',
				'class' => 'sg-datepicker',
				'require' => true,
				'value' => $post->dateinput?sg_date($post->dateinput,'d/m/Y'):'',
			],
			'</div>',
			'</div>',

			// Weight
			(function($tpid, $tranId) {
				// $tables->thead = ['ครั้งที่','ปีการศึกษา','ภาคการศึกษา','ช่วงเวลา','ผู้ประเมิน','วันที่ชั่ง/วัด','ผอม<br />(%)','ค่อนข้างผอม<br />(%)','สมส่วน<br />(%)','ท้วม<br />(%)','เริ่มอ้วน<br />(%)','อ้วน<br />(%)',''];

				$tables = new Table('item -input -weight');
				$tables->addConfig('showHeader',false);
				$tables->caption = 'สถานการณ์ภาวะโภชนาการนักเรียน - น้ำหนักตามเกณฑ์ส่วนสูง';
				$tables->thead = ['ชั้น','amt total'=>'จำนวนนักเรียน<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง<br />(คน)','ผอม<br />(คน)','ค่อนข้างผอม<br />(คน)','สมส่วน<br />(คน)','ท้วม<br />(คน)','เริ่มอ้วน<br />(คน)','อ้วน<br />(คน)'];

				$stmt='SELECT
						  qt.`question`
						, qt.`qtgroup`
						, qt.`qtno`
						, tr.`parent`
						, tr.`part`
						, tr.`sorder`
						, tr.`num1` total
						, tr.`num2` getweight
						, tr.`num5` thin
						, tr.`num6` ratherthin
						, tr.`num7` willowy
						, tr.`num8` plump
						, tr.`num9` gettingfat
						, tr.`num10` fat
						, qt.`description`
					FROM %qt% qt
						LEFT JOIN %project_tr% tr
							ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
								AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
					WHERE `qtgroup`="schoolclass"
					ORDER BY `qtgroup` ASC, `qtno` ASC';
				$qtResultDbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_KAMSAIINDICATOR);

				$i=0;
				foreach ($qtResultDbs->items as $rs) {
					$i++;
					if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
					if (in_array($rs->qtno,array(11,21,31))) {
						$tables->rows[]='<tr class="subheader"><th colspan="9"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
					}
					$tables->rows[] = [
						$rs->question
						//.'<br />'.$stdKey.print_o($rs,'$rs')
						,
						'<input class="form-text -numeric -total" type="text" size="3" name="qt['.$rs->qtno.'][total]" value="'.number_format($rs->total).'" autocomplete="off" />',
						'<span id="schoolclass'.$rs->qtno.'">'.number_format($rs->getweight).'</span>',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][thin]" value="'.number_format($rs->thin).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][ratherthin]" value="'.number_format($rs->ratherthin).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][willowy]" value="'.number_format($rs->willowy).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][plump]" value="'.number_format($rs->plump).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][gettingfat]" value="'.number_format($rs->gettingfat).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][fat]" value="'.number_format($rs->fat).'" autocomplete="off" />',
					];
					$subtotal += $rs->answer;
				}
				return $tables->build();
			})($tpid, $tranId),

			// Height
			(function($tpid, $tranId) {
				$tables=new Table('item -input -height');
				$tables->addConfig('showHeader',false);
				$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูงตามเกณฑ์อายุ';
				$tables->thead=array('ชั้น','amt total'=>'จำนวนนักเรียนทั้งหมด<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่วัดส่วนสูง<br />(คน)','เตี้ย<br />(คน)','ค่อนข้างเตี้ย<br />(คน)','สูงตามเกณฑ์<br />(คน)','ค่อนข้างสูง<br />(คน)','สูง<br />(คน)');
				$stmt='SELECT
						  qt.`question`
						, qt.`qtgroup`
						, qt.`qtno`
						, tr.`parent`
						, tr.`part`
						, tr.`sorder`
						, tr.`num1` total
						, tr.`num2` getheight
						, tr.`num5` short
						, tr.`num6` rathershort
						, tr.`num7` standard
						, tr.`num8` ratherheight
						, tr.`num9` veryheight
						, qt.`description`
					FROM %qt% qt
						LEFT JOIN %project_tr% tr
							ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
								AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
					WHERE `qtgroup`="schoolclass"
					ORDER BY `qtgroup` ASC, `qtno` ASC';
				$qtResultDbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_INDICATORHEIGHT);

				$i=0;
				foreach ($qtResultDbs->items as $rs) {
					$i++;
					if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
					if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<tr class="subheader"><th colspan="8"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
					$tables->rows[] = [
						$rs->question,
						'<input class="form-text -numeric -total" type="text" size="3" name="height['.$rs->qtno.'][total]" value="'.number_format($rs->total).'" autocomplete="off" />',
						'<span id="schoolclass'.$rs->qtno.'">'.number_format($rs->getheight).'</span>',
						'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][short]" value="'.number_format($rs->short).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][rathershort]" value="'.number_format($rs->rathershort).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][standard]" value="'.number_format($rs->standard).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][ratherheight]" value="'.number_format($rs->ratherheight).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][veryheight]" value="'.number_format($rs->veryheight).'" autocomplete="off" />',
					];
					$subtotal+=$rs->answer;
				}
				return $tables->build();
			})($tpid, $tranId),

			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/'.$tpid.'/info.weight').'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			],
		], // children
	]);

	$ret .= $form->build();

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	$ret.='<style type="text/css">
	.item.-input {margin:40px 0 80px 0;}
	.item.-input caption {background: #FFAE00; color: #333; font-size: 1.4em; padding:8px 0;}
	.item.-input td:nth-child(n+2) {width:80px;}
	.item.-input td:nth-child(3) input {font-weight:bold;}
	.item.-input td:nth-child(4) {font-weight:bold;}
	.item.-input input {margin:0 auto; display:block;}
	.item.-input tr:nth-child(2n+1) td, .item.-weight tr:nth-child(2n+1) td {background-color:#FFF7C9;}
	.item.-input h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item.-input .-error, .item .-error .form-text {color:red;}
	.item.-input .subheader th {background:#fff;padding:0;}

	form>div>.form-item {margin: 0; padding:0;}
	form>div>.form-item:first-child {margin-left:0;}
	form>div>.form-item:last-child {margin-right:0;}
	form>#form-item-edit-weight-submit {display:block; border:none;}
	.container>.row.-flex>.col {float: none; padding: 8px 16px; margin: 16px 16px 16px 0;}

	@media (min-width:45em) { /* 720/16 */
	form>div>.form-item {margin: 16px; padding:0 16px; display: inline-block; border: 1px #ccc solid; vertical-align: top; border-radius:2px;}
	}
	</style>';

	$ret.='<script type="text/javascript">
	var i=0;
	var formSubmit=false;

	haveRowError();
	// Check total error
	function haveRowError() {
		var isError=false;
		$(".item tr.even, .item tr.odd").each(function(i){
			var $this=$(this);
			var total=parseInt($this.find(".-total").val());
			var itemTotal=parseInt($this.find(".getweight>span").text());
			//console.log("Row="+i+"Total="+total+" itemTotal="+itemTotal);
			if (total<itemTotal) {
				$this.addClass("-error");
				isError=true;
				console.log("Error row ="+i);
			}
		});
		return isError;
	}

	$(document).on("keydown keyup",".item.-input .form-text",function(event) {
		var keyCode=event.keyCode;
		var keyChar=event.which;
		console.log("keyChar="+keyChar+" keyCode="+keyCode);

		if (keyCode==13) {
			event.stopPropagation();
			console.log("Enter key was press");
			return false;
		}

		var $this=$(this);
		var $row=$this.closest("tr");
		var total=parseInt($row.find(".-total").val());
		var itemTotal=0;

		if (/\D/g.test(this.value)) {
			// Filter non-digits from input value.
			this.value = this.value.replace(/\D/g, "");
		}

		console.log("Change to "+$this.val()+" key="+keyCode+" row="+$row.attr("class")+" total="+total);

		var debug="";
		$row.find(".-item").each(function(i){
			debug+=$(this).attr("class")+"="+$(this).val();
			var itemValue=parseInt($(this).val());
			if (isNaN(itemValue)) {
				itemValue=0;
				//$(this).val(0);
				console.log("ITEMVALUE IS NaN");
			}
			itemTotal+=itemValue;
		});

		if ($this.hasClass("-total")) total=parseInt($this.val());
		console.log("itemValue="+$this.val()+" itemTotal="+itemTotal);
		$row.find(".getweight>span").text(itemTotal);
		if (total<itemTotal) {
			$row.addClass("-error");
		} else {
			$row.removeClass("-error");
		}
		console.log(debug);
	});

	$("#weight-add").submit(function() {
		if (formSubmit) return true;
		var $form=$(this);
		var errorField;
		notify();
		if (!$("input[name=\'weight[year]\']:checked").val()) errorField="edit-weight-year";
		else if (!$("input[name=\'weight[termperiod]\']:checked").val()) errorField="edit-weight-termperiod";
		else if ($("#edit-weight-postby").val().trim()=="") errorField="edit-weight-postby";
		else if ($("#edit-weight-dateinput").val().trim()=="") errorField="edit-weight-dateinput";
		if (errorField) {
			var errorFieldLabel=$("#form-item-"+errorField+">label").text();
			notify("กรุณาป้อนข้อมูล :: "+errorFieldLabel,30000);
			$("#"+errorField).focus();
		} else {
			// Check year/termperiod is duplicate
			var para={}
			para.checkdup="yes";
			para.trid=$("#edit-weight-trid").val();
			para.year=$("input[name=\'weight[year]\']:checked").val();
			para.termperiod=$("input[name=\'weight[termperiod]\']:checked").val();
			var url=$(this).attr("action");
			//notify("Check duplicate "+(++i)+url+"?checkdup=yes&year="+para.year+"&termperiod="+para.termperiod);
			$.ajax({
				url: url,
				type: "POST",
				data: para,
				dataType: "json",
				success: function(data) {
						//notify("Result = "+data.isDup+"<br />"+data.para+data.stmt);
						if (haveRowError()) {
							notify("ข้อมูลบางชั้นเรียนไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง",3000);
						} else if (data.isDup) {
							notify("ข้อมูลของปีการศึกษา "+(parseInt(para.year)+543)+" ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!",30000);
						} else {
							notify("กำลังบันทึกข้อมูล...");
							formSubmit=true;
							$form.submit();
						}
					},
			})
		}
		return false;
	});
	</script>';
	return $ret;
}

function __project_form_weight_view($tpid,$tranId,$isEdit) {
	$formid=_KAMSAIINDICATOR;
	$percentDigit=2;

	$title=__project_form_weight_gettitle($tranId);

	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`trid`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` getweight
					, tr.`num5` thin
					, tr.`num6` ratherthin
					, tr.`num7` willowy
					, tr.`num8` plump
					, tr.`num9` gettingfat
					, tr.`num10` fat
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$dbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_KAMSAIINDICATOR);

	$ret.='<h3>ปีการศึกษา : <strong>'.($title->year+543).'</strong> ภาคการศึกษา : <strong>'.$title->term.'</strong> ครั้งที่ : <strong>'.$title->period.'</strong> ผู้ประเมิน : <strong>'.$title->postby.'</strong> วันที่ชั่ง/วัด : <strong>'.sg_date($title->dateinput,'ว ดด ปป').'</strong>';

	$ret.='</h3>';

	$weightTotal=$weightGetweight=$weightThin=$weightRatherthin=$weightWillowy=$weightPlump=$weightGettingfat=$weightFat=0;
	$tables=new table('item -weightform');
	$tables->addConfig('showHeader',false);
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - น้ำหนักตามเกณฑ์ส่วนสูง';
	$tables->colgroup=array('','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead=array('ชั้น','amt total'=>'จำนวนนักเรียน<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง<br />(คน)','ผอม<br />(คน)','%','ค่อนข้างผอม<br />(คน)','%','สมส่วน<br />(คน)','%','ท้วม<br />(คน)','%','เริ่มอ้วน<br />(คน)','%','อ้วน<br />(คน)','%');
	$tables->thead='<tr><th rowspan="2">ชั้น</th><th rowspan="2">จำนวนนักเรียน<br />(คน)</th><th rowspan="2">จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง<br />(คน)</th><th colspan="2">ผอม</th><th colspan="2">ค่อนข้างผอม</th><th colspan="2">สมส่วน</th><th colspan="2">ท้วม</th><th colspan="2">เริ่มอ้วน</th><th colspan="2">อ้วน</th><th colspan="2">เริ่มอ้วน+อ้วน</th></tr><tr><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th></tr>';
	foreach ($dbs->items as $rs) {
		$totalError=$rs->total<$rs->getweight;
		if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="17"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
			$subWeightTotal=$subWeightGetweight=$subWeightThin=$subWeightRatherthin=$subWeightWillowy=$subWeightPlump=$subWeightGettingfat=$subWeightFat=0;
		}
		$tables->rows[]=array(
											$rs->question,
											number_format($rs->total).($totalError?'!':''),
											number_format($rs->getweight).($totalError?'!':''),
											number_format($rs->thin),
											number_format($rs->thin*100/$rs->total,$percentDigit).'%',
											number_format($rs->ratherthin),
											number_format($rs->ratherthin*100/$rs->total,$percentDigit).'%',
											number_format($rs->willowy),
											number_format($rs->willowy*100/$rs->total,$percentDigit).'%',
											number_format($rs->plump),
											number_format($rs->plump*100/$rs->total,$percentDigit).'%',
											number_format($rs->gettingfat),
											number_format($rs->gettingfat*100/$rs->total,$percentDigit).'%',
											number_format($rs->fat),
											number_format($rs->fat*100/$rs->total,$percentDigit).'%',
											number_format($rs->gettingfat+$rs->fat),
											number_format(($rs->gettingfat+$rs->fat)*100/$rs->total,$percentDigit).'%',
											'config'=>array('class'=>$totalError?'error -weight':''),
											);
		$subWeightTotal+=$rs->total;
		$subWeightGetweight+=$rs->getweight;
		$subWeightThin+=$rs->thin;
		$subWeightRatherthin+=$rs->ratherthin;
		$subWeightWillowy+=$rs->willowy;
		$subWeightPlump+=$rs->plump;
		$subWeightGettingfat+=$rs->gettingfat;
		$subWeightFat+=$rs->fat;

		if (in_array($rs->qtno,array(13,26,33))) {
			$tables->rows[]=array(
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
												'config'=>array('class'=>'subfooter')
												);
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
	$tables->tfoot[]=array(
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
										);
	$ret.=$tables->build();


	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`trid`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` getheight
					, tr.`num5` short
					, tr.`num6` rathershort
					, tr.`num7` standard
					, tr.`num8` ratherheight
					, tr.`num9` veryheight
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$dbs=mydb::select($stmt,':trid',$tranId,':tpid',$tpid,':formid',_INDICATORHEIGHT);





	$heightTotal=$heightGetheight=$heightShort=$heightRathershort=$heightStandard=$heightRatherheight=$heightVeryheight=0;

	$tables=new table('item -weightform');
	$tables->addConfig('showHeader',false);
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูงตามเกณฑ์อายุ';
	$tables->colgroup=array('','amt student'=>'','amt bad'=>'','amt badpercent'=>'','amt fair'=>'','amt fairpercent'=>'','amt good'=>'','amt goodpercent'=>'');
	$tables->thead=array('ชั้น','amt total'=>'จำนวนนักเรียนทั้งหมด<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่วัดส่วนสูง<br />(คน)','เตี้ย<br />(คน)','%','ค่อนข้างเตี้ย<br />(คน)','%','สูงตามเกณฑ์<br />(คน)','%','ค่อนข้างสูง<br />(คน)','%','สูง<br />(คน)','%','','');
	$tables->thead='<tr><th rowspan="2">ชั้น</th><th rowspan="2">จำนวนนักเรียนทั้งหมด<br />(คน)</th><th rowspan="2">จำนวนนักเรียนที่วัดส่วนสูง<br />(คน)</th><th colspan="2">เตี้ย</th><th colspan="2">ค่อนข้างเตี้ย</th><th colspan="2">สูงตามเกณฑ์</th><th colspan="2">ค่อนข้างสูง</th><th colspan="2">สูง</th><th colspan="2"></th><th colspan="2"></th></tr><tr><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th>คน</th><th>%</th><th></th><th></th><th></th><th></th></tr>';
	foreach ($dbs->items as $rs) {
		$totalError=$rs->total<$rs->getheight;
		if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="17"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
			$subHeightTotal=$subHeightGetheight=$subHeightShort=$subHeightRathershort=$subHeightStandard=$subHeightRatherheight=$subHeightVeryheight=0;
		}
		$tables->rows[]=array(
											$rs->question,
											number_format($rs->total).($totalError?'!':''),
											number_format($rs->getheight).($totalError?'!':''),
											number_format($rs->short),
											number_format($rs->short*100/$rs->total,$percentDigit).'%',
											number_format($rs->rathershort),
											number_format($rs->rathershort*100/$rs->total,$percentDigit).'%',
											number_format($rs->standard),
											number_format($rs->standard*100/$rs->total,$percentDigit).'%',
											number_format($rs->ratherheight),
											number_format($rs->ratherheight*100/$rs->total,$percentDigit).'%',
											number_format($rs->veryheight),
											number_format($rs->veryheight*100/$rs->total,$percentDigit).'%',
											'',
											'',
											'',
											'',
											'config'=>array('class'=>$totalError?'error -weight':''),
											);

		$subHeightTotal+=$rs->total;
		$subHeightGetheight+=$rs->getheight;
		$subHeightShort+=$rs->short;
		$subHeightRathershort+=$rs->rathershort;
		$subHeightStandard+=$rs->standard;
		$subHeightRatherheight+=$rs->ratherheight;
		$subHeightVeryheight+=$rs->veryheight;

		if (in_array($rs->qtno,array(13,26,33))) {
			$tables->rows[]=array(
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
												'config'=>array('class'=>'subfooter')
												);
		}

		$heightTotal+=$rs->total;
		$heightGetheight+=$rs->getheight;
		$heightShort+=$rs->short;
		$heightRathershort+=$rs->rathershort;
		$heightStandard+=$rs->standard;
		$heightRatherheight+=$rs->ratherheight;
		$heightVeryheight+=$rs->veryheight;
	}
	//$tables->rows[]=array('รวมชั้นอนุบาล','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นประถมศึกษาปีที่ 1-6','','','','','','','','','','','','','','');
	//$tables->rows[]=array('รวมชั้นมัธยมศึกษาปีที่ 1-3','','','','','','','','','','','','','','');
	$tables->tfoot[]=array(
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
										);
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.item.-weightform caption {background:#FFAE00; color:#333; font-size:1.4em;}
	.item.-weightform td:nth-child(2n+1) {color:#999;}
	.item.-weightform td:nth-child(2n+2) {background:#efefef; font-weight: bold;}
	.item.-weightform td:nth-child(n+2) {width:50px;}
	.item.-weightform td:first-child, .item.-weightform td:nth-child(3) {color:#333;}
	.item td:nth-child(n+2) {text-align: center;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item .student {font-weight:bold;}
	.item .error td:nth-child(n+1) {background:red; color:#333;}
	.item .error td:nth-child(2),.item .error td:nth-child(3) {text-decoration:underline;}
	.item .subheader th {background:#fff;padding:0;}
	</style>';

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_form_weight_gettitle($tranId) {
	$stmt='SELECT `trid`, `tpid`, `sorder`, `detail1` `year`, `detail2` `term`, `period`, `detail3` `area`, `detail4` `postby`, `date1` `dateinput` FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
	$rs=mydb::select($stmt,':trid',$tranId);
	if ($rs->_num_rows) $rs->termperiod=$rs->term.':'.$rs->period;
	return $rs;
}


function __project_form_weight_remove($tpid,$tranId) {
	$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid AND `tpid`=:tpid AND `formid`="weight" AND `part`="title"';
	mydb::query($stmt,':trid',$tranId,':tpid',$tpid);
	//$ret.=mydb()->_query.'<br />';

	$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:trid AND `formid`="weight" AND `part`="weight"';
	mydb::query($stmt,':trid',$tranId,':tpid',$tpid);
	//$ret.=mydb()->_query.'<br />';

	$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:trid AND `formid`="height" AND `part`="height"';
	mydb::query($stmt,':trid',$tranId,':tpid',$tpid);
	//$ret.=mydb()->_query.'<br />';

	return $ret;
}

?>