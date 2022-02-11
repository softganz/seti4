<?php
	/**
	 * OrgDg report รายชื่อสมาชิกในพื้นที่
	 *
	 */
	function org_report_project($self,$tpid=NULL,$action=NULL) {
		$title='จำนวนกิจกรรมของโครงการ';

		$self->theme->title=$title;

		$year=post('y');


		switch ($action) {
			case 'activity':
				$ret.='<h2>กิจกรรมโครงการ</h2>';
				$where=array();
				$where=sg::add_condition($where,'`tpid`=:tpid','tpid',$tpid);
				if ($year) $where=sg::add_condition($where,'YEAR(FROM_UNIXTIME(`atdate`))=:year','year',$year);
				$stmt='SELECT d.*, COUNT(DISTINCT `psnid`) `amt`
								FROM %org_doings% d
									LEFT JOIN %org_dos% dos ON dos.`doid`=d.`doid` AND dos.`isjoin`>0
								'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
								GROUP BY `doid` ORDER BY `atdate` ASC';
				$dbs=mydb::select($stmt,$where['value']);
				$tables = new Table();
				$tables->thead=array('date'=>'วันที่','ชื่อกิจกรรม','amt'=>'จำนวนผู้เข้าร่วม');
				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(sg_date($rs->atdate,'ว ดด ปปปป'),'<a href="'.url('org/'.$rs->orgid.'/meeting.info/'.$rs->doid).'">'.$rs->doings.'</a>',$rs->amt);
				}
				$ret.=$tables->build();
				return $ret;
				break;
			
			default:
				# code...
				break;
		}

		$projectName='';

		$years=mydb::select('SELECT DISTINCT YEAR(FROM_UNIXTIME(`atdate`)) `year` FROM %org_doings% HAVING `year` IS NOT NULL ORDER BY `year` ASC');

		$ret.='<form method="get" action="">';
		if ($tpid) $ret.='<input type="hidden" name="tpid" value="'.$tpid.'" />';
		$ret.='<label>ปี พ.ศ. </label><select class="form-select" name="y"><option value="">ทุกปี</option>';
		foreach ($years->items as $item) $ret.='<option value="'.$item->year.'" '.($year==$item->year?'selected="selected"':'').'>'.($item->year+543).'</option>';
		$ret.='</select> ';
		$ret.='<button class="btn -primary" type="submit"><span>ดูรายงาน</span></button>';
		$ret.='</form>';

		$where=array();
		$where=sg::add_condition($where,'d.`tpid` IS NOT NULL');
		if ($year) $where=sg::add_condition($where,'YEAR(FROM_UNIXTIME(`atdate`))=:year','year',$year);

		$stmt='SELECT d.`tpid` , t.`title`
						, COUNT(DISTINCT d.`doid`) activitys
						, COUNT(DISTINCT `psnid`) persons
						, COUNT(`isjoin`) allPersons
						FROM %org_doings% d
							LEFT JOIN %topic% t USING(`tpid`)
							LEFT JOIN %org_dos% do ON do.`doid`=d.`doid` AND do.`isjoin`>0
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY d.`tpid` ';
		$dbs=mydb::select($stmt,$where['value']);
		//$ret.=print_o($dbs,'$dbs');

		$tables = new Table();
		$tables->thead=array('no'=>'ลำดับ','โครงการ','amt activity'=>'จำนวนกิจกรรม','amt person'=>'จำนวนคน','amt allperson'=>'จำนวนผู้เข้าร่วม (คน)');
		$no=0;
		foreach ($dbs->items as $rs) {
			if ($tpid && $rs->tpid==$tpid) $projectName=$rs->title;
			$tables->rows[]=array(
																++$no,
																'<a href="'.url('org/report/project/'.$rs->tpid,array('y'=>$year)).'">'.SG\getFirst($rs->title,'ไม่ระบุ').'</a>',
																'<a class="sg-action" href="'.url('org/report/project/'.$rs->tpid.'/activity',array('y'=>$year)).'" data-rel="box">'.number_format($rs->activitys).'</a>',
																number_format($rs->persons),
																number_format($rs->allPersons),
																);
		}
		$ret.=$tables->build();

		if ($tpid) $where=sg::add_condition($where,'t.`tpid`=:tpid','tpid',$tpid);
		$stmt='SELECT CASE
							WHEN `joins`<=5 THEN "  <= 5 ครั้ง"
							WHEN `joins`<=10 THEN " 6 - 10 ครั้ง"
							WHEN `joins`<=20 THEN "11 - 20 ครั้ง"
							WHEN `joins`<=50 THEN "21 - 50 ครั้ง"
							WHEN `joins`<=70 THEN "51 - 70 ครั้ง"
							WHEN `joins`<=100 THEN "71 - 100 ครั้ง"
							ELSE ">100 ครั้ง"
						END `label`, COUNT(`joins`) amt
						FROM
						( SELECT do.`psnid`,COUNT(*) joins
						FROM %org_doings% d
							LEFT JOIN %topic% t USING(`tpid`)
							LEFT JOIN %org_dos% do USING(`doid`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY do.`psnid`
						) a
						GROUP BY `label`';
		$dbs=mydb::select($stmt,$where['value']);

		$total=0;
		foreach ($dbs->items as $rs) $total+=$rs->amt;

		if ($projectName) $ret.='<h3>'.$projectName.'</h3>';
		$tables = new Table();
		$tables->thead=array('จำนวนครั้งเข้าร่วม','amt person'=>'จำนวนคน','amt %'=>'%');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array($rs->label,number_format($rs->amt),number_format($rs->amt*100/$total,2));
		}
		$ret.=$tables->build();
		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}
?>