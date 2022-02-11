<?php
/**
 * Poorman report by area
 * @param $_GET
 * @return String
 */
function imed_report_poormanarea($self) {
	$title = 'คนยากลำบาก';
	$prov = post('p'); //SG\getFirst($_REQUEST['p'],'90');
	$ampur = post('a');
	$tambon = post('t');
	$village = post('v');
	$getArea = post('ar');

	$reportType=SG\getFirst(post('r'),'area');
	$graphType=strtolower(SG\getFirst(post('g'),'pie'));
	$detail=post('detail');
	$order=post('o');

	$debug = post('debug') && user_access('admin');

	$commune = post('commune');
	$for_year = post('for_year');
	$for_type = post('d');
	$for_educate = post('e');
	$for_course = post('for_course');
	$for_gov = post('for_gov');
	$for_needcommu = post('for_needcommu');
	$for_sex = post('for_sex');
	$for_occupa = post('for_occupa');
	$for_married = post('for_married');
	$for_house = post('for_house');
	$for_health = post('for_health');

	$orderBy='`value` DESC';
	//$orderBy='ORDER BY `label` IS NULL, `label` ASC';

	//$ret.='$reportType='.$reportType.' Area = '.$getArea.' Province = '.$prov.' Tambon = '.$tambon.' Village = '.$village.' graph type = '.$graphType;
	//$ret.=print_o(post(),'post()');

	$isAdmin = user_access('administer imeds');

	$isFirstRequest = post('r') == '';

	/*
	$areaList = array(
		1 => array('name'=>'เขต 1 เชียงราย','prov'=>'57,55,56,54,50,58,52,51'),
		2 => array('name'=>'เขต 2 ตาก','prov'=>'63,65,67,64,53'),
		3 => array('name'=>'เขต 3 ชัยนาท','prov'=>'18,62,66,60,61'),
		4 => array('name'=>'เขต 4 ลพบุรี','prov'=>'16,17,15,14,26,12,13,19'),
		5 => array('name'=>'เขต 5 กาญจนบุรี','prov'=>'71,73,70,72,77,76,75,74'),
		6 => array('name'=>'เขต 6 ฉะเชิงเทรา','prov'=>'24,25,27,11,22,20,23,21'),
		7 => array('name'=>'เขต 7 กาฬสินธุ์','prov'=>'46,40,44,45,'),
		8 => array('name'=>'เขต 8 บึงกาฬ','prov'=>'38,42,43,39,41,48,47'),
		9 => array('name'=>'เขต 9 ชัยภูมิ','prov'=>'36,30,31,32'),
		10 => array('name'=>'เขต 10 มุกดาหาร','prov'=>'49,35,33,34,37'),
		11 => array('name'=>'เขต 11 สุราษฎร์ธานี','prov'=>'86,80,84,81,82,83,85'),
		12 => array('name'=>'เขต 12 สงขลา','prov'=>'93,92,91,90,94,95,96'),
		13 => array('name'=>'เขต 13 กรุงเทพมหานคร','prov'=>'10'),
	);
	*/

	$areaList = cfg('imedAreaList');


	$orderArr = array(
		'na'=>'ชื่อ:name',
		'cd'=>'วันที่ป้อน:q.qtdate',
		'ar'=>'พื้นที่:CONVERT(copv.`provname` USING tis620), CONVERT(codist.`distname` USING tis620), CONVERT(cosd.`subdistname` USING tis620), p.`village`+0',
		//	'tb'=>'ตำบล:p.tambon',
		//	'vi'=>'หมู่บ้าน:p.village+0',
		//	'age'=>'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label'
	);
	list(,$listOrderBy)=explode(':',$orderArr[$order]);

	$fldList=R::Model('imed.poorman.field');

	$zones=imed_model::get_user_zone(i()->uid,'imed.poorman');

	if ($isFirstRequest) {
		$ret.='<form class="report-form sg-form" id="report-form" data-rel="replace:#report-output" method="get" action="'.url(q()).'"><input type="hidden" name="r" id="reporttype" value="'.$reportType.'" /><input type="hidden" name="g" id="graphtype" value="'.$graphType.'" />';
		$ret.='<h3>'.$title.'</h3>'._NL;
		$ret.='<div class="form-item -province">'._NL;

		if ($areaList) {
			$ret .= '<select id="area" class="form-select" name="ar"><option value="">-ทุกเขต-</option>';
			foreach ($areaList as $k=>$v) $ret.='<option value="'.$k.'"'.($k==$getArea?' selected="selected"':'').' data-prov="'.$v['prov'].'">'.$v['name'].'</option>'._NL;
			$ret .= '</select>';
		}

		// Get poorman province
		$stmt='SELECT DISTINCT `provid`, `provname`, COUNT(*) `totals` FROM %qtmast% q LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE q.`qtgroup`=4 AND q.`qtstatus`>=0 GROUP BY `provid` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC';
		$provdbs=mydb::select($stmt);
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">-ทุกจังหวัด-</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.' ('.number_format($rs->totals).' คน)</option>'._NL;
		$ret.='</select>'._NL;

		$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select" '.($prov?'':'style="display:none;"').'>'._NL.'<option value="">-ทุกอำเภอ-</option>'._NL;
		if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
		}
		$ret.='</select>'._NL;
		$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">-ทุกตำบล-</option>'._NL.'</select>'._NL;
		$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">-ทุกหมู่บ้าน-</option>'._NL.'</select>'._NL;
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>'._NL;



		// Condition for only person
		$selectDefect='<select name="d"><option value="-1">-ทุกประเภท-</option>';
		foreach ($fldList as $key=>$item) {
			if (preg_match('/^(POOR.TYPE.LIST.)(.*)/i',$key,$out)) {
				$typeKey=$out[2];
				$selectDefect.='<option value="'.$key.'"'.($typeKey==$for_type?' selected="selected"':'').'>'.$item.'</option>';
			}
		}
		$selectDefect.='</select>';
		//$ret.=print_o($fldList,'$fldList');

		$selectEdu.='<select name="e"><option value="-1">-ทุกระดับ-</option>';
		foreach ($fldList['PSNL.EDUCA.CHOICE'] as $key=>$item) {
			$selectEdu.='<option value="'.$key.'"'.($key==$for_educate?' selected="selected"':'').'>'.$item.'</option>';
		}
		$selectEdu.='</select>';

		$selectForCourse='<select name="for_course"><option value="-1">-ทุกสาเหตุ-</option>';
		foreach ($fldList as $key=>$item) {
			if (preg_match('/^(POOR.CAUSE.LIST.)(.*)/i',$key,$out)) {
				$typeKey=$out[2];
				$selectForCourse.='<option value="'.$key.'"'.($typeKey==$for_course?' selected="selected"':'').'>'.$item.'</option>';
			}
		}
		$selectForCourse.='</select>';

		$selectForGov='<select name="for_gov"><option value="-1">-ทุกรัฐช่วย-</option>';
		foreach ($fldList as $key=>$item) {
			if (preg_match('/^(POOR.NEED.GOV.LIST.)(.*)/i',$key,$out)) {
				$typeKey=$out[2];
				$selectForGov.='<option value="'.$key.'"'.($typeKey==$for_gov?' selected="selected"':'').'>'.$item.'</option>';
			}
		}
		$selectForGov.='</select>';

		$selectNeedCommu='<select name="for_needcommu"><option value="-1">-ทุกชุมชนช่วย-</option>';
		foreach ($fldList as $key=>$item) {
			if (preg_match('/^(POOR.NEED.COMMUNITY.LIST.)(.*)/i',$key,$out)) {
				$typeKey=$out[2];
				$selectNeedCommu.='<option value="'.$key.'"'.($typeKey==$for_needcommu?' selected="selected"':'').'>'.$item.'</option>';
			}
		}
		$selectNeedCommu.='</select>';

		$selectSex='<select name="for_sex"><option value="-1">-ทุกเพศ-</option>';
		foreach (array('ชาย','หญิง') as $key=>$item) {
			$selectSex.='<option value="'.$item.'"'.($item==$for_sex?' selected="selected"':'').'>'.$item.'</option>';
		}
		$selectSex.='</select>';

		$selectOccupa='<select name="for_occupa"><option value="-1">-ทุกอาชีพ-</option>';
		foreach ($fldList['PSNL.OCCUPA.CHOICE'] as $key=>$item) {
			$selectOccupa.='<option value="'.$key.'"'.($key==$for_occupa?' selected="selected"':'').'>'.$item.'</option>';
		}
		$selectOccupa.='</select>';

		$selectMarried='<select name="for_married"><option value="-1">-ทุกสถานภาพ-</option>';
		foreach ($fldList['PSNL.MARRIED.CHOICE'] as $key=>$item) {
			$selectMarried.='<option value="'.$key.'"'.($key==$for_married?' selected="selected"':'').'>'.$item.'</option>';
		}
		$selectMarried.='</select>';

		$selectHouse='<select name="for_house"><option value="-1">-ทุกที่อยู่-</option>';
		foreach ($fldList['PSNL.HOME.STATUS.CHOICE'] as $key=>$item) {
			$selectHouse.='<option value="'.$key.'"'.($key==$for_house?' selected="selected"':'').'>'.$item.'</option>';
		}
		$selectHouse.='</select>';

		$selectHeath='<select name="for_health"><option value="-1">-ทุกสุขภาพ-</option>';
		foreach ($fldList as $key=>$item) {
			if (preg_match('/^(POOR.HEALTH.LIST.)(.*)/i',$key,$out)) {
				$typeKey=$out[2];
				$selectHeath.='<option value="'.$key.'"'.($typeKey==$for_health?' selected="selected"':'').'>'.$item.'</option>';
			}
		}
		$selectHeath.='</select>';		

		$selectYear = '<select name="for_year"><option value="-1">-ทุกปี-</option>';
		for ($i = 2559 - 543; $i <= date('Y'); $i++) {
			$selectYear .= '<option value="'.$i.'"'.($typeKey==$for_year ? ' selected="selected"' : '').'>'.($i + 543).'</option>';
		}
		$selectYear.='</select>';	

		$reportTypeArray = array(
			'area' => array('text'=>'พื้นที่'),
			'year' => array(
				'text' => 'ปี พ.ศ.',
				'select' => $selectYear,
			),
			'commune' => array(
				'text' => 'ชุมชน',
				'select' => '<span><a class="sg-action commune-name" href="'.url('imed/report/selectcommune').'" data-rel="box" data-width="400">เลือกชุมชน</a></span><input type="hidden" name="commune" value="" />',
			),
			'defect' => array(
				'text' => 'ประเภท',
				'select' => $selectDefect,
			),
			'sex' => array(
				'text' => 'เพศ',
				'select' => $selectSex,
			),
			//'age'=>array('text'=>'อายุ'),
			'marry' => array('text'=>'สมรส','select'=>$selectMarried),
			'home' => array('text'=>'ที่อยู่อาศัย','select'=>$selectHouse),
			'edu' => array('text'=>'การศึกษา','select'=>$selectEdu),
			'occu' => array('text'=>'อาชีพ','select'=>$selectOccupa),
			'cause' => array('text'=>'สาเหตุ','select'=>$selectForCourse),
			'health' => array('text'=>'สุขภาพ','select'=>$selectHeath),
			'govhelp' => array('text'=>'รัฐช่วย','select'=>$selectForGov),
			'communehelp' => array('text'=>'ชุมชนช่วย','select'=>$selectNeedCommu),
		);

		$ret.='<a href="javascript:void(0)" class="left"><i class="icon -back"></i></a><div class="toolbar">'._NL.'<ul>';
		foreach ($reportTypeArray as $k=>$v) {
			$ret.='<li'.($k==$reportType?' class="active"':'').'><a href="#'.$k.'">'.$v['text'].'</a>';
			if (isset($v['select'])) $ret.=$v['select'];
			$ret.='</li>'._NL;
		}
		$ret.='</ul></div><a href="javascript:void(0)" class="right"><i class="icon -forward"></i></a>'._NL;

		$ret.='<div class="optionbar"><ul>';
		$ret.='<li><input type="submit" name="g" value="Pie" class="btn -graph'.($graphType=='pie'?'  active':'').'" /> <input type="submit" name="g" value="Bar" class="btn -graph'.($graphType=='bar'?' active':'').'" /> <input type="submit" name="g" value="Col" class="btn -graph'.($graphType=='col'?' active':'').'" /> <input type="submit" name="g" value="Line" class="btn -graph'.($graphType=='line'?' active':'').'" /> <input type="submit" name="g" value="Table" class="btn -graph'.($graphType=='table'?' active':'').'" /></li>';
		if (i()->ok) $ret.='<li><input type="checkbox" name="detail" value="yes"'.($detail=='yes'?' checked="checked"':'').'/> แสดงรายชื่อ ';
		$ret.='<select class="form-select" name="o"><option>--เรียงตาม--</option>';
		foreach ($orderArr as $k=>$v) $ret.='<option value="'.$k.'"'.($order==$k?' selected="selected"':'').'>'.substr($v,0,strpos($v,':')).'</option>';
		$ret.='</select></li>';
		if (user_access('access debugging program')) $ret.='<li><input type="checkbox" name="debug" value="yes"'.($_REQUEST['debug']?' checked="checked"':'').' /> Debug</li>';

		$ret.='</ul></div>';
		$ret.='</form>';
		$ret.='<script>toolbarIndex=0;</script>';
	}



	$ret.='<div id="report-output">';
	if ($graphType!='table') $ret.='<div id="chart_div"></div>';

	unset($stmt);


	$cfg['from']='%qtmast% q';
	$cfg['joins'][]='	LEFT JOIN %db_person% p USING(`psnid`)';
	$cfg['joins'][]='	LEFT JOIN %co_province% copt ON copt.`provid` = p.`changwat`';
	$cfg['joins'][]='	LEFT JOIN %co_district% codt ON codt.`distid` = CONCAT(p.`changwat`, p.`ampur`)';
	$cfg['joins'][]='	LEFT JOIN %co_subdistrict% cost ON cost.`subdistid` = CONCAT(p.`changwat`, p.`ampur`, p.`tambon`)';
	$cfg['joins'][]='	LEFT JOIN %co_village% covt ON covt.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))';

	mydb::where('q.`qtgroup` = 4 AND q.`qtstatus`>=0');
	if ($getArea) mydb::where('p.`changwat` IN (:areaprov)',':areaprov','SET:'.$areaList[$getArea]['prov']);
	if ($prov) mydb::where('p.`changwat` = :prov',':prov',$prov);
	if ($ampur) mydb::where('p.`ampur` = :ampur',':ampur',$ampur);
	if ($tambon) mydb::where('p.`tambon` = :tambon',':tambon',$tambon);
	if ($village) mydb::where('LPAD(p.`village`,2,"0") = :village',':village',intval($village));
	if ($commune) mydb::where('p.`commune` IN (:commune)',':commune','SET-STRING:'.$commune);

	// For choice only
	if ($for_year && $for_year != -1) {
		mydb::where('(YEAR(q.`qtdate`) = :year)',':year',$for_year);
	}

	if ($for_type && $for_type!=-1) {
		mydb::where('(defectType.`part` IS NOT NULL AND defectType.`part`=:defect)',':defect',$for_type);
		$cfg['joins'][]='	LEFT JOIN %qttran% defectType ON defectType.`qtref`=q.`qtref` AND defectType.`part`=:defect';
	}

	if ($for_course && $for_course!=-1) {
		mydb::where('(forCourse.`part` IS NOT NULL AND forCourse.`part` = :for_course AND forCourse.`value`>0)',':for_course',$for_course);
		$cfg['joins'][]='	LEFT JOIN %qttran% forCourse ON forCourse.`qtref`=q.`qtref` AND forCourse.`part`=:for_course';
	}

	if ($for_educate && $for_educate!=-1) {
		mydb::where('(eduType.`part` IS NOT NULL AND eduType.`part`="PSNL.EDUCA" AND eduType.`value`=:educate)',':educate',$for_educate);
		$cfg['joins'][]='	LEFT JOIN %qttran% eduType ON eduType.`qtref`=q.`qtref` AND eduType.`part`="PSNL.EDUCA" AND eduType.`value`=:educate';
		//mydb::where('p.`educate`=:educate',':educate',$for_educate);
	}

	if ($for_gov && $for_gov!=-1) {
		mydb::where('(forGov.`part` IS NOT NULL AND forGov.`part`=:for_gov)',':for_gov',$for_gov);
		$cfg['joins'][]='	LEFT JOIN %qttran% forGov ON forGov.`qtref`=q.`qtref` AND forGov.`part`=:for_gov';
	}

	if ($for_needcommu && $for_needcommu!=-1) {
		mydb::where('(forNeedCommune.`part` IS NOT NULL AND forNeedCommune.`part`=:for_needcommu)',':for_needcommu',$for_needcommu);
		$cfg['joins'][]='	LEFT JOIN %qttran% forNeedCommune ON forNeedCommune.`qtref`=q.`qtref` AND forNeedCommune.`part`=:for_needcommu';
	}

	if ($for_sex && $for_sex!=-1) {
		mydb::where('p.`sex`=:for_sex',':for_sex',$for_sex);
	}

	if ($for_occupa && $for_occupa!=-1) {
		mydb::where('(forOccupa.`part` IS NOT NULL AND forOccupa.`part`="PSNL.OCCUPA" AND forOccupa.`value`=:forOccupa)',':forOccupa',$for_occupa);
		$cfg['joins'][]='	LEFT JOIN %qttran% forOccupa ON forOccupa.`qtref`=q.`qtref` AND forOccupa.`part`="PSNL.OCCUPA" AND forOccupa.`value`=:forOccupa';
	}

	if ($for_married && $for_married!=-1) {
		mydb::where('(forMarried.`part` IS NOT NULL AND forMarried.`part`="PSNL.MARRIED" AND forMarried.`value`=:for_married)',':for_married',$for_married);
		$cfg['joins'][]='	LEFT JOIN %qttran% forMarried ON forMarried.`qtref`=q.`qtref` AND forMarried.`part`="PSNL.MARRIED" AND forMarried.`value`=:for_married';
	}

	if ($for_house && $for_house!=-1) {
		mydb::where('(forHouse.`part` IS NOT NULL AND forHouse.`part`="PSNL.HOME.STATUS" AND forHouse.`value`=:for_house)',':for_house',$for_house);
		$cfg['joins'][]='	LEFT JOIN %qttran% forHouse ON forHouse.`qtref`=q.`qtref` AND forHouse.`part`="PSNL.HOME.STATUS" AND forHouse.`value`=:for_house';
	}

	if ($for_health && $for_health!=-1) {
		mydb::where('(forHealth.`part` IS NOT NULL AND forHealth.`part`=:for_health)',':for_health',$for_health);
		$cfg['joins'][]='	LEFT JOIN %qttran% forHealth ON forHealth.`qtref`=q.`qtref` AND forHealth.`part`=:for_health';
	}

	//$ret.=print_o($fldList,'$fldList');

	switch ($reportType) {
		case 'year':
				$cfg['thead'] = array('ปี พ.ศ.','จำนวน(คน)');
				$cfg['label'] = 'CONCAT("พ.ศ.",YEAR(q.`qtdate`) + 543)';
			break;

		case 'commune':
				$cfg['thead']=array('ชุมชน','จำนวน(คน)');
				$cfg['label']='p.`commune`';
			break;

		case 'defect' :
			$cfg['caption']='จำนวนคนคนยากลำบากแต่ละประเภท';
			$cfg['thead']=array('ประเภทคนยากลำบาก','จำนวน(คน)');
			$cfg['label']='CASE qt.`part`
				WHEN "POOR.TYPE.LIST.1" THEN "'.$fldList['POOR.TYPE.LIST.1'].'"
				WHEN "POOR.TYPE.LIST.2" THEN "'.$fldList['POOR.TYPE.LIST.2'].'"
				WHEN "POOR.TYPE.LIST.3" THEN "'.$fldList['POOR.TYPE.LIST.3'].'"
				WHEN "POOR.TYPE.LIST.4" THEN "'.$fldList['POOR.TYPE.LIST.4'].'"
				WHEN "POOR.TYPE.LIST.5" THEN "'.$fldList['POOR.TYPE.LIST.5'].'"
				WHEN "POOR.TYPE.LIST.6" THEN "'.$fldList['POOR.TYPE.LIST.6'].'"
				WHEN "POOR.TYPE.LIST.7" THEN "'.$fldList['POOR.TYPE.LIST.7'].'"
				WHEN "POOR.TYPE.LIST.8" THEN "'.$fldList['POOR.TYPE.LIST.8'].'"
				WHEN "POOR.TYPE.LIST.9" THEN "'.$fldList['POOR.TYPE.LIST.9'].'"
				WHEN "POOR.TYPE.LIST.10" THEN "'.$fldList['POOR.TYPE.LIST.10'].'"
				WHEN "POOR.TYPE.LIST.11" THEN "'.$fldList['POOR.TYPE.LIST.11'].'"
			END';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.`part` LIKE "POOR.TYPE.LIST.%" AND qt.`value` > 0';
			break;

		case 'cause' :
			$cfg['caption']='สาเหตุความยากลำบาก	';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE qt.`part`
				WHEN "POOR.CAUSE.LIST.1" THEN "'.$fldList['POOR.CAUSE.LIST.1'].'"
				WHEN "POOR.CAUSE.LIST.2" THEN "'.$fldList['POOR.CAUSE.LIST.2'].'"
				WHEN "POOR.CAUSE.LIST.3" THEN "'.$fldList['POOR.CAUSE.LIST.3'].'"
				WHEN "POOR.CAUSE.LIST.4" THEN "'.$fldList['POOR.CAUSE.LIST.4'].'"
				WHEN "POOR.CAUSE.LIST.5" THEN "'.$fldList['POOR.CAUSE.LIST.5'].'"
				WHEN "POOR.CAUSE.LIST.6" THEN "'.$fldList['POOR.CAUSE.LIST.6'].'"
				WHEN "POOR.CAUSE.LIST.7" THEN "'.$fldList['POOR.CAUSE.LIST.7'].'"
				WHEN "POOR.CAUSE.LIST.8" THEN "'.$fldList['POOR.CAUSE.LIST.8'].'"
				WHEN "POOR.CAUSE.LIST.9" THEN "'.$fldList['POOR.CAUSE.LIST.9'].'"
				WHEN "POOR.CAUSE.LIST.10" THEN "'.$fldList['POOR.CAUSE.LIST.10'].'"
				WHEN "POOR.CAUSE.LIST.11" THEN "'.$fldList['POOR.CAUSE.LIST.11'].'"
				WHEN "POOR.CAUSE.LIST.12" THEN "'.$fldList['POOR.CAUSE.LIST.12'].'"
				WHEN "POOR.CAUSE.LIST.13" THEN "'.$fldList['POOR.CAUSE.LIST.13'].'"
				WHEN "POOR.CAUSE.LIST.99" THEN "'.$fldList['POOR.CAUSE.LIST.99'].'"
			END';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.`part` LIKE "POOR.CAUSE.LIST.%" AND qt.`value` > 0';
			break;

		case 'health' :
			$cfg['caption']='สถานะทางสุขภาพ		';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE qt.`part`
				WHEN "POOR.HEALTH.LIST.1" THEN "'.$fldList['POOR.HEALTH.LIST.1'].'"
				WHEN "POOR.HEALTH.LIST.2" THEN "'.$fldList['POOR.HEALTH.LIST.2'].'"
				WHEN "POOR.HEALTH.LIST.3" THEN "'.$fldList['POOR.HEALTH.LIST.3'].'"
				WHEN "POOR.HEALTH.LIST.4" THEN "'.$fldList['POOR.HEALTH.LIST.4'].'"
				WHEN "POOR.HEALTH.LIST.5" THEN "'.$fldList['POOR.HEALTH.LIST.5'].'"
			END';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.`part` LIKE "POOR.HEALTH.LIST.%" AND qt.`value` > 0';
			break;

		case 'govhelp' :
			$cfg['caption']='สิ่งที่ต้องการให้รัฐช่วยเหลือ	';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE qt.`part`
				WHEN "POOR.NEED.GOV.LIST.1" THEN "'.$fldList['POOR.NEED.GOV.LIST.1'].'"
				WHEN "POOR.NEED.GOV.LIST.2" THEN "'.$fldList['POOR.NEED.GOV.LIST.2'].'"
				WHEN "POOR.NEED.GOV.LIST.3" THEN "'.$fldList['POOR.NEED.GOV.LIST.3'].'"
				WHEN "POOR.NEED.GOV.LIST.4" THEN "'.$fldList['POOR.NEED.GOV.LIST.4'].'"
				WHEN "POOR.NEED.GOV.LIST.5" THEN "'.$fldList['POOR.NEED.GOV.LIST.5'].'"
				WHEN "POOR.NEED.GOV.LIST.6" THEN "'.$fldList['POOR.NEED.GOV.LIST.6'].'"
				WHEN "POOR.NEED.GOV.LIST.7" THEN "'.$fldList['POOR.NEED.GOV.LIST.7'].'"
				WHEN "POOR.NEED.GOV.LIST.8" THEN "'.$fldList['POOR.NEED.GOV.LIST.8'].'"
				WHEN "POOR.NEED.GOV.LIST.9" THEN "'.$fldList['POOR.NEED.GOV.LIST.9'].'"
				WHEN "POOR.NEED.GOV.LIST.99" THEN "'.$fldList['POOR.NEED.GOV.LIST.99'].'"
			END';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.`part` LIKE "POOR.NEED.GOV.LIST.%" AND qt.`value` > 0';
			break;

		case 'communehelp' :
			$cfg['caption']='สิ่งที่ต้องการให้ชุ่มชนช่วยเหลือ	';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE qt.`part`
				WHEN "POOR.NEED.COMMUNITY.LIST.1" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.1'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.2" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.2'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.3" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.3'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.4" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.4'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.5" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.5'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.6" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.6'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.7" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.7'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.8" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.8'].'"
				WHEN "POOR.NEED.COMMUNITY.LIST.99" THEN "'.$fldList['POOR.NEED.COMMUNITY.LIST.99'].'"
			END';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.`part` LIKE "POOR.NEED.COMMUNITY.LIST.%" AND qt.`value` > 0';
			break;

		case 'age' :
			$cfg['caption']='จำนวนคนยากลำบากแต่ละช่วงอายุ';
			$cfg['thead']=array('ช่วงอายุ','จำนวนคน');
			$cfg['label']='birth';

			$stmt='SELECT
				CASE
					WHEN age < 5 THEN " 0 - 5 ปี"
					WHEN age BETWEEN 6 and 12 THEN " 6 - 12 ปี"
					WHEN age BETWEEN 13 and 25 THEN "13 - 25 ปี"
					WHEN age BETWEEN 26 and 59 THEN "26 - 59 ปี"
					WHEN age >= 60 THEN "60 ปีขึ้นไป"
					WHEN age IS NULL THEN NULL
				END as `label`,
				COUNT(*) AS `value`
				FROM (
					SELECT TIMESTAMPDIFF(YEAR, d.`birth`, CURDATE()) AS age FROM %db_person% d
						'.implode(_NL,$cfg['joins']).'
						%WHERE%
					) as derived
				GROUP BY `label`
				ORDER BY '.$orderBy;

			break;

		case 'edu' :
			$cfg['caption']='ระดับการศึกษา';
			$cfg['thead']=array('ระดับการศึกษา','จำนวน(คน)');
			$cfg['label']='qt.value';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.part LIKE "PSNL.EDUCA"';
			$labelChoice=$fldList['PSNL.EDUCA.CHOICE'];
			break;

		case 'sex' :
			$cfg['caption']='เพศ';
			$cfg['thead']=array('เพศ','จำนวน(คน)');
			$cfg['label']='p.sex';
			break;

		case 'marry' :
			$cfg['caption']='สถานภาพสมรส';
			$cfg['thead']=array('สถานภาพสมรส','จำนวน(คน)');
			//$cfg['label']='com.cat_name';
			//$cfg['joins'][]='	LEFT JOIN %co_category% com ON com.cat_id=p.mstatus';
			$cfg['label']='qt.value';
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.part LIKE "PSNL.MARRIED"';
			break;

		case 'occu' :
			$cfg['caption']='อาชีพ';
			$cfg['thead']=array('อาชีพ','จำนวน(คน)');
			//$cfg['label']='coe.occu_desc';
			//$cfg['joins'][]='	LEFT JOIN %co_occu% coe ON coe.occu_code=p.occupa';
			$cfg['label']='qt.value';
			$labelChoice=$fldList['PSNL.OCCUPA.CHOICE'];
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.part LIKE "PSNL.OCCUPA"';
			break;

		case 'home' :
			$cfg['caption']='สภาพบ้าน';
			$cfg['thead']=array('สภาพบ้าน','จำนวน(คน)');
			$cfg['label']='qt.value';
			$labelChoice=$fldList['PSNL.HOME.STATUS.CHOICE'];
			$cfg['joins'][]='	LEFT JOIN %qttran% qt ON qt.`qtref`=q.`qtref` AND qt.part LIKE "PSNL.HOME.STATUS"';
			break;

		default :
			$cfg['caption']='จำนวนคนยากลำบากในพื้นที่';
			if ($tambon) {
				$cfg['thead']=array('หมู่บ้าน','จำนวน(คน)');
				$cfg['label']='CONCAT("หมู่ ",dv.`villno`," - ",dv.`villname`)';
				$cfg['joins'][]='	LEFT JOIN %co_village% dv ON dv.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))';
			} else if ($ampur) {
				$cfg['thead']=array('ตำบล','จำนวน(คน)');
				$cfg['label']='dd.subdistname';
				$cfg['joins'][]='	LEFT JOIN %co_subdistrict% dd ON dd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)';
			} else if ($prov) {
				$cfg['thead']=array('อำเภอ','จำนวน(คน)');
				$cfg['label']='cod.distname';
				$cfg['joins'][]='	LEFT JOIN %co_district% cod ON cod.distid=CONCAT(p.changwat,p.ampur)';
			} else {
				$cfg['thead']=array('จังหวัด','จำนวน(คน)');
				$cfg['label']='cop.provname';
				$cfg['joins'][]='	LEFT JOIN %co_province% cop ON cop.provid=p.changwat';
			}
			break;
	}

	mydb::value('$LABEL',$cfg['label'],false);

	if ($graphType=='table') {
		if ($tambon || $village) {
			mydb::value('$GROUPBY','CONCAT(p.`changwat`, p.`ampur`, p.`tambon`, p.`village`),`label`');
			$fields[]='covt.`villname` `areaName`';
		} else if ($ampur) {
			mydb::value('$GROUPBY','CONCAT(p.`changwat`, p.`ampur`, p.`tambon`),`label`');
			$fields[]='cost.`subdistname` `areaName`';
		} else if ($prov) {
			mydb::value('$GROUPBY','CONCAT(p.`changwat`, p.`ampur`),`label`');
			$fields[]='codt.`distname` `areaName`';
		} else {
			mydb::value('$GROUPBY','p.`changwat`,`label`');
			$fields[]='copt.`provname` `areaName`';
		}
		mydb::value('$ORDER','CONVERT(`areaName` USING tis620) ASC, CONVERT(`label` USING tis620) ASC');
	} else {
		mydb::value('$GROUPBY','`label`');
		mydb::value('$ORDER',$listOrderBy?'CONVERT(`label` USING tis620) ASC':'`value` DESC');
	}

	if (!$stmt) {
		$stmt='SELECT
			  $LABEL `label`
			'.($fields?', '.implode(',',$fields):'').'
			, COUNT(*) `value`
			FROM '.$cfg['from'].'
				'.implode(_NL,$cfg['joins']).'
			%WHERE%
			GROUP BY $GROUPBY
			ORDER BY $ORDER;
			-- {reset:false}';
	}
	$dbs=mydb::select($stmt);

	if ($debug) $ret.='<br clear="all" /><p><pre>'.preg_replace('/\t/','&nbsp;',mydb()->_query).'</pre></p>';

	//$ret.='Order='.$listOrderBy.'<br />'.print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');
	

	if ($graphType=='table') {
		$tables = new Table();
		$tables->addClass('-center');
		$tables->thead['label']='พื้นที่';
		$tables->tfoot[1]['label']='รวม';
		foreach ($dbs->items as $rs) {
			$tables->thead[$rs->label]=$rs->label?($labelChoice?$labelChoice[$rs->label]:$rs->label):'ไม่ระบุ';
			$tables->tfoot[1][$rs->label]='-';
		}
		$tables->thead['totalrow']='รวม';
		foreach ($dbs->items as $rs) {
			if (empty($tables->rows[$rs->areaName])) {
				//$tables->rows[$rs->areaName]['label']=$rs->areaName;
				foreach ($tables->thead as $key=>$item) {
					$tables->rows[$rs->areaName][$key]='-';
				}
			}
			$tables->rows[$rs->areaName]['label']=$rs->areaName?$rs->areaName:'ไม่ระบุ';
			$tables->rows[$rs->areaName][$rs->label]=$rs->value;
			$tables->rows[$rs->areaName]['totalrow']+=$rs->value;
			$tables->tfoot[1][$rs->label]+=$rs->value;
			$tables->tfoot[1]['totalrow']+=$rs->value;
		}
		$ret.='<div style="width:100%;overflow-x:scroll;">'.$tables->build().'</div>';
		//$ret.=print_o($tables,'$tables');
		//$ret.=print_o($dbs,'$dbs');
	} else {
		$data->title=$cfg['caption'];
		$ghead[]='พื้นที่';
		$data->items[]=$ghead;

		$tables = new Table();
		$tables->addClass('report-summary');
		$tables->caption=$cfg['caption'];
		$tables->thead=array('ค่า', $cfg['thead'][1],'%');
		$pie->items[]=array('รายการ','จำนวน');

		foreach ($dbs->items as $rs) $total+=$rs->value;
		//$ret.=print_o($labelChoice,'$labelChoice');
		foreach ($dbs->items as $rs) {
			unset($row);
			if ($labelChoice) {
				if (array_key_exists($rs->label, $labelChoice)) {
					$label=$labelChoice[$rs->label].($labelChoice[$rs->label]!=$rs->label?' ['.$rs->label.']':'');
				} else {
					$label=SG\getFirst($rs->label,'ไม่ระบุ');
				}
			} else {
				$label=SG\getFirst($rs->label,'ไม่ระบุ');
			}
			if ($label=='') $label='ไม่ระบุ';
			if ($label != 'ไม่ระบุ') $pie->items[]=array($label,intval($rs->value));
			$tables->rows[]=array($label,number_format($rs->value),number_format(100*$rs->value/$total,2).'%');
		}
		$tables->tfoot[]=array('รวมทั้งสิ้น',number_format($total),$total?'100%':'-');
		$ret.=$tables->build();

		//$ret.='<br clear="all" /><p><strong>หมายเหตุ</strong><ul><li>แหล่งที่มาของข้อมูลเบื้องต้นจากฐานข้อมูล พมจ.สงขลา เมื่อปี พ.ศ. 2553</li><li>ขณะนี้กำลังอยู่ในระหว่างการเก็บรวมรวมข้อมูลเพื่อปรับปรุงให้มีความสมบูรณ์และทันสมัย โดยในปี 2555-2556 จะดำเนินการเก็บรวบรวมข้อมูลคนยากลำบากของ 2 อำเภอในจังหวัดสงขลาคือ <strong>อำเภอนาหม่อม และ อำเภอนาทวี</strong></li><li>กรุณาอย่าเพิ่งนำข้อมูลในรายงานนี้ไปอ้างอิงจนกว่ากระบวนการเก็บรวมรวมข้อมูลเสร็จสมบูรณ์</ul></p>';
	}



	unset($stmt);



	// Show person detail
	if ($detail) {
		if (empty($listOrderBy)) $listOrderBy='CONVERT(`name` USING tis620)';
		if ($listOrderBy && in_array($listOrderBy,array('name','label'))) $listOrderBy='CONVERT (`'.$listOrderBy.'` USING tis620)';

		if ($isAdmin) {

		} else if ($zones) {
			mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
		} else {
			mydb::where('p.`uid`=:uid',':uid',i()->uid);
		}

		$stmt='SELECT
			p.`psnid` `pid`
			, p.`name`
			, CONCAT(IFNULL(`prename`,"")," ",`name`," ",`lname`) `fullname`
			, q.`qtdate`
			, p.`house`, p.`village`, p.`commune`
			, cosd.`subdistname`
			, copv.`provname`
			, codist.`distname`
			, $LABEL `label`
			, GROUP_CONCAT($LABEL SEPARATOR " , ") `allLabel`
		FROM '.$cfg['from'].'
			'.implode(_NL,$cfg['joins']).'
			LEFT JOIN %co_province% copv ON copv.provid=p.changwat
			LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
			LEFT JOIN %co_subdistrict% cosd ON cosd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
		%WHERE%
		GROUP BY `psnid`
		ORDER BY '.$listOrderBy.' ASC';

		$nameDbs=mydb::select($stmt);
		//$ret.=$nameDbs->_query;
		
		if ($debug) $ret.='<br clear="all" /><p><pre>'.preg_replace('/\t/','&nbsp;',mydb()->_query).'</pre></p>';

		if ($labelChoice) {
			foreach ($nameDbs->items as $key => $rs) {
				$nameDbs->items[$key]->allLabel=$labelChoice[$rs->allLabel];
			}
		}
		//$ret.=print_o($nameDbs);

		$ret.=R::View(
			'imed.report.name.list',
			$nameDbs,
			'รายชื่อคนยากลำบาก',
			array('prov'=>$prov,'ampur'=>$ampur,'tambon'=>$tambon,'village'=>$village,'show'=>'yes'),
			'no,fullname,address,allLabel,qtdate',
			$cfg['thead'][0]
		);
	}

	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	$changeAxis=0;


	if ($graphType != 'table') {
		$ret .= '<script type="text/javascript">
		$.getScript("https://www.google.com/jsapi", function(data, textStatus, jqxhr) {
			google.load("visualization", "1", {packages:["corechart"], callback: drawChart});

			//google.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = google.visualization.arrayToDataTable('.json_encode($pie->items).');
				var options = {
					title: "'.$data->title.'",
					hAxis: {title: "'.$cfg['thead'][0].'", titleTextStyle: {color: "black"}},
					vAxis: {title: "'.$cfg['thead'][1].'", minValue: 0},
					isStacked: '.(post('stack') ? 'true' : 'false').'
				};
				var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
				chart.draw(data, options);
			}
		});
		</script>';
	}

	$ret.='</div><!--report-output-->';



	if ($isFirstRequest) {
		head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');



		$ret.='<script>
			var allProvince = $.map($("#prov option") ,function(option) {
				return {"id": option.value, "name" : option.text};
			});
			//console.log(allProvince)

			$("#prov,#ampur").change(function() {
				var para="prov="+$("#prov").val()+"&ampur="+$("#ampur").val()
				$(".commune-name").attr("href",$(".commune-name").data("src")+"?"+para).text("เลือกชุมชน")
				$("input[name=\'commune\']").val("")
			})
		</script>';

		$ret.='
			<style type="text/css">
			table.report-summary {width:100%;}
			#chart_div {width:100%;height:600px;float:left; background: transparent;}
			table.report-summary {width:100%;float:right;}
			table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
			table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
			.totalrow {font-weight:bold;}
			</style>
			';
	}


	if (debug('yes')) $ret.='<br clear="all" /><div style="height:400px;overflow:auto;border:1px #ccc solid;">'.print_o($dbs,'$dbs').print_o($nameDbs,'$nameDbs').mydb()->_query.'</div>';
	return $ret;
}
?>