<?php

	/**
	 * iMed report วันหมดอายุบัตร
	 *
	 */
	function imed_report_addqtbyme($self) {
		$isAdmin=user_access('administer imeds');

		$self->theme->title='รายชื่อคนพิการป้อนแบบสอบถาม';
		$ret.='<h3>รายชื่อคนพิการป้อนแบบสอบถาม</h3>';

		$ret.='<div id="report-output">';

		$where=array();
		//		$where=sg::add_condition($where,'d.pid IS NOT NULL');
		$where=sg::add_condition($where,'q.`ucreated`=:me OR q.`umodify`=:me','me',i()->uid);
		/*
		if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
		if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
		if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
		if ($village) $where=sg::add_condition($where,'p.`village`=:village','village',$village);
*/
		$stmt='SELECT SQL_CALC_FOUND_ROWS
							  q.`pid`, CONCAT(p.`name`," ", p.`lname`) fullname
							, q.`ucreated`, q.`dcreated`
							, u.`username`, u.`name`
							, COUNT(*) qtTotal
							, p.`commune`
							, p.`house`, cosd.`subdistname`, codist.`distname`, copv.`provname`
						FROM %imed_qt% q
						LEFT JOIN %users% u ON u.`uid`=q.`ucreated`
						LEFT JOIN sgz_db_person p ON p.`psnid`=q.`pid`
						LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
						LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY q.`pid`
						ORDER BY CONVERT(fullname USING tis620) ASC';

		$dbs=mydb::select($stmt,$where['value']);
		//$ret.=print_o($dbs,'$dbs');

		$totals = $dbs->_found_rows;

		$start=$items*($page-1)+1;
		$end=$start+$dbs->_num_rows-1;
		$prevpage=$page>1?$page-1:1;
		$nextpage=$dbs->_num_rows==$items?$page+1:$page;
		//$ret.='<script>$(document).ready(function(){$(".pagenv").html(\'<li>รายการ '.$start.' - '.$end.' จาก '.number_format($totals).'</li><li><a href="'.url(q(),array('p'=>$prov,'a'=>$ampur,'t'=>$tambon,'v'=>$village,'qm'=>post('qm'),'pg'=>$prevpage)).'" class="sg-action" data-rel="#report-output"> < </a></li><li><a href="'.url(q(),array('p'=>$prov,'a'=>$ampur,'t'=>$tambon,'v'=>$village,'qm'=>post('qm'),'pg'=>$nextpage)).'" class="sg-action" data-rel="#report-output"> > </a></li>\')});</script>';

		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อ-สกุล','ชุมชน','date regdate'=>'วันที่ป้อน','amt'=>'qt');
		$no=$start-1;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				'<a class="" href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.SG\getFirst($rs->fullname,'...').'</a><br />'.SG\implode_address($rs,'short'),
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