<?php

/**
 * iMed report ได้รับเงินยังชีพคนพิการ
 *
 */
function imed_report_localmoney($self) {
	$isAdmin=user_access('administer imeds');

	if (!$isAdmin) return message('error','access denied');

	$prov=SG\getFirst($_REQUEST['p'],'90');
	$ampur=$_REQUEST['a'];
	$tambon=$_REQUEST['t'];
	$village=$_REQUEST['v'];
	$todate=sg_date(SG\getFirst(post('todate'),date('Y-m-d')),'Y-m-d');
	$showRawDate=false;

	$isAdmin=user_access('administer imeds');

	$self->theme->title='ได้รับเบี้ยยังชีพคนพิการ';
	if (post('s')!='1') {
		$ret.='<h3>'.$self->theme->title.'</h3>';
		$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output">';
		$ret.='<input type="hidden" name="s" value="1" />';
		$ret.='<div class="form-item">'._NL;
		$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
		$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
		foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
		$ret.='</select>'._NL;
		if ($prov) {
			$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
			$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
			foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
			$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
		}
		$ret.='</div>'._NL;
		$ret.='<input type="submit" class="button main floating" value="ดูรายงาน" />'._NL;
		$ret.='<div class="optionbar"><ul>';
		if ($isAdmin) {
		}
		$ret.='</ul></div>';
		$ret.='</form>';
	}

	$ret.='<div id="report-output">';

	$where=array();
	if ($isAdmin && (post('showerror') || post('fixed'))) {
		//$where=sg::add_condition($where,'qt.`part`="PSNL.1.9.1.3" && qt.`value`!="" ');
	} else  if (!empty($tambon)) {
		//$where=sg::add_condition($where,'qt.`part`="OTHR.5.1.8" ');
		//if ($todate) $where=sg::add_condition($where,'qt.`value`<=:todate','todate',$todate);
		if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
		if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
		if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
		if ($village) $where=sg::add_condition($where,'p.`village`=:village','village',$village);

		$stmt='SELECT 
							CONCAT(IFNULL(`prename`,"")," ",p.`name`," ",`lname`) fullname,
							qt.`part`, qt.`value` `value`,
							p.`changwat`, p.`ampur`, p.`tambon`,
							cosd.`subdistname`, codist.`distname`, copv.`provname`
							,COUNT(*)
						FROM %db_person% p
							LEFT JOIN %imed_qt% qt ON qt.`pid`=p.`pid` AND qt.`part`="OTHR.5.1.8"
							LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
							LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
							LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						ORDER BY `provname` ASC, `distname` ASC, `subdistname` ASC';
		$dbs=mydb::select($stmt,$where['value']);
		$ret.='Tambon='.$tambon.'<pre>'.str_replace("\t", " ", mydb()->_query).'</pre>';
	}

	// Show register date error of fix it
	if ($isAdmin && (post('showerror') || post('fixed'))) {
		$showRawDate=true;
		if (post('fixed')) {
			foreach ($dbs->items as $key=>$rs) {
				$newExpireDate=sg_date($rs->expiredate.' 00:00:00','Y-m-d');
				$newRegDate=sg_date($rs->registerdate,'Y-m-d');
				$dbs->items[$key]->registerdate=$newRegDate;
				$dbs->items[$key]->expiredate=$newExpireDate;
				if ($newRegDate) mydb::query('UPDATE %imed_qt% SET `value`=:newRegDate WHERE `qid`=:qid LIMIT 1',':newRegDate',$newRegDate, ':qid',$rs->rdQid);
				if ($newExpireDate) mydb::query('UPDATE %imed_qt% SET `value`=:newExpDate WHERE `qid`=:qid LIMIT 1',':newExpDate',$newExpireDate, ':qid',$rs->qid);
			}
		}
	}

	$tables = new Table();
	$tables->thead=array('','ชื่อ-สกุล','date regdate'=>'ได้รับเบี้ยยังชีพคนพิการ','พื้นที่');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.SG\getFirst($rs->fullname,'...').'</a>',
			$rs->part.':'.$rs->value,
			SG\implode_address($rs,'short'),
		);
	}

	$ret .= $tables->build();
	if ($dbs->_empty) $ret.='ไม่มีข้อมูล';
	$ret.=print_o($dbs,'$dbs');
	$ret.='</div>';
	return $ret;
}
?>