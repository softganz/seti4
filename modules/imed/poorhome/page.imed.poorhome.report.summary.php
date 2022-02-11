<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome_report_summary($self) {
	$self->theme->title='รายงาน';
	$self->theme->toolbar=R::Page('imed.poorhome.toolbar',$self);

	$act=post('act');
	$field=post('k');
	$muni=post('muni');
	$checkValue=post('v');

	$isAdmin=i()->admin;
	$isAccess=$isAdmin || user_access('access imed poorhomes');

	$keys=array(
					'housingstatus'=>array('housingproblem','housingneed'),
					'clothesstatus'=>array('clothesproblem','clothesneed'),
					'foodstatus'=>array('foodproblem','foodneed'),
					'healthstatus'=>array('healthproblem','healthneed'),
					'savingstatus'=>array('savingproblem','savingneed'),
					'jobstatus'=>array('jobproblem',''),
					'govhelp'=>array('govhelp'),
					'otherproblem'=>array('otherproblem'),
					'problempiority'=>array('problempiority'),
					'jobproblem'=>array('jobproblem'),
					'jobaddoccuspecify'=>array('jobaddoccuspecify'),
					'jobathomespecify'=>array('jobathomespecify'),
					'jobotherspec'=>array('jobotherspec'),
					);

	switch ($act) {
		case 'problem' :
			if (!$keys[$field][0]) return;

			if (post('muni')) mydb::where('p.`municipality` = :muni',':muni',$muni);

			$stmt='SELECT `poorid`, `municipality`, `'.$field.'`, `'.$keys[$field][0].'` `problem`
						FROM %poor% p
						%WHERE%
						HAVING `problem`!="" ';

			$dbs=mydb::select($stmt);

			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','เทศบาล','สภาพปัญหา','');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
													++$no,
													$rs->municipality,
													str_replace("\n",'<br />',$rs->problem),'<a href="'.url('imed/poorhome/view/'.$rs->poorid).'" target="_blank"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a>'
													);
			}
			$ret.=$tables->build();
			//$ret.=print_o($dbs);
			return $ret;
			break;

		case 'need' :
			if (!$keys[$field][1]) return;

			if (post('muni')) mydb::where('p.`municipality` = :muni',':muni',$muni);

			$stmt = 'SELECT
								`poorid`
							, `municipality`
							, `'.$field.'`
							, `'.$keys[$field][1].'` `need`
						FROM %poor% p
						%WHERE%
						HAVING `need`!="" ';

			$dbs=mydb::select($stmt);

			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','เทศบาล','ความต้องการ','');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
													++$no,
													$rs->municipality,
													str_replace("\n",'<br />',$rs->need),'<a href="'.url('imed/poorhome/view/'.$rs->poorid).'" target="_blank"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a>'
													);
			}
			$ret.=$tables->build();
			//$ret.=print_o($dbs);
			return $ret;
			break;

		case 'list' :
			if (post('muni')) mydb::where('p.`municipality` = :muni',':muni',$muni);
			mydb::where('(p.`'.$field.'` = :value'.($checkValue == 'na' ? ' OR p.`'.$field.'` IS NULL' : '').')', ':value', $checkValue == 'na' ? '' : $checkValue);

			$stmt='SELECT `poorid`, p.`uid`, `municipality`, `'.$field.'` `checkValue`
						, p.`commune`
						, p.`house`
						, p.`village`
						, cosub.`subdistname` subdistname
						, codist.`distname` distname
						, copv.`provname` provname
						FROM %poor% p
							LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
							LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
							LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
							LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
						%WHERE%
						';

			$dbs=mydb::select($stmt);

			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','เทศบาล','ชุมชน','ที่อยู่','สถานะ','');
			foreach ($dbs->items as $rs) {
				if (!($isAccess || $uid==$rs->uid)) unset($rs->house,$rs->village);
				$tables->rows[]=array(
													++$no,
													$rs->municipality,
													$rs->commune,
													SG\implode_address($rs,'short'),
													SG\getFirst($rs->checkValue,'ไม่ระบุ'),
													'<a href="'.url('imed/poorhome/view/'.$rs->poorid).'" target="_blank"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a>'
													);
			}
			$ret.=$tables->build();
			//$ret.=print_o($dbs);
			return $ret;

			break;
	}

	$ret.='<nav class="nav -page"><form method="get">';
	$ret.='เงื่อนไข : <select name="muni" class="form-select"><option value="">** ทุกเทศบาล **</option>';
	$dbs=mydb::select('SELECT `municipality`, COUNT(*) `amt` FROM %poor% GROUP BY `municipality` ');
	foreach ($dbs->items as $rs) {
		$ret.='<option value="'.$rs->municipality.'" '.($rs->municipality==$muni?'selected="selected"':'').'>'.$rs->municipality.' ('.$rs->amt.' ครัวเรือน)</option>';
	}
	$ret.='</select> ';
	$ret.='<button class="btn -primary" type="submit"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>';
	$ret.='</form></nav>';

	$ret.=__imed_poor_report_showfield($dbs,'housingstatus','สภาพที่อยู่อาศัย',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'housingowner','สถานะที่อยู่อาศัย',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'housingmankong','ต้องการเข้าร่วมโครงการบ้านมั่นคง',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'clothesstatus','เครื่องนุ่งห่ม/ของใช้ในครัวเรือน',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'foodstatus','อาหาร',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'healthstatus','สุขภาพ',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'savingstatus','การออม',$keys);

	$ret.=__imed_poor_report_showfield($dbs,'porpiangstatus','ความรู้ความเข้าใจในปรัชญาเศรษฐกิจพอเพียง',$keys);

	$ret.=__imed_poor_report_showneed('jobproblem','อาชีพและรายได้ของสมาชิกในครัวเรือน',$keys);
	$ret.=__imed_poor_report_showneed('jobaddoccuspecify','อาชีพและรายได้ของสมาชิกในครัวเรือน - อาชีพเสริมเพื่อเพิ่มรายได้',$keys);
	$ret.=__imed_poor_report_showneed('jobathomespecify','อาชีพและรายได้ของสมาชิกในครัวเรือน - รับงานมาทำที่บ้าน',$keys);
	$ret.=__imed_poor_report_showneed('jobotherspec','อาชีพและรายได้ของสมาชิกในครัวเรือน - อาชีพอื่น ๆ',$keys);

	$ret.=__imed_poor_report_showneed('govhelp','การได้รับความช่วยเหลือจากหน่วยงานของรัฐหรือเอกชนในรอบปีที่ผ่านมา',$keys);

	$ret.=__imed_poor_report_showneed('otherproblem','ข้อเสนอแนะ สภาพปัญหาอื่น ๆ หรือความต้องการช่วยเหลือ',$keys);

	$ret.=__imed_poor_report_showneed('problempiority','ลำดับความสำคัญของปัญหาที่ต้องการให้แก้ไขเร่งด่วน',$keys);


	$ret.='<style type="text/css">
	.item.-summary td {width:100px; text-align:center;}
	.item.-summary td:first-child {width:auto; text-align:left; font-weight:bold;}
	</style>';
	return $ret;
}

function __imed_poor_report_showfield($dbs,$field,$datakey,$keys=array()) {
	$muni=post('muni');

	mydb::where('p.`municipality`!=""');
	if ($muni) mydb::where('p.`municipality` = :muni',':muni',$muni);
	$stmt = 'SELECT
						`'.$field.'` `label`
					, COUNT(*) amt
					FROM %poor% p
					%WHERE%
					GROUP BY `'.$field.'`
					';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('-summary');
	$tables->thead[]='';
	$tables->rows[$datakey][]=$datakey;
	foreach ($dbs->items as $rs) $total+=$rs->amt;
	foreach ($dbs->items as $rs) {
		$tables->thead[]=SG\getFirst($rs->label,'ไม่ระบุ').'<br />(ครัวเรือน)';
		$tables->rows[$datakey][]='<a class="sg-action" href="'.url('imed/poorhome/report/summary',array('act'=>'list','k'=>$field,'v'=>SG\getFirst($rs->label,'na'),'muni'=>$muni)).'" data-rel="box"><strong>'.$rs->amt.'</strong></a>'.'<br />('.round($rs->amt*100/$total,2).'%)';
	}
	$tables->thead[]='รวม<br />(ครัวเรือน)';
	$tables->thead[]='';
	$tables->thead[]='';
	$tables->rows[$datakey][]='<strong>'.$total.'</strong>';
	$tables->rows[$datakey][]=$keys[$field][0]?'<a class="sg-action" href="'.url('imed/poorhome/report/summary',array('act'=>'problem','k'=>$field,'muni'=>$muni)).'" data-rel="box">สภาพปัญหา</a>':'';
	$tables->rows[$datakey][]=$keys[$field][1]?'<a class="sg-action" href="'.url('imed/poorhome/report/summary',array('act'=>'need','k'=>$field,'muni'=>$muni)).'" data-rel="box">ความต้องการ</a>':'';
	$ret.=$tables->build();
	return $ret;
}

function __imed_poor_report_showneed($field,$datakey,$keys=array()) {
	$tables = new Table();
	$tables->addClass('-summary');
	$tables->thead[]='';
	$tables->rows[$datakey][]=$datakey;
	$tables->thead[]='';
	$tables->thead[]='';
	$tables->rows[$datakey][]=$keys[$field][0]?'<a class="sg-action" href="'.url('imed/poorhome/report/summary',array('act'=>'problem','k'=>$field,'muni'=>post('muni'))).'" data-rel="box">รายละเอียด</a>':'';
	$ret.=$tables->build();
	return $ret;
}
?>