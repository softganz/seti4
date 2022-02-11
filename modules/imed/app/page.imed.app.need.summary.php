<?php
/**
* iMed : Need Summary
* Created 2020-10-24
* Modify  2020-10-24
*
* @param Object $self
* @return String
*
* @usage imed/app/need/summary
*/

$debug = true;

function imed_app_need_summary($self) {
	$getShow = post('show');

	$getChangwat = post('p');
	$getAmpur = post('a');
	$getTambon = post('t');
	$getVillage = post('v');
	$getNeedType = post('nt');
	$getDone = post('done');

	if (!post('f')) {
		$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#imed-app" style="flex: 1 0 100%; padding:0 4px;"><input type="hidden" name="f" value="n" />';
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

	$ret .= '<div id="imed-app" class="imed-app -fill">'._NL;

	//$ret .= '<header class="header -imed-pocenter"><h3>ความต้องการ</h3></header>';

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
				'<a class="sg-action" href="'.url('imed/needs',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype)).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->needTypeName.'</a>',
				'<a class="sg-action" href="'.url('imed/needs',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype)).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->amt.'</a>',
				$rs->done > 0 ? '<a class="sg-action" href="'.url('imed/needs',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype, 'done'=>'y')).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.$rs->done.'</a>' : '',
				$rs->amt-$rs->done > 0 ? '<a class="sg-action" href="'.url('imed/needs',array('f'=>'y','p'=>$getChangwat, 'a'=>$getAmpur, 't'=>$getTambon, 'nt'=>$rs->needtype, 'done'=>'n')).'" data-rel="box" data-webview="ต้องการ'.$rs->needTypeName.'" data-width="480" data-max-height="80%">'.($rs->amt-$rs->done).'</a>' : '',
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
			, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `patient_name`
			, nt.`name` `needTypeName`
			FROM %imed_need% n
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
			%WHERE%
			ORDER BY `needid` DESC';
		$dbs = mydb::select($stmt);
		//$ret .= mydb()->_query;


		$ui = new Ui('div','ui-card imed-my-note -need');
		$ui->addId('imed-my-note');

		foreach ($dbs->items as $rs) {
			$ui->add(R::View('imed.need.render',$rs, '{page: "app"}'), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
		}
		$ret .= $ui->build(true).'<!-- imed-my-note -->';
	}

	$ret .= '</div><!-- imed-app -->';

	return $ret;
}
?>