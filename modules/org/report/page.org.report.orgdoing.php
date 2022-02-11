<?php

	/**
	 * OrgDg report รายชื่อสมาชิกในพื้นที่
	 *
	 */
	function org_report_orgdoing($self,$orgid=NULL,$action=NULL) {
		$title='จำนวนกิจกรรมขององค์กร';

		$self->theme->title=$title;

		$year=post('y');

		switch ($action) {
			case 'activity':
				$ret.='<h2>กิจกรรมโครงการ</h2>';
				$where=array();
				$where=sg::add_condition($where,'`orgid`=:orgid','orgid',$orgid);
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

		$orgName='';

		$years=mydb::select('SELECT DISTINCT YEAR(FROM_UNIXTIME(`atdate`)) `year` FROM %org_doings% HAVING `year` IS NOT NULL ORDER BY `year` ASC');

		$ret.='<form method="get" action="">';
		if ($orgid) $ret.='<input type="hidden" name="org" value="'.$orgid.'" />';
		$ret.='<label>ปี พ.ศ. </label><select class="form-select" name="y"><option value="">ทุกปี</option>';
		foreach ($years->items as $item) $ret.='<option value="'.$item->year.'" '.($year==$item->year?'selected="selected"':'').'>'.($item->year+543).'</option>';
		$ret.='</select> ';
		$ret.='<button class="btn -primary" type="submit"><span>ดูรายงาน</span></button>';
		$ret.='</form>';

		$where=array();
		if ($year) $where=sg::add_condition($where,'YEAR(FROM_UNIXTIME(`atdate`))=:year','year',$year);
		$stmt='SELECT d.`orgid` , o.`name`
						, COUNT(DISTINCT d.`doid`) activitys
						, COUNT(DISTINCT `psnid`) persons
						, COUNT(`isjoin`) allPersons
						FROM %org_doings% d
							LEFT JOIN %db_org% o USING ( `orgid` )
							LEFT JOIN %org_dos% do ON do.`doid`=d.`doid` AND do.`isjoin`>0
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY `orgid`;
						-- {sum:"activitys,persons,allPersons"}';
		$dbs=mydb::select($stmt,$where['value']);
		//$ret.=print_o($dbs,'$dbs');

		$tablesName = new Table();
		$tablesName->thead=array('no'=>'ลำดับ','องค์กร','amt activity'=>'จำนวนกิจกรรม(ครั้ง)','amt person'=>'จำนวนคน(คน)','amt allperson'=>'จำนวนผู้เข้าร่วม(คน)');
		$no=0;
		foreach ($dbs->items as $rs) {
			if ($orgid && $rs->orgid==$orgid) $orgName=$rs->name;
			$tablesName->rows[]=array(
																++$no,
																'<a href="'.url('org/report/orgdoing/'.$rs->orgid,array('y'=>$year)).'">'.$rs->name.'</a>',
																'<a class="sg-action" href="'.url('org/report/orgdoing/'.$rs->orgid.'/activity',array('y'=>$year)).'" data-rel="box">'.number_format($rs->activitys).'</a>',
																number_format($rs->persons),
																number_format($rs->allPersons),
																);
		}
		$tablesName->tfoot[]=array(
												'',
												'รวม',
												number_format($dbs->sum->activitys),
												number_format($dbs->sum->persons),
												number_format($dbs->sum->allPersons)
												);
		$ret .= $tablesName->build();

		if ($orgid) $where=sg::add_condition($where,'d.`orgid`=:orgid','orgid',$orgid);

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
							LEFT JOIN %db_org% o USING ( `orgid` )
							LEFT JOIN %org_dos% do ON do.`doid`=d.`doid` AND do.`isjoin`>0
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY do.`psnid`
						) a
						GROUP BY `label`;
						-- {sum:"amt"}';
		$dbs=mydb::select($stmt,$where['value']);
		$total=0;
		foreach ($dbs->items as $rs) $total+=$rs->amt;

		if ($orgName) $ret.='<h3>'.$orgName.'</h3>';
		$tables = new Table();
		$tables->thead=array('จำนวนครั้งเข้าร่วม','amt person'=>'จำนวนคน(คน)','amt %'=>'เปอร์เซ็นต์ (%)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												$rs->label,
												number_format($rs->amt),
												number_format($rs->amt*100/$total,2)
												);
		}
		$tables->tfoot[]=array(
												'รวม',
												number_format($dbs->sum->amt),
												''
												);
		$ret.=$tables->build();

		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}
?>