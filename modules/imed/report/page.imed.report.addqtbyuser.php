<?php

	/**
	 * iMed report วันหมดอายุบัตร
	 *
	 */
	function imed_report_addqtbyuser($self) {
		$prov=SG\getFirst($_REQUEST['p'],'90');
		$ampur=$_REQUEST['a'];
		$tambon=$_REQUEST['t'];
		$village=$_REQUEST['v'];
		$todate=sg_date(SG\getFirst(post('todate'),date('Y-m-d')),'Y-m-d');
		$showRawDate=false;
		$page=intval(SG\getFirst(post('pg'),1));
		$items=50;

		$isAdmin=user_access('administer imeds');

		$self->theme->title='แบบสอบถาม';
		if (!post('p')) {
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
			$ret.='<ul class="pagenv"></ul>';
			$ret.='</form>';
		}

		$ret.='<div id="report-output">';

		$where=array();
		//		$where=sg::add_condition($where,'d.pid IS NOT NULL');
		$where=sg::add_condition($where,'q.`ucreated` IS NOT NULL AND q.ucreated>1');
		if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
		if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
		if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
		if ($village) $where=sg::add_condition($where,'p.`village`=:village','village',$village);

		$stmt='SELECT SQL_CALC_FOUND_ROWS
							  q.`pid`, CONCAT(p.`name`," ", p.`lname`) fullname
							, q.`ucreated`, q.`dcreated`
						--	, q.*
							, u.`username`, u.`name`
							, COUNT(*) qtTotal
							, p.`commune`
							, cosd.`subdistname`, codist.`distname`, copv.`provname`
						FROM `sgz_imed_qt` q
						LEFT JOIN %users% u ON u.`uid`=q.`ucreated`
						LEFT JOIN %db_person% p ON p.`psnid`=q.`pid`
						LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY q.`ucreated`, q.`pid`
						ORDER BY CONVERT(u.name USING tis620) ASC, CONVERT(fullname USING tis620) ASC
						LIMIT '.(($page-1)*$items).','.$items;

		$dbs=mydb::select($stmt,$where['value']);
		//$ret.=print_o($dbs,'$dbs');

		$totals = $dbs->_found_rows;

		$start=$items*($page-1)+1;
		$end=$start+$dbs->_num_rows-1;
		$prevpage=$page>1?$page-1:1;
		$nextpage=$dbs->_num_rows==$items?$page+1:$page;
		$ret.='<script>$(document).ready(function(){$(".pagenv").html(\'<li>รายการ '.$start.' - '.$end.' จาก '.number_format($totals).'</li><li><a href="'.url(q(),array('p'=>$prov,'a'=>$ampur,'t'=>$tambon,'v'=>$village,'qm'=>post('qm'),'pg'=>$prevpage)).'" class="sg-action" data-rel="#report-output"> < </a></li><li><a href="'.url(q(),array('p'=>$prov,'a'=>$ampur,'t'=>$tambon,'v'=>$village,'qm'=>post('qm'),'pg'=>$nextpage)).'" class="sg-action" data-rel="#report-output"> > </a></li>\')});</script>';


		$tables = new Table();
		$tables->thead=array('no'=>'','ผู้ป้อนข้อมูล','ชื่อ-สกุล','ชุมชน','date regdate'=>'วันที่ป้อน','amt'=>'qt');
		$no=$start-1;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				$rs->name,
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