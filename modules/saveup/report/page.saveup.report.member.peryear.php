<?php
/**
* Saveup :: Report Member Per Year
* Created 2017-04-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/peryear
*/

$debug = true;

function saveup_report_member_peryear($self) {
	$self->theme->title='จำนวนสมาชิกเข้าใหม่แต่ละปี';

	$getYear = post('year');

	if (!$getYear) {
		$stmt='SELECT DATE_FORMAT(date_approve,"%Y") year,count(*) AS total
			FROM %saveup_member% WHERE status="active"
			GROUP BY DATE_FORMAT(date_approve,"%Y")
			ORDER BY date_approve ASC';
		$dbs=mydb::select($stmt);

		$current_year=date('Y');

		$tables = new Table();
		$tables->caption=$self->theme->title;
		$tables->thead=array('year'=>'ปี พ.ศ.','year age'=>'อายุการเป็นสมาชิก (ปี)','amt'=>'จำนวนสมาชิกเข้าใหม่ (คน)');
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a class="sg-action" href="'.url('saveup/report/member/peryear','year='.SG\getFirst($rs->year, 'na')).'" data-rel="box">'.($rs->year?$rs->year+543:'N/A').'</a>',
				$rs->year?$current_year-$rs->year:'N/A',
				$rs->total
			);
			$total+=$rs->total;
		}
		$tables->tfoot[]=array('','รวมทั้งสิ้น',$total);

		$ret .= $tables->build();

		$ret.='<p>หมายเหตุ : รายงานแสดงเฉพาะสมาชิกที่ยังไม่ลาออกเท่านั้น</p>';
		return $ret;
	}

	if ($getYear) {
		$dbs=mydb::select('SELECT `mid`, `date_approve`, `prename`, `firstname`, `lastname` FROM %saveup_member% WHERE YEAR(`date_approve`)=:year ORDER BY `date_approve` ASC',':year',$getYear);

		$tables = new Table();
		$tables->caption='รายชื่อสมาชิกเข้าใหม่ปี '.($getYear+543);
		$tables->thead=array('รหัสสมาชิก','วันที่อนุมัติ','ชื่อ-นามสกุล');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array($rs->mid,sg_date($rs->date_approve,'ว ดดด ปปปป'),'<a href="'.url('saveup/member/view/'.$rs->mid).'">'.$rs->prename.' '.$rs->firstname.' '.$rs->lastname.'</a>');
		}
		$ret .= $tables->build();
	}
	return $ret;
}
?>