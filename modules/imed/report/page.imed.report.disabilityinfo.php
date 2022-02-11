<?php
/**
 * iMed report ข้อมูลคนพิการ
 *
 */
function imed_report_disabilityinfo($self) {
	$isAdmin=user_access('administer imeds');
	$export=post('export');

	//if (!$isAdmin) return message('error','access denied');
	
	$self->theme->title='ข้อมูลคนพิการ';
	$prov=SG\getFirst(post('p'),'90');
	$ampur=post('a');
	$tambon=post('t');
	$village=post('v');

	if (!post('f')) {
		$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output"><input type="hidden" name="f" value="n" />';
		$ret.='<h3>ข้อมูลคนพิการ</h3>';
		$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
		$ret.='<div class="form-item">'._NL;
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--เลือกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--เลือกอำเภอ--</option>'._NL;
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
			$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		}
		$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		//$ret.='<input type="checkbox" name="export" value="1" />Export';
		//$ret.='&nbsp;&nbsp;<button type="submit" class="btn" name="export" value="excel"><i class="icon -download"></i><span>Export</span></button>';
		$ret.='</div>'._NL;
		$ret.='<div class="optionbar"><ul>';
		$ret.='</ul></div>';
		$ret.='</form>';
		$ret.='<div id="report-output">';
	}

	$zones=imed_model::get_user_zone(i()->uid,'imed');

	if ($ampur) {
		mydb::where('id.`discharge` IS NULL');
		if ($prov) mydb::where('p.`changwat`=:prov',':prov',$prov);
		if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
		if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);
		if ($village) mydb::where('p.`village`=:village',':village',$village);
		if ($zones) {
			mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
		}

		$order='p.`changwat` ASC, p.`ampur` ASC, p.`tambon` ASC, LPAD(p.`village`,2,0) ASC';
		$order.=', CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC';
		$stmt='SELECT
							p.`psnid`
							, `prename`, p.`name`," ",`lname`
							, CONCAT(p.`name`," ",`lname`) fullname
							, p.`sex`, p.`birth`, ms.`cat_name` mstatusText
							, cooccu.`occu_desc`
							, co_na.`value` nationText
							, co_na.`value` religionText
							, co_ed.`edu_desc` educateText
							, co_penv.`value` `HLTH.2.5.4`
							, p.`cid`
							, p.`phone`
							, GROUP_CONCAT(care.`detail1`) carerName
							, co_raksa.`value` `PSNL.1.10.1`
							, id.`regdate`
							, co_begetting.`cat_name` begettingText
							, p.`created`, u.`name` createby
							, p.`house`, p.`village`, cod.`distname`, cop.`provname`, cos.`subdistname`
							, p.`tambon`, p.`ampur`, p.`changwat`
							, GROUP_CONCAT(co_pros.`cat_name`) prostheticText
							, d.*
						FROM 
							(
								SELECT *, GROUP_CONCAT(`defect`) defectText
									FROM %imed_disabled_defect% GROUP BY `pid`
							) d
							LEFT JOIN %imed_disabled% id ON id.`pid`=d.`pid`
							LEFT JOIN %db_person% p ON p.`psnid`=d.`pid`
							LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
							LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
							LEFT JOIN %co_subdistrict% cos ON cos.`subdistid`=CONCAT(p.`changwat`, p.`ampur`, p.`tambon`)
							LEFT JOIN %users% u ON u.`uid`=p.`uid`
							LEFT JOIN %co_category% ms ON ms.`cat_id`=p.`mstatus`
							LEFT JOIN %co_occu% cooccu ON cooccu.`occu_code`=p.`occupa`
							-- สัญชาติ
							LEFT JOIN %imed_qt% co_na ON co_na.`pid`=p.`psnid` AND co_na.`part`="PSNL.1.5.1"
							-- ศาสนา
							LEFT JOIN %imed_qt% co_re ON co_re.`pid`=p.`psnid` AND co_re.`part`="PSNL.1.5.3"
							LEFT JOIN %co_educate% co_ed ON co_ed.`edu_code`=p.`educate`
							-- ประวัติการแพ้ยา
							LEFT JOIN %imed_qt% co_penv ON co_penv.`pid`=p.`psnid` AND co_penv.`part`="HLTH.2.5.4"
							LEFT JOIN %imed_tr% care ON care.`pid`=p.`psnid` AND care.`tr_code`="carer"
							-- สิทธิการรักษา
							LEFT JOIN %imed_qt% co_raksa ON co_raksa.`pid`=p.`psnid` AND co_raksa.`part`="PSNL.1.10.1"
							-- สาเหตุ
							LEFT JOIN %co_category% co_begetting ON co_begetting.`cat_id`=id.`begetting`
							-- กายอุปกรณ์
							LEFT JOIN %imed_tr% pros ON pros.`pid`=p.`psnid` AND pros.`tr_code`="prosthetic"
							LEFT JOIN %co_category% co_pros ON co_pros.`cat_id`=pros.`cat_id`
				
						%WHERE%
						GROUP BY d.`pid`
						ORDER BY '.$order;
		$dbs=mydb::select($stmt);
		//$ret.='<pre>'.mydb()->_query.'</pre>';
		//$ret.=print_o($dbs,'$dbs');

		if ($dbs->_num_rows) {
			$ret.='<nav class="nav -page"><a class="btn" href="'.url(q(),array('p'=>$prov, 'a'=>$ampur, 't'=>$tambon,'v'=>$village, 'export'=>1)).'"><i class="icon -download"></i><span>Export</span></a></nav>';

			$tables = new Table();
			$tables->addClass='-allinfo';

			$tables->thead=array(
											'no'=>'ลำดับ',
											'คำนำหน้าชื่อ',
											'title'=>'ชื่อ',
											'นามสกุล',
											'เพศ',
											'วันเกิด',
											'amt age'=>'อายุ',
											'สถานภาพ',
											'อาชีพ',
											'center 1'=>'เชื้อชาติ',
											'cemter 2'=>'สัญชาติ',
											'ศาสนา',
											'การศึกษา',
											'กรุ๊ปเลือด',
											'ประวัติการแพ้ยา',
											'เลขที่บัตรประชาชน',
											'center 3'=>'เลขที่บัตรคนพิการ',
											'ที่อยู่',
											'โทรศัพท์',
											'บุคคลที่ติดต่อกรณีฉุกเฉิน(ผู้ดูแล)',
											'สิทธิการรักษา',
											'วันขึ้นทะเบียนผู้พิการ',
											'วันได้รับเอกสารรับรอง',
											'ประเภทความพิการ',
											'สาเหตุ',
											'ลักษณะความพิการ',
											'กายอุปกรณ์'
											);
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
													++$no,
													$rs->prename,
													SG\getFirst(trim($rs->name),$rs->psnid),
													$rs->lname,
													$rs->sex,
													$rs->birth,
													$rs->birth?date('Y')-sg_date($rs->birth,'Y'):'',
													$rs->mstatusText,
													$rs->occu_desc,
													$rs->nationText,
													$rs->nationText,
													$rs->religionText,
													$rs->educateText,
													'-',
													$rs->{'HLTH.2.5.4'},
													$rs->cid,
													'-',
													SG\implode_address($rs,'short'),
													$rs->phone,
													$rs->carerName,
													$rs->{'PSNL.1.10.1'},
													$rs->regdate,
													'-',
													$rs->defectText,
													$rs->begettingText,
													'',
													$rs->prostheticText,
													);
			}
			if ($export) {
				die(R::Model('excel.export',$tables,'คนพิการ-'.$prov.'-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
			}
			$ret.=$tables->build();
		} else {
			$ret.='<p>ไม่มีข้อมูล</p>';
		}
	}

	if (!post('f')) {
		$ret.='</div>';
		$ret.='<style type="text/css">
		.item.-allinfo td {white-space:nowrap;}
		</style>';
	}
	return $ret;
}
?>