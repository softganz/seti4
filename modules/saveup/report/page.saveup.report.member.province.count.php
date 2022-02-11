<?php
/**
* Saveup :: Report Member Province
* Created 2017-04-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/province/count
*/

$debug = true;

function saveup_report_member_province_count($self) {
	$prov = post('prov');

	if (!$prov) {
		$stmt='SELECT province,count(*) AS total
			FROM %saveup_member%
			WHERE firstname IS NOT NULL AND status="active"
			GROUP BY province
			ORDER BY IF(`province`="",1,0),total DESC';

		$reports=mydb::select($stmt);

		$tables = new Table();
		$tables->addClass('saveup-report-main');
		$self->theme->title='จำนวนสมาชิกแต่ละจังหวัด';
		$tables->caption='จำนวนสมาชิกแต่ละจังหวัด';
		$tables->thead=array('จังหวัด','amt'=>'จำนวนคน','icons -c1'=>'');
		$total=$no=0;
		foreach ($reports->items as $rs) {
			$tables->rows[]=array(
				'<a class="sg-action" href="'.url(q(),'prov='.SG\getFirst($rs->province,'na')).'" data-rel="box">'.SG\getFirst($rs->province,'ไม่ระบุ').'</a>',
				$rs->total,
				'<a class="sg-action" href="'.url(q(),'prov='.SG\getFirst($rs->province,'na')).'" data-rel="box"><i class="icon -view"></i></a>'
			);
			$total+=$rs->total;
		}
		$tables->tfoot[]=array('รวม',$total,'');

		$ret .= $tables->build();
		return $ret;
	}

	if ($prov) {
		if ($prov=='na') $prov='';
		$stmt='SELECT *
			FROM %saveup_member%
			WHERE province=:province AND status="active"
			ORDER BY amphure ASC,firstname ASC';
		$dbs=mydb::select($stmt,':province',$prov);

		$tables = new Table();
		$tables->addClass('saveup-report-detail');
		$tables->caption='รายชื่อสมาชิกในจังหวัด';
		$tables->thead=array('ID','ชื่อ-สกุล','ที่อยู่','อำเภอ','จังหวัด');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array($rs->mid,'<a href="'.url('saveup/member/view/'.$rs->mid).'">'.$rs->firstname.' '.$rs->lastname.'</a>',$rs->address,$rs->amphure,$rs->province);
		}

		$ret .= $tables->build();
	}
	return $ret;
}
?>