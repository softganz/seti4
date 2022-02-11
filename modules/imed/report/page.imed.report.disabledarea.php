<?php
/**
 * Disabled report by area
 * @param $_GET
 * @return String
 */
function imed_report_disabledarea($self) {
	$title='รายงานคนพิการ';
	//$prov=SG\getFirst($_REQUEST['p'],'90');
	$prov = post('p');
	$prov = SG\getFirst(post('p'),'90');
	$ampur = post('a');
	$tambon = post('t');
	$village = post('v');
	$commune = post('commune');
	$defect = post('d');
	$educate = post('e');
	$reportType = SG\getFirst(post('r'),'amt');
	$graphType = strtolower(SG\getFirst(post('g'),'pie'));
	$detail = post('detail');
	$order = post('o');

	$orderBy='`value` DESC';
	//$orderBy='ORDER BY `label` IS NULL, `label` ASC';

	$isAdmin=user_access('administer imeds');

	cfg('db.disabled.title',$title);

	$areaList = cfg('imedAreaList');

	$orderArr=array(
		'na'=>'ชื่อ:name',
		'rd'=>'วันที่จดทะเบียน:d.regdate',
		'cd'=>'วันที่ป้อน:d.created',
		'tb'=>'ตำบล:p.tambon',
		'vi'=>'หมู่บ้าน:p.village+0',
		'age'=>'อายุ:p.birth',
		'label'=>'ป้ายรายงาน:label'
	);
	list(,$listOrderBy)=explode(':',$orderArr[$order]);

	if (post('r')=='') {
		$ret.='<form class="report-form sg-form" id="report-form" data-rel="#report-output" method="get" action="'.url(q()).'"><input type="hidden" name="r" id="reporttype" value="'.$reportType.'" /><input type="hidden" name="g" id="graphtype" value="'.$graphType.'" />';
		$ret.='<h3>'.$title.'</h3>'._NL;
		/*
		if ($areaList) {
			$ret.='<div class="form-item">'._NL;
			$ret .= '<select id="area" class="form-select" name="ar"><option value="">-ทุกเขต-</option>';
			foreach ($areaList as $k=>$v) $ret.='<option value="'.$k.'"'.($k==$getArea?' selected="selected"':'').' data-prov="'.$v['prov'].'">'.$v['name'].'</option>'._NL;
			$ret .= '</select>';
			$ret .= '</div>';
		}
		*/

		$ret.='<div class="form-item">'._NL;
		$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').' data-prov="'.$v['prov'].'">'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		//if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
			$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		//}
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>'._NL;

		$selectDefect='<select name="d"><option value="-1">---ทุกประเภท---</option>';
		foreach (mydb::select('SELECT defect+0 defectID,defect FROM %imed_disabled_defect% df GROUP BY defect ORDER BY defect')->items as $item) {
			if (empty($item->defectID)) continue;
			$selectDefect .= '<option value="'.$item->defectID.'"'.($item->defectID==$defect?' selected="selected"':'').'>'.SG\getFirst($item->defect,'ไม่ระบุ').'</option>';
		}
		$selectDefect.='</select>';

		$selectEdu.='<select name="e"><option value="-1">---ทุกระดับ---</option>';
		foreach (mydb::select('SELECT edu_code, edu_desc FROM %co_educate%')->items as $item) {
			$selectEdu.='<option value="'.$item->edu_code.'"'.($item->edu_code==$educate?' selected="selected"':'').'>'.$item->edu_desc.'</option>';
		}
		$selectEdu.='</select>';

		include_once 'modules/imed/assets/qt.individual.php';

		$selectDfDetail.='<select name="dfdetail"><option value="">--เลือก</option>'._NL;
		foreach ($qt as $key=>$item) {
			if (empty($item['label']) || $item['group']!='qt') continue;
			$key=trim($key);
			$dfdetail=post('dfdetail');
			$selectDfDetail.='<option value="'.$key.'"'.($dfdetail==$key?' selected="selected"':'').'>'.trim($item['label']).'</option>'._NL;
		}
		$selectDfDetail.='</select>'._NL;
		$reportTypeArray=array(
			'amt'=>array('text'=>'จำนวน'),
			'commune'=>array('text'=>'ชุมชน','select'=>'<span><a class="sg-action commune-name" href="'.url('imed/report/selectcommune').'" data-rel="box" data-width="400">เลือกชุมชน</a></span><input type="hidden" name="commune" value="" />'),
			'defect'=>array('text'=>'ประเภท','select'=>$selectDefect),
			'kind'=>array('text'=>'ลักษณะ'),
			'sex'=>array('text'=>'เพศ'),
			'age'=>array('text'=>'อายุ'),
			'marry'=>array('text'=>'สมรส'),
			'edu'=>array('text'=>'การศึกษา','select'=>$selectEdu),
			'occu'=>array('text'=>'อาชีพ'),
			'level'=>array('text'=>'ระดับความพิการ'),
			'cause'=>array('text'=>'สาเหตุ'),
			'reg'=>array('text'=>'จดทะเบียน'),
			'allowance'=>array('text'=>'การช่วยเหลือ'),
			'claim'=>array('text'=>'สิทธิ์'),
			'job'=>array('text'=>'การฝึกอาชีพ'),
			'home1'=>array('text'=>'สถานะบ้าน'),
			'home'=>array('text'=>'สภาพบ้าน'),
			'disease'=>array('text'=>'โรคประจำตัว'),
			'chronic'=>array('text'=>'โรคเรื้อรัง'),
			'dfdetail'=>array('text'=>'รายละเอียด','select'=>$selectDfDetail),
		);

		$ret.='<script>
			$("#prov,#ampur").change(function() {
				var para="prov="+$("#prov").val()+"&ampur="+$("#ampur").val()
				$(".commune-name").attr("href",$(".commune-name").data("src")+"?"+para).text("เลือกชุมชน")
				$("input[name=\'commune\']").val("")
			})
		</script>';
		$ret.='<a href="javascript:void(0)" class="left"><i class="icon -back"></i></a><div class="toolbar">'._NL.'<ul>';
		foreach ($reportTypeArray as $k=>$v) {
			$ret.='<li'.($k==$reportType?' class="active"':'').'><a href="#'.$k.'">'.$v['text'].'</a>';
			if (isset($v['select'])) $ret.=$v['select'];
			$ret.='</li>'._NL;
		}
		$ret.='</ul></div><a href="javascript:void(0)" class="right"><i class="icon -forward"></i></a>'._NL;

		$ret.='<div class="optionbar"><ul>';
		$ret.='<li><input type="submit" name="g" value="Pie" class="btn -graph'.($graphType=='pie'?'  active':'').'" /> <input type="submit" name="g" value="Bar" class="btn -graph'.($graphType=='bar'?' active':'').'" /> <input type="submit" name="g" value="Col" class="btn -graph'.($graphType=='col'?' active':'').'" /> <input type="submit" name="g" value="Line" class="btn -graph'.($graphType=='line'?' active':'').'" /></li>';
		if (i()->ok) {
			$ret.='<li><input type="checkbox" name="detail" value="yes"'.($detail=='yes'?' checked="checked"':'').'/> แสดงรายชื่อ ';
		}
		$ret.='<select class="form-select" name="o"><option>--เรียงตาม--</option>';
		foreach ($orderArr as $k=>$v) $ret.='<option value="'.$k.'"'.($order==$k?' selected="selected"':'').'>'.substr($v,0,strpos($v,':')).'</option>';
		$ret.='</select></li>';
		if (user_access('access debugging program')) $ret.='<li><input type="checkbox" name="debug" value="yes"'.($_REQUEST['debug']?' checked="checked"':'').' /> Debug</li>';

		$ret.='</ul></div>';
		$ret.='</form>';
	}

	$ret.='<div id="report-output">';
	$ret.='<div id="chart_div" style=""></div>';

	unset($stmt);
//		$cfg['from']='%imed_disabled% d';
//		$cfg['joins'][]='LEFT JOIN %db_person% p ON p.psnid=d.pid';

	$cfg['from']='%imed_disabled% d';
	$cfg['joins'][]='RIGHT JOIN %db_person% p ON p.psnid=d.pid';

	$where=array();
	$where=sg::add_condition($where,'d.pid IS NOT NULL AND d.discharge IS NULL');
	if ($commune) $where=sg::add_condition($where,'p.`commune` IN (:commune)','commune','SET-STRING:'.$commune);
	if ($prov) $where=sg::add_condition($where,'p.`changwat` = :prov','prov',$prov);
	if ($ampur) $where=sg::add_condition($where,'p.`ampur` = :ampur','ampur',$ampur);
	if ($tambon) $where=sg::add_condition($where,'p.`tambon` = :tambon','tambon',$tambon);
	if ($village) $where=sg::add_condition($where,'LPAD(p.`village`,2,"0") = :village','village',$village);
	if ($defect>0) {
		$where=sg::add_condition($where,'ddf.`defect`+0=:defect','defect',$defect);
		$cfg['joins'][]='LEFT JOIN %imed_disabled_defect% ddf ON ddf.pid=d.pid';
	}
	if ($educate && $educate!=-1) $where=sg::add_condition($where,'p.`educate`=:educate','educate',$educate);

	switch ($reportType) {
		case 'defect' :
			$cfg['caption']='จำนวนคนพิการแต่ละประเภท';
			$cfg['thead']=array('ประเภทความพิการ','จำนวน(คน)');
			$cfg['label']='df.defect';
			$cfg['joins'][]='LEFT JOIN %imed_disabled_defect% df ON df.pid=d.pid';
			break;

		case 'kind' :
			$cfg['caption']='จำนวนคนพิการแต่ละลักษณะความพิการ';
			$cfg['thead']=array('ลักษณะความพิการ','จำนวน(คน)');
			//$cfg['label']='CASE WHEN df.kind IS NULL OR df.kind = "" THEN null ELSE df.kind END';
			//$cfg['joins'][]='LEFT JOIN %imed_disabled_defect% df ON df.pid=d.pid';

			$cfg['label']='IFNULL(SUBSTRING(q.part,10),"ไม่ระบุ")';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND (q.part LIKE "DSBL.SEE.%" )';
			$where=sg::add_condition($where,'(q.`value` IS NULL OR q.`value`!="")');

			break;

		case 'dfdetail' :
			$dfdetail=post('dfdetail');
			include_once 'modules/imed/assets/qt.individual.php';
			$cfg['caption']='จำนวนคนพิการจำแนกตาม '.$qt[$dfdetail]['label'];
			$cfg['thead']=array($detail,'จำนวน(คน)');
			$cfg['label']='qt.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% qt ON qt.pid=d.pid AND qt.`part`="'.$dfdetail.'"';
			$where=sg::add_condition($where,'qt.value <> "" ');
			break;
			/*
		case 'dfdetail' :
			$cfg['caption']='รายละเอียดความพิการ';
			$cfg['thead']=array('รายละเอียดความพิการ','จำนวน(คน)');
			$cfg['label']='CASE WHEN df.detail IS NULL OR df.detail = "" THEN null ELSE df.detail END';
			$cfg['joins'][]='LEFT JOIN %imed_disabled_defect% df ON df.pid=d.pid';
			if ($_REQUEST['dfdetail']) {
				foreach (explode(',',$_REQUEST['dfdetail']) as $dfDetailItem) $dfDetailList[]='df.`detail` LIKE "%'.addslashes($dfDetailItem).'%"';
				$ret.=print_o(dfDetailList,'dfDetailList');
				$where=sg::add_condition($where,'( '.implode(' AND ',$dfDetailList).')');
			}
			break;
			*/

		case 'age' :
			$cfg['caption']='จำนวนคนพิการแต่ละช่วงอายุ';
			$cfg['thead']=array('ช่วงอายุ','จำนวนคน');
			$cfg['label']='birth';

			$stmt = 'SELECT
				CASE
					WHEN age < 20 THEN "1 - 20 ปี"
					WHEN age BETWEEN 20 and 29 THEN "20 - 29 ปี"
					WHEN age BETWEEN 30 and 39 THEN "30 - 39 ปี"
					WHEN age BETWEEN 40 and 49 THEN "40 - 49 ปี"
					WHEN age BETWEEN 50 and 59 THEN "50 - 59 ปี"
					WHEN age BETWEEN 60 and 69 THEN "60 - 69 ปี"
					WHEN age BETWEEN 70 and 79 THEN "70 - 79 ปี"
					WHEN age >= 80 THEN "80 ปีขึ้นไป"
					WHEN age IS NULL THEN NULL
				END as `label`,
				COUNT(*) AS `value`
				FROM (SELECT TIMESTAMPDIFF(YEAR, birth, CURDATE()) AS age FROM %imed_disabled% d
								LEFT JOIN %db_person% p ON p.psnid=d.pid
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').') as derived
				GROUP BY `label`
				ORDER BY '.$orderBy;

			$stmt = 'SELECT
				CASE
					WHEN age < 5 THEN " 0 - 5 ปี"
					WHEN age BETWEEN 6 and 12 THEN " 6 - 12 ปี"
					WHEN age BETWEEN 13 and 25 THEN "13 - 25 ปี"
					WHEN age BETWEEN 26 and 59 THEN "26 - 59 ปี"
					WHEN age >= 60 THEN "60 ปีขึ้นไป"
					WHEN age IS NULL THEN NULL
				END as `label`,
				COUNT(*) AS `value`
				FROM (SELECT TIMESTAMPDIFF(YEAR, birth, CURDATE()) AS age FROM %imed_disabled% d
								'.implode(_NL,$cfg['joins']).'
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').') as derived
				GROUP BY `label`
				ORDER BY '.$orderBy;

			break;

		case 'level' :
			$cfg['caption']='ระดับความพิการ';
			$cfg['thead']=array('ระดับความพิการ','จำนวน(คน)');
			$cfg['label']='dislevel.cat_name';
			$cfg['joins'][]='LEFT JOIN %co_category% dislevel ON dislevel.cat_id=d.disabilities_level';
			break;

		case 'cause' :
			$cfg['caption']='สาเหตุการเกิดพิการ';
			$cfg['thead']=array('สาเหตุ','จำนวน(คน)');
			$cfg['label']='CASE WHEN begetting.cat_name IS NULL OR begetting.cat_name = "" THEN null ELSE begetting.cat_name END';
			$cfg['joins'][]='LEFT JOIN %co_category% begetting ON begetting.cat_id=d.begetting';
			break;

		case 'edu' :
			$cfg['caption']='ระดับการศึกษา';
			$cfg['thead']=array('ระดับการศึกษา','จำนวน(คน)');
			$cfg['label']='coe.edu_desc';
			$cfg['joins'][]='LEFT JOIN %co_educate% coe ON coe.edu_code=p.educate';
			break;

		case 'sex' :
			$cfg['caption']='เพศ';
			$cfg['thead']=array('เพศ','จำนวน(คน)');
			$cfg['label']='p.sex';
			break;

		case 'marry' :
			$cfg['caption']='สถานภาพสมรส';
			$cfg['thead']=array('สถานภาพสมรส','จำนวน(คน)');
			$cfg['label']='com.cat_name';
			$cfg['joins'][]='LEFT JOIN %co_category% com ON com.cat_id=p.mstatus';
			break;

		case 'occu' :
			$cfg['caption']='อาชีพ';
			$cfg['thead']=array('อาชีพ','จำนวน(คน)');
			$cfg['label']='coe.occu_desc';
			$cfg['joins'][]='LEFT JOIN %co_occu% coe ON coe.occu_code=p.occupa';
			break;

		case 'reg' :
			$cfg['caption']='จดทะเบียนคนพิการ';
			$cfg['thead']=array('จดทะเบียนคนพิการ','จำนวน(คน)');
			$cfg['label']='register';
			break;

		case 'allowance' :
			$cfg['caption']='จำนวนคนพิการที่ได้รับบริการ/สวัสดิการ';
			$cfg['thead']=array('บริการ/สวัสดิการ','จำนวน(คน)');
			$cfg['label']='q.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "OTHR.5.1.%"';
			$where=sg::add_condition($where,'LENGTH(q.`part`)=10 AND q.`value` != ""');
			break;

		case 'claim' :
			$cfg['caption']='จำนวนคนพิการการใช้สิทธิ์';
			$cfg['thead']=array('สิทธิ์','จำนวน(คน)');
			$cfg['label']='q.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "PSNL.1.10.1"';
			break;

		case 'job' :
			$cfg['caption']='จำนวนคนพิการกับการฝึกอาชีพ';
			$cfg['thead']=array('การฝึกอาชีพ','จำนวน(คน)');
			$cfg['label']='q.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "ECON.4.1"';
			break;

		case 'home1' :
			$cfg['caption']='สถานะของที่พักอาศัย';
			$cfg['thead']=array('สถานะของที่พักอาศัย','จำนวน(คน)');
			$cfg['label']='q.value';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "OTHR.5.5"';
			break;

		case 'home' :
			$cfg['caption']='สภาพบ้าน';
			$cfg['thead']=array('สภาพบ้าน','จำนวน(คน)');
			$cfg['label']='q.value';
			$dfdetail='PSNL.HOUSECONDITION';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "PSNL.HOUSECONDITION"';
			break;

		case 'disease' :
			$cfg['caption']='โรคประจำตัว';
			$cfg['thead']=array('โรคประจำตัว','จำนวน(คน)');
			$cfg['label']='q.value';
			$dfdetail='HLTH.2.4';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.pid=d.pid AND q.part LIKE "HLTH.2.4"';
			$where=sg::add_condition($where,'(q.`value` IS NULL OR q.`value`!="")');
			break;

		case 'chronic' :
			$cfg['caption']='โรคเรื้อรัง';
			$cfg['thead']=array('โรคเรื้อรัง','จำนวน(คน)');
			$cfg['label']='IFNULL(q.part,"ไม่มีโรคเรื้อรังหรือไม่ระบุ")';
			$cfg['joins'][]='LEFT JOIN %imed_qt% q ON q.`pid`=d.`pid` AND (q.`part` LIKE "โรคประจำตัว-%" OR q.`part` IN ("ภาวะแทรกซ้อน-แผลกดทับ","ภาวะแทรกซ้อน-ข้อติดแข็ง","ภาวะแทรกซ้อน-กล้ามเนื้อเกร็งหรือกระตุก","ภาวะแทรกซ้อน-อื่นๆ","HLTH.2.4.1") )';
			$where=sg::add_condition($where,'(q.`value` IS NULL OR q.`value`!="")');
			break;

		case 'commune':
				$cfg['thead']=array('ชุมชน','จำนวน(คน)');
				$cfg['label']='p.`commune`';
			break;

		default :
			$cfg['caption']='จำนวนคนพิการในพื้นที่';
			if ($tambon) {
				$cfg['thead']=array('หมู่บ้าน','จำนวน(คน)');
				$cfg['label']='CONCAT("หมู่ ",dv.villno," - ",dv.villname)';
				$cfg['joins'][]='LEFT JOIN %co_village% dv ON dv.villid=CONCAT(p.changwat,p.ampur,p.tambon, LPAD(p.village, 2, "0"))';
			} else if ($ampur) {
				$cfg['thead']=array('ตำบล','จำนวน(คน)');
				$cfg['label']='dd.subdistname';
				$cfg['joins'][]='LEFT JOIN %co_subdistrict% dd ON dd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)';
			} else if ($prov) {
				$cfg['thead']=array('อำเภอ','จำนวน(คน)');
				$cfg['label']='cod.distname';
				$cfg['joins'][]='LEFT JOIN %co_district% cod ON cod.distid=CONCAT(p.changwat,p.ampur)';
			}
			break;
	}

	if (!$stmt) {
		$stmt = 'SELECT
			'.($sql_fields?implode(', ',$sql_fields).', ':'').$cfg['label'].' `label`
			, COUNT(*) `value`
			FROM '.$cfg['from'].'
				'.implode(_NL,$cfg['joins']).'
			'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			GROUP BY `label`
			ORDER BY '.($listOrderBy?'CONVERT(`label` USING tis620) ASC':'`value` DESC');
	}

	$dbs = mydb::select($stmt,$where['value']);


	//$ret.='Order='.$listOrderBy.'<br />'.print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');


	$data->title=$cfg['caption'];
	$ghead[]='พื้นที่';
	$data->items[]=$ghead;


	$tables = new Table();
	$tables->addClass('report-summary');
	$tables->caption=$cfg['caption'];
	$tables->thead=array('ค่า', $cfg['thead'][1],'%');
	$pie->items[]=array('รายการ','จำนวน');

	foreach ($dbs->items as $rs) $total+=$rs->value;
	if ($dfdetail) {
		include_once 'modules/imed/assets/qt.individual.php';
		$optionsList=is_string($qt[$dfdetail]['option']) ? explode(',', $qt[$dfdetail]['option']) : $qt[$dfdetail]['option'];
		foreach ($optionsList as $optionKey => $optionValue) {
			if (strpos($optionValue,':')) {
				list($ok,$ov)=explode(':',$optionValue);
				$ok=trim($ok);
				//$ret.=$ok.'='.$ov.'<br />';
				$options[$ok]=trim($ov);
			} else {
				$options[$optionValue]=$optionValue;
			}
		}
	}
	//$ret.=print_o($optionsList,'$optionsList').print_o($qt[$dfdetail],'$qt[$dfdetail]');
	//$ret.=$dfdetail.print_o($options,'$options');
	foreach ($dbs->items as $rs) {
		unset($row);
		if ($dfdetail) {
			if ($qt[$dfdetail]['option']) {
				$label=$options[$rs->label].($options[$rs->label]!=$rs->label?' ['.$rs->label.']':'');
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
	$ret .= $tables->build();

	$ret.='<br clear="all" /><p><strong>หมายเหตุ</strong><ul><li>แหล่งที่มาของข้อมูลเบื้องต้นจากฐานข้อมูล พมจ.สงขลา เมื่อปี พ.ศ. 2553</li><li>ขณะนี้กำลังอยู่ในระหว่างการเก็บรวมรวมข้อมูลเพื่อปรับปรุงให้มีความสมบูรณ์และทันสมัย โดยในปี 2555-2556 จะดำเนินการเก็บรวบรวมข้อมูลคนพิการของ 2 อำเภอในจังหวัดสงขลาคือ <strong>อำเภอนาหม่อม และ อำเภอนาทวี</strong></li><li>กรุณาอย่าเพิ่งนำข้อมูลในรายงานนี้ไปอ้างอิงจนกว่ากระบวนการเก็บรวมรวมข้อมูลเสร็จสมบูรณ์</ul></p>';







	unset($stmt);



	// Show person detail
	if ($detail) {
		if (empty($listOrderBy)) $listOrderBy='name';
		if ($listOrderBy && in_array($listOrderBy,array('name','label'))) $listOrderBy='CONVERT (`'.$listOrderBy.'` USING tis620)';

		$zones=imed_model::get_user_zone(i()->uid,'imed');
		if ($isAdmin) {

		} else if ($zones) {
			$where=sg::add_condition($where,'('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')','uid',i()->uid);
		} else {
			$where=sg::add_condition($where,'p.`uid`=:uid','uid',i()->uid);
		}

		$stmt = 'SELECT p.`psnid` `pid`, d.`regdate`,
				p.`name`, CONCAT(IFNULL(`prename`,"")," ",`name`," ",`lname`) fullname,
				d.`created`,
				p.`house`, p.`village`, p.`commune`, cosd.`subdistname`,
				copv.`provname`, codist.`distname`,
				'.$cfg['label'].' `label`
			FROM '.$cfg['from'].'
				'.implode(_NL,$cfg['joins']).'
				LEFT JOIN %co_province% copv ON copv.provid=p.changwat
				LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
				LEFT JOIN %co_subdistrict% cosd ON cosd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
			'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			ORDER BY '.$listOrderBy.' ASC';
		$nameDbs=mydb::select($stmt,$where['value']);


		$ret .= R::View('imed.report.name.list',$nameDbs,'รายชื่อคนพิการ',array('prov'=>$prov,'ampur'=>$ampur,'tambon'=>$tambon,'village'=>$village,'show'=>'yes'),NULL,$cfg['thead'][0]);
	}

	$chartTypes = array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	$changeAxis = 0;

	$ret .= '</div><!--report-output-->';


	head('js.imed.public.js','<script type="text/javascript" src="imed/js.imed.public.js"></script>');

	$ret .= '<script type="text/javascript">
		var allProvince = $.map($("#prov option") ,function(option) {
			return {"id": option.value, "name" : option.text};
		});

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

	$ret .= '<style type="text/css">
	table.report-summary {width:100%;}
	#chart_div {width:100%;height:400px;float:left; background: transparent;}
	table.report-summary {width:100%;float:right;}
	table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
	table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
	</style>';

	if (debug('yes')) $ret.='<br clear="all" /><div style="height:400px;overflow:auto;border:1px #ccc solid;">'.print_o($dbs,'$dbs').print_o($nameDbs,'$nameDbs').mydb()->_query.'</div>';
	return $ret;
}
?>