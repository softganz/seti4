<?php
function imed_app_poorman_report_summary($self) {
	$prov=post('prov');
	$ampur=post('ampur');
	$tambon=post('tambon');
	$export=post('export');

	ini_set('memory_limit','512M');

	$isAdmin=user_access('administer imeds');
	$zones=imed_model::get_user_zone(i()->uid,'imed.poorman');

	R::View('imed.toolbar',$self,'รายงานสรุปแบบสอบถาม','none');

	//if (!$isAdmin) return message('error','access denied');

	$ret.='<nav class="nav -page">';
	$ret.='<form class="form -report" method="get" action="'.url('imed/app/poorman/report/summary').'">';
	$ret.='<ul>';

	// Select province
	$ret.='<li class="ui-nav">';
	$ret.='<select id="changwat" class="form-select sg-changwat" name="prov"><option value="">==ทุกจังหวัด==</option>';
	mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %qtmast% q LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` %WHERE% GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret.='</select> ';
	$ret.='<select id="ampur" class="form-select sg-ampur'.($prov?'':' -hidden').'" name="ampur"><option value="">== ทุกอำเภอ ==</option>';
	if ($prov) {
		$stmt='SELECT * FROM %co_district% WHERE LEFT(`distid`,2)=:prov';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.substr($rs->distid,2,2).'" '.($ampur==substr($rs->distid,2,2)?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
	}
	$ret.='</select> ';
	$ret.='<select id="tambon" class="form-select sg-tambon'.($ampur?'':' -hidden').'" name="tambon"><option value="">== ทุกตำบล ==</option>';
	if ($ampur) {
		$stmt='SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4)=:ampur';
		$dbs=mydb::select($stmt,':ampur',$prov.$ampur);
		//debugMsg($dbs,'$dbs');
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.substr($rs->subdistid,4,2).'" '.($tambon==substr($rs->subdistid,4,2)?'selected="selected"':'').'>'.$rs->subdistname.'</option>';
		}
	}
	$ret.='</select>';


	$ret.='&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	if ($ampur && i()->ok) $ret.='&nbsp;&nbsp;<button type="submit" class="btn" name="export" value="excel"><i class="icon -download"></i><span>Export</span></button>';
	$ret.='</li>';
	//$ret.='<li>';
	//$ret.='<select class="form-select" name="sex"><option value="" />ทุกเพศ</option><option value="1">ชาย</option><option value="2">หญิง</option></option></select>';
	//$ret.='</li>';
	$ret.='</ul></form>';
	$ret.='</nav>';


$fldList = R::Model('imed.poorman.field');


/*
ลำดับ	แบบสำรวจลำดับที่	คำนำหน้า	ชื่อ	นามสกุล	เลขที่บัตรประชาชน	กรณีไม่มีบัตรเนื่องจาก	วันเดือนปีเกิด	เพศ	เชื้อชาติ	สัญชาติ	ศาสนา	สถานภาพสมรส

ลำดับ	แบบสำรวจลำดับที่	ชื่อสถานที่	รหัสประจำบ้าน	บ้านเลขที่	หมู่ที่	ตรอก	ซอย	ถนน	ตำบล	อำเภอ	จังหวัด	ที่อยู่ปัจจุบัน	ชื่อสถานที่	รหัสประจำบ้าน	บ้านเลขที่	หมู่ที่	ตรอก	ซอย	ถนน	ตำบล	อำเภอ	จังหวัด	โทรศัพท์ 1	โทรศัพท์2

ลำดับ	แบบสำรวจลำดับที่	ระดับการศึกษา	อาชีพ	ระบุรายละเอียด	รายได้เฉลี่ยต่อเดือน	ที่มาของรายได้	ผู้ให้ข้อมูล	ระบุชื่อผู้ให้ข้อมูลแทน

ลำดับ	แบบสำรวจลำดับที่	สภาวะความยากลำบาก	1	2	3	4	5	6	7	8	9	10	11	12	13	14	ระบุสาเหตุความยากลำบาก

ลำดับ	แบบสำรวจลำดับที่	สถานะทางสุขภาพ	ระบุรายละเอียดสุขภาพ	สิ่งที่ต้องการให้รัฐช่วยเหลือ	ระบุรายละเอียดความช่วยเหลืออื่น ๆ ที่ต้องการ	เคยได้รับความช่วยเหลือจากหน่วยงานใดบ้าง	รายละเอียดความช่วยเหลือจากหน่วยงาน
ลำดับ	แบบสำรวจลำดับที่	สิ่งที่ต้องการให้ชุมชนช่วยเหลือ	สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน	ประวัติเพิ่มเติม	ประวัติครอบครัว	ผู้สำรวจ	วันที่สำรวจ
*/

	mydb::where('q.`qtgroup` = 4 AND q.`qtstatus` >= 0');

	$tables = new Table();
	$tables->thead=array('หัวข้อแบบสำรวจ','amt'=>'จำนวน(คน)');

	if (empty($prov)) {
		// Count
		$stmt='SELECT
			  p.`changwat`, cop.`provname`
			, COUNT(*) `amt`
			FROM %qtmast% q
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
			%WHERE%
			GROUP BY `changwat`
			HAVING `provname` IS NOT NULL;
		-- {sum:"amt",reset:false}';
		$dbs=mydb::select($stmt);

		$tables->rows[]=array('จังหวัด','','config'=>array('class'=>'subheader'));
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array('<a href="'.url('imed/app/poorman/report/summary',array('prov'=>$rs->changwat)).'">'.$rs->provname.'</a>',number_format($rs->amt));
		}
		$tables->rows[]=array('รวม',number_format($dbs->sum->amt),'config'=>array('class'=>'subfooter'));
		//$ret.=print_o($dbs,'$dbs');
	}

	if ($prov) mydb::where('p.`changwat`=:changwat',':changwat',$prov);

	if ($prov && !$ampur) {
		// Count
		$stmt='SELECT *
			FROM %co_district% cod
			LEFT JOIN
				(SELECT
				  CONCAT(p.`changwat`,p.`ampur`) `distid`
				, COUNT(*) `amt`
				FROM %qtmast% q
					LEFT JOIN %db_person% p USING(`psnid`)
				%WHERE%
				GROUP BY `ampur`) a USING(`distid`)
			WHERE LEFT(`distid`,2) = :changwat;
			-- {sum:"amt",reset:false}';
		$dbs=mydb::select($stmt);

		$tables->rows[]=array('อำเภอ','','config'=>array('class'=>'subheader'));
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array('<a href="'.url('imed/app/poorman/report/summary',array('prov'=>$prov,'ampur'=>substr($rs->distid,2,2))).'">'.$rs->distname.'</a>',number_format($rs->amt));
		}
		$tables->rows[]=array('รวม',number_format($dbs->sum->amt),'config'=>array('class'=>'subfooter'));
		//$ret.=print_o($dbs,'$dbs');
	}

	if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);

	if ($prov && $ampur) {
		// Count
		$stmt='SELECT *
			FROM %co_subdistrict% cod
			LEFT JOIN
				(SELECT
				  CONCAT(p.`changwat`,p.`ampur`, p.`tambon`) `subdistid`
				, COUNT(*) `amt`
				FROM %qtmast% q
					LEFT JOIN %db_person% p USING(`psnid`)
				%WHERE%
			GROUP BY `tambon`) a USING(`subdistid`)
			WHERE LEFT(`subdistid`,2) = :changwat AND SUBSTR(subdistid,3,2) = :ampur;
			-- {sum:"amt",reset:false}';

		$dbs=mydb::select($stmt);

		$tables->rows[]=array('ตำบล','','config'=>array('class'=>'subheader'));
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array('<a href="'.url('imed/app/poorman/report/summary',array('prov'=>$prov,'ampur'=>$ampur,'tambon'=>substr($rs->subdistid,4,2))).'">'.$rs->subdistname.'</a>',number_format($rs->amt));
		}
		$tables->rows[]=array('รวม',number_format($dbs->sum->amt),'config'=>array('class'=>'subfooter'));
		//$ret.=print_o($dbs,'$dbs');
	}

	if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);



	// Sex
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "PSNL.SEX"
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('เพศ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->value,$rs->amt);
	}


	// Married
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "PSNL.MARRIED"
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);
	$tables->rows[]=array('สถานภาพสมรส','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->value,$rs->amt);
	}

	$qtChoices['PSNL.HOME.STATUS']=$fldList['PSNL.HOME.STATUS.CHOICE'];// array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง');

	// ที่อยู่อาศัย
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "PSNL.HOME.STATUS"
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('ที่อยู่อาศัย','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.HOME.STATUS'][$rs->value],$rs->amt);
	}

	$qtChoices['PSNL.EDUCA']=$fldList['PSNL.EDUCA.CHOICE'];

	// ระดับการศึกษา
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "PSNL.EDUCA"
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('ระดับการศึกษา','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.EDUCA'][$rs->value],$rs->amt);
	}

	$qtChoices['PSNL.OCCUPA'] = array(
		1=>'ไม่มีอาชีพ/ว่างงาน',
		'นักเรียน/นักศึกษา',
		'ค้าขาย/ธุรกิจส่วนตัว',
		'ภิกษุ/สามเณร/แม่ชี',
		'เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)',
		'ข้าราชการ/พนักงานของรัฐ',
		'พนักงานรัฐวิสาหกิจ',
		'พนักงานบริษัท',
		'รับจ้าง',
		99=>'อื่น ๆ'
	);

	// อาชีพ
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "PSNL.OCCUPA"
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('อาชีพ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($qtChoices['PSNL.OCCUPA'][$rs->value],$rs->amt);
	}

	// สภาวะความยากลำบาก
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.TYPE.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สภาวะความยากลำบาก','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สาเหตุความยากลำบาก
	$stmt='SELECT
		  TRIM(tr.`part`) `part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.CAUSE.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สาเหตุความยากลำบาก','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สถานะทางสุขภาพ
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.HEALTH.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สถานะทางสุขภาพ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สิ่งที่ต้องการให้รัฐช่วยเหลือ
	$stmt='SELECT
					  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.NEED.GOV.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สิ่งที่ต้องการให้รัฐช่วยเหลือ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// เคยได้รับความช่วยเหลือ
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND (tr.`part` LIKE "POOR.HELP.ORG.YES")
		GROUP BY `value`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('เคยได้รับความช่วยเหลือจากหน่วยงาน','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(($rs->value == 1 ? 'ไม่' : '').$fldList[$rs->part],$rs->amt);
	}

	// เคยได้รับความช่วยเหลือเป็น
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.HELP.ORG.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('เคยได้รับความช่วยเหลือเป็น','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}

	// สิ่งที่ต้องการให้รัฐช่วยเหลือ
	$stmt='SELECT
		  tr.`part`
		, tr.`value`
		, COUNT(*) `amt`
		FROM %qtmast% q
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %qttran% tr USING(`qtref`)
		%WHERE% AND tr.`part` LIKE "POOR.NEED.COMMUNITY.LIST.%" AND tr.`value`>0
		GROUP BY `part`;
		-- {reset:false}';
	$dbs=mydb::select($stmt);

	$tables->rows[]=array('สิ่งที่ต้องการให้ชุมชนช่วยเหลือ','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($fldList[$rs->part],$rs->amt);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');








	//if (!$ampur) return $ret;


	if ($export) {
		foreach ($fldList as $key => $value) {
			if (is_array($value)) unset($fldList[$key]);
		}

		//$ret .= print_o($fldList, '$fldList');

		$fldList['homeareacode'] = 'รหัสพื้นที่ปัจจุบัน';
		$fldList['regareacode'] = 'รหัสพื้นที่ทะเบียนบ้าน';

		if (!$isAdmin) mydb::where('(q.`uid` = :uid'.($zones ? ' OR ('.R::Model('imed.person.zone.condition',$zones).')' : '').')', ':uid', i()->uid);

		$orderby = '`areacode` ASC, `qtref` ASC';
		$limit = SG\getFirst(post('items'),'');
		//if ($ampur) $orderby='`qtref`';
		mydb::value('$orderby',$orderby);
		//mydb::value('$limit','LIMIT '.$limit);
		$stmt = 'SELECT
			  q.`psnid`
			, tr.`part`
			, tr.`value`
			, q.`qtref`
			, q.`collectname`
			, q.`qtdate`
			, q.`qtstatus`
			, CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,p.`village`) `areacode`
			, CONCAT(p.`rchangwat`,p.`rampur`,p.`rtambon`,p.`rvillage`) `regareacode`
			FROM %qtmast% q
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
				LEFT JOIN %qttran% tr USING(`qtref`)
			%WHERE%
			ORDER BY $orderby
			;
			-- {resultType: "resource"}
			';
		$dbs = mydb::select($stmt);
		//$ret.=mydb()->_query;
		//debugMsg(mydb()->_query);
		//unset($dbs->_resource);
		//$ret.=gettype($dbs->_resource);
		//$ret.=print_o($dbs,'$dbs');

		// FIXME : บางคนไม่มีชื่อ นามสกุล แต่มีชื่อเต็มอยู่
		// MEE : For me only


		$tables = new Table();
		$tables->thead = $fldList;
		$curRef = NULL;
		$fldListKey = array_keys($fldList);
		while($rs = $dbs->resource->fetch_array(MYSQLI_ASSOC)) {
			//$ret.= ++$no.' ';
			$rs = (Object) $rs;

			//$ret .= print_o($rs,'$rs');

			if (!in_array($rs->part, $fldListKey)) continue;
			if ($rs->qtref != $curRef) {
				//print_o($row,'$row',1);
				if ($row) $tables->rows[] = $row;
				unset($row);
				foreach ($fldList as $k => $v) $row[$k] = '';
				$row['col-no QTMAST.NO'] = ++$no;
				$row['QTMAST.QTREF'] = $rs->qtref;
				$row['COLLECTOR.NAME'] = $rs->collectname;
				$row['COLLECTOR.DATE'] = $rs->qtdate;
				$row['QTMAST.QTSTATUS'] = $rs->qtstatus;
				$row['homeareacode'] = $rs->areacode;
				$row['regareacode'] = $rs->regareacode;
			}
			$row[$rs->part] = (String) $rs->value;

			$curRef = $rs->qtref;
		}
		//print_r($tables);

		//$ret.=$tables->build();

		if (empty($tables->rows)) return $ret;

		/*
		$tables = new Table();
		$tables->thead=$fldList;
		$no=0;
		foreach ($dbs->items as $rs) {
			if (!in_array($rs->part, array_keys($fldList))) continue;
			if (empty($tables->rows[$rs->qtref])) {
				foreach ($fldList as $k=>$v) $tables->rows[$rs->qtref][$k]='';
				$tables->rows[$rs->qtref]['col-no QTMAST.NO']=++$no;
				$tables->rows[$rs->qtref]['QTMAST.QTREF']=$rs->qtref;
				$tables->rows[$rs->qtref]['COLLECTOR.NAME']=$rs->collectname;
				$tables->rows[$rs->qtref]['COLLECTOR.DATE']=$rs->qtdate;
				$tables->rows[$rs->qtref]['QTMAST.QTSTATUS']=$rs->qtstatus;
			}
			$tables->rows[$rs->qtref][$rs->part]=(string)$rs->value;
		}
		*/

		$ret.='<div style="width:100%;height:600px; overflow:scroll;">'.$tables->build().'</div>';

		die(R::Model('excel.export',$tables,'คนยากลำบาก-'.$prov.'-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));

		return $ret;
	}

	//$ret.=$tables->build();

	//$ret.='<p>รวมทั้งสิ้น '.count($tables->rows).' รายการ</p>';
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>