<?php

	/**
	 * Org report รายชื่อสมาชิกในพื้นที่
	 *
	 */
	function org_report_issue($self) {
		$para=para(func_get_args(),1);
		$self->theme->title='รายงานจำนวนคนเข้าร่วมกิจกรรมแยกตามประเด็น';

		$year=mydb::select('SELECT MIN(YEAR(`joindate`)) fromyear ,  MAX(YEAR(`joindate`)) toyear FROM %org_mjoin% LIMIT 1');

		$stmt='SELECT COUNT(*) regs ,
							tg.`tid` issueid, tg.`name` issue ,
							YEAR(FROM_UNIXTIME(d.`atdate`)) year
						FROM %org_dos% do
							LEFT JOIN %org_doings% d ON d.`doid`=do.`doid`
							LEFT JOIN %tag% tg ON tg.`tid`=d.`issue`
						GROUP BY d.`issue` , YEAR(FROM_UNIXTIME(d.`atdate`))
						ORDER BY issue';
		$dbs=mydb::select($stmt);
		//$ret.=print_o($dbs,'$dbs');

		$totals=0;
		$total=array();
		$issue=array();

		$tables = new Table();
		$tables->addClass('item--1');
		$tables->caption=$self->theme->title;
		$tables->thead='<thead><tr><th rowspan="2">ประเด็น</th><th colspan="'.($year->toyear-$year->fromyear+1).'">จำนวนคน</th><th rowspan="2">รวม</th></tr><tr>';
		$tables->colgroup['name']='';
		for ($i=$year->fromyear;$i<=$year->toyear;$i++) {
			$tables->thead.='<th>'.($i+543).'</th>';
			$tables->colgrpup['amt amt-'.$i]='';
		}
		$tables->colgroup['amt amt-total']='';
		$tables->thead.='</tr></thead>';
		foreach ($dbs->items as $rs) {
			$key=$rs->issue?$rs->issue:'ไม่ระบุ';
			$issue[$key]['id']=$rs->issueid;
			$issue[$key][$rs->year]+=$rs->regs;
			$total[$rs->year]+=$rs->regs;
			$totals+=$rs->regs;
		}
		//$ret.=print_o($issue,'$issue');
		foreach ($issue as $name=>$rs) {
			$rows=array('<a href="'.url('org/report/issue',array('issue'=>$rs['id'])).'">'.$name.'</a>');
			$subtotal=0;
			for ($i=$year->fromyear;$i<=$year->toyear;$i++) {
				$rows[]=$issue[$name][$i]?number_format($issue[$name][$i]):'-';
				$subtotal+=$issue[$name][$i];
			}
			$rows[]=$subtotal?number_format($subtotal):'';
			$tables->rows[]=$rows;
		}
		$rows=array('รวม');
		for ($i=$year->fromyear;$i<=$year->toyear;$i++) $rows[]=$total[$i]?number_format($total[$i]):'-';
		$rows[]=$totals?number_format($totals):'';
		$tables->tfoot[]=$rows;

		$ret .= $tables->build();
		if (post('issue')) {
			$ret.='กิจกรรมของประเด็น';
			$stmt='SELECT d.*, COUNT(*) totals
							FROM %org_doings% d
								LEFT JOIN %org_dos% do USING(`doid`)
							WHERE `issue`=:issue
							GROUP BY d.`doid`
							ORDER BY `atdate` DESC';
			$dbs=mydb::select($stmt,':issue',post('issue'));

			$tables = new Table();
			$tables->thead=array('no'=>'','date'=>'วันที่','กิจกรรม','amt'=>'จำนวนผู้เข้าร่วม');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(++$no,sg_date($rs->atdate,'ว ดด ปป'),$rs->doings,number_format($rs->totals));
			}
			$ret .= $tables->build();
			//$ret.=print_o($dbs,'$dbs');
		}
		$ret.='<style type="text/css">.item--1 td {text-align:right;} .item--1 td:first-child {text-align:left;}</style>';

		return $ret;
	}
?>