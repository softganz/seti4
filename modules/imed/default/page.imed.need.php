<?php
/**
* Vew personal health information
*
* @param Integer $psnId
* @return String
*/
function imed_need($self, $psnId = NULL, $action = NULL, $tranId = NULL) {
	$getChangwat = post('p');
	$getAmpur = post('a');
	$getTambon = post('t');
	$getVillage = post('v');
	$getNeedType = post('nt');
	$getDone = post('done');
	$getDownload = post('output');

	$isAdmin = is_admin('imed');
	$myZones = R::Model('imed.zone.get',i()->uid,'imed');
	$urgencyList = array(1 => 'รอได้', 5 => 'เร่งด่วน', 9=> 'เร่งด่วนมาก');


	if (!post('f')) {

		$ret = R::View('imed.toolbox',$self,'iMed@ความต้องการ', 'need');

		$ret .= '<div class="imed-sidebar">'.R::View('imed.menu.main')->build().'</div>';
		$ret .= '<div id="imed-app" class="imed-app">'._NL;


		$headerUi = new Ui();
		//$headerUi->add('<a href=""><i class="icon -material">view_list</i><span class="-hidden">คงเหลือ</span></a>');

		$ret .= '<header class="header -imed-pocenter"><nav class="nav -back"><a class="" href="'.url('imed').'"><i class="icon -material">arrow_back</i></a></nav><h3>ความต้องการ</h3><nav class="nav">'.$headerUi->build().'</header>';


		$ret.='<form method="get" action="'.url('imed/need').'" class="report-form sg-form" data-rel="replace:#report-result" style="padding: 0 4px; background-color: transparent;"><input type="hidden" name="f" value="n" />';
		//$ret .= '<h3>รายงานการเยี่ยมบ้าน</h3>';
		$ret.='<div class="form-item">'._NL;

		$provdbs = mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_need% n LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');

		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" class="form-select sg-changwat" data-change="submit">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" class="form-select sg-ampur -hidden" data-change="submit">'._NL.'<option value="">--เลือกอำเภอ--</option>'._NL;
		$ret.='</select>'._NL;
		$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" class="form-select sg-tambon -hidden" data-change="submit">'._NL.'<option value="">--เลือกตำบล--</option>'._NL;
		$ret.='</select>'._NL;

		//$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" class="form-select sg-tambon -hidden" data-change="submit">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
		$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select sg-village" data-change="submit">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;

		//$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
		$ret.='</div>';
		$ret.='</form>';
	}

	$ui = new Ui();
	if ($getNeedType && ($myZones || $isAdmin)) {
		$ui->add('<a href="'.url('imed/need',array('output'=>'cvs','f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$getNeedType)).'" target="_blank" title="ดาวน์โหลด"><i class="icon -material">cloud_download</i></a>');
	}
	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>ความต้องการ</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$ret .= '<div id="report-result" style="background-color: transparent;">';

	mydb::where('p.`changwat` != ""');
	if ($getChangwat) mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
	if ($getAmpur) mydb::where('p.`ampur` = :ampur', ':ampur', $getAmpur);
	if ($getTambon) mydb::where('p.`tambon` = :tambon', ':tambon', $getTambon);
	if ($getVillage) mydb::where('p.`village` = :village', ':village', intval($getVillage));
	if ($getDone == 'y') mydb::where('n.`status` = 1');
	else if ($getDone == 'n') mydb::where('n.`status` IS NULL');


	if (!$getNeedType) {
		$stmt = 'SELECT
				n.`needtype`
			, COUNT(*) `amt`
			, COUNT(n.`status`) `done`
			, nt.`name` `needTypeName`
			FROM %imed_need% n
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
			%WHERE%
			GROUP BY `needtype`
			ORDER BY `amt` DESC;
			-- {reset: false}';
		$dbs = mydb::select($stmt);

		$tables = new Table();
		$tables->thead = array(
			'ความต้องการ',
			'total -amt -nowrap' => 'จำนวน',
			'done -amt -nowrap' => 'ดำเนินการ',
			'wait -amt -nowrap' => 'ยังไม่ได้รับ',
		);
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a class="sg-action" href="'.url('imed/need',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype)).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->needTypeName.'</a>',
				'<a class="sg-action" href="'.url('imed/need',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype)).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->amt.'</a>',
				$rs->done > 0 ? '<a class="sg-action" href="'.url('imed/need',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype, 'done'=>'y')).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->done.'</a>' : '',
				$rs->amt-$rs->done > 0 ? '<a class="sg-action" href="'.url('imed/need',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype, 'done'=>'n')).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.($rs->amt-$rs->done).'</a>' : '',
			);
		}
		$ret .= $tables->build();
		//$ret .= print_o($dbs);
	}

	if ($getNeedType) {
		mydb::where('n.`needtype` = :needtype', ':needtype', $getNeedType);

		$stmt = 'SELECT
				n.*
			, u.`username`, u.`name`
			, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `patient_name`
			, nt.`name` `needTypeName`
			, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
			, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
			, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
			, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
			, u.`mobile`
			, u.`organization`
			FROM %imed_need% n
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
				LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
				LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			%WHERE%
			ORDER BY `needid` DESC';

		$dbs = mydb::select($stmt);



		$ui = new Ui('div','ui-card imed-my-note -need');
		$ui->addId('imed-my-note');


		$tables = new Table();
		$tables->thead = array('ผู้ป่วย', 'ความต้องการ', 'ความเร่งด่วน', 'สถานะ', 'ที่อยู่','หมู่ที่','ตำบล','อำเภอ','จังหวัด', 'ผู้บันทึก', 'โทรศัพท์', 'หน่วยงาน', 'วันที่บันทึก');

		foreach ($dbs->items as $rs) {
			$isInZone = $isAdmin || R::Model('imed.zone.right',$myZones,$rs->changwat,$rs->ampur,$rs->tambon);

			$ui->add(R::View('imed.need.render',$rs), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
			if ($getDownload) {
				$tables->rows[] = array(
					$rs->prename.$rs->patient_name,
					$rs->needTypeName,
					$urgencyList[$rs->urgency],
					$rs->status,
					$isInZone ? $rs->house : '',
					$isInZone ? $rs->village : '',
					$rs->subdistname,
					$rs->distname,
					$rs->provname,
					$rs->name,
					$isInZone ? $rs->mobile : '',
					$isInZone ? $rs->organization : '',
					sg_date($rs->created, 'Y-m-d'),
				);
			}
		}
		$ret .= $ui->build(true).'<!-- imed-my-note -->';

		if ($getDownload) {
			die(R::Model('excel.export',$tables,'ความต้องการ'.($getChangwat ? '-'.$getChangwat : '').'-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
		}
	}

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($tables,'$tables');
	$ret .= '</div>';

	if (!post('f')) {
		$ret .= '</div><!-- imed-app -->';
	}

	return $ret;
}
?>