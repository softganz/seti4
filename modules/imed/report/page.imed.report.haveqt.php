<?php

	/**
	 * iMed report วันหมดอายุบัตร
	 *
	 */
	function imed_report_haveqt($self) {
		$prov=SG\getFirst($_REQUEST['p'],'90');
		$ampur=$_REQUEST['a'];
		$tambon=$_REQUEST['t'];
		$village=$_REQUEST['v'];
		$todate=sg_date(SG\getFirst(post('todate'),date('Y-m-d')),'Y-m-d');
		$showRawDate=false;

		$isAdmin=user_access('administer imeds');

		$self->theme->title='แบบสอบถาม';
		if (empty($ampur)) {
			$ret.='<h3>แบบสอบถาม</h3>';
			$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output">';
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
			$ret.='</ul></div>';
			$ret.='</form>';
		}

		$ret.='<div id="report-output">';

		$where=array();
		$where=sg::add_condition($where,'d.pid IS NOT NULL');
		if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
		if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
		if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
		if ($village) $where=sg::add_condition($where,'p.`village`=:village','village',$village);

		$stmt='SELECT q.`pid`, CONCAT(p.`name`," ", p.`lname`) fullname, COUNT(*) qtTotal,
							p.`house`, p.`village`, p.`changwat`, p.`ampur`, p.`tambon`, p.`commune`,
							q.*,
							cosd.`subdistname`, codist.`distname`, copv.`provname`
						FROM `sgz_imed_qt` q
						LEFT JOIN `sgz_db_person` p ON p.`psnid`=q.`pid`
						LEFT JOIN `sgz_imed_disabled_defect` d ON d.`pid`=q.`pid`
						LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY q.pid
						ORDER BY dcreated DESC';
		$dbs=mydb::select($stmt,$where['value']);
//$ret.=print_o($dbs,'$dbs');
		$ret.='<p>จำนวน <strong>'.number_format($dbs->_num_rows).'</strong> คน</p>';

		$tables = new Table();
		$tables->thead=array('','ชื่อ-สกุล','ชุมชน','date regdate'=>'วันที่ป้อน','amt'=>'qt');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				'<a href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.SG\getFirst($rs->fullname,'...').'</a><br />'.SG\implode_address($rs,'short'),
				$rs->commune,
				sg_date($rs->dcreated,'ว ดด ปป H:i'),
				$rs->qtTotal,
			);
		}

		$ret .= $tables->build();

		if ($dbs->_empty) $ret.='ไม่มีข้อมูล';
		//$ret.=print_o($dbs,'$dbs');
		$ret.='</div>';
		return $ret;
	}
?>