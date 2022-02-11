<?php
function imed_admin_visit($self) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>บันทึกการเยี่ยมบ้าน</h3></header>';
	$user=post('u');

	if (empty($user)) {
		$stmt='SELECT
				s.`uid`,u.`username`, u.`name`, COUNT(*) `total`
			,  FROM_UNIXTIME(SUBSTRING_INDEX(GROUP_CONCAT(CAST(created AS CHAR) ORDER BY created DESC), ",", 1),"%Y-%m-%d %H:%i:%s")  AS `created_date`
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
			GROUP BY s.`uid`
			ORDER BY `created_date` DESC
			';
		$dbs=mydb::select($stmt);

		$tables = new Table();
		$tables->thead=array('ผู้เยี่ยม','amt'=>'จำนวนครั้ง','date'=>'ล่าสุด');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				'<a class="sg-action" href="'.url('imed/admin/visit',array('u'=>$rs->uid)).'" data-rel="box">'.$rs->name.'</a>',
				$rs->total,
				$rs->created_date
			);
		}
		$ret.=$tables->build();
		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}

	mydb::where('s.`uid`=:uid',':uid',$user);
	$stmt='SELECT
			s.*, u.`username`, u.`name`
		, CONCAT(p.`name`," ",p.`lname`) patientName
		, cosd.`subdistname`, codist.`distname`, copv.`provname`
		FROM %imed_service% s
			LEFT JOIN %users% u USING (uid)
			LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
			LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		ORDER BY seq DESC
		';
	$dbs=mydb::select($stmt);
	
	$tables = new Table();
	$tables->class='item';
	$tables->thead=array('no'=>'Seq','poster'=>'Poster','Partien/Description','date'=>'Created');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->seq,
			'<a class="sg-action" href="'.url('imed/u/'.$rs->uid).'" data-rel="box">'.$rs->name.'</a>',
			'<a href="'.url('imed','pid='.$rs->pid).'" target="_blank"><strong>'.$rs->patientName.'</strong></a><br />ต.'.$rs->subdistname.' อ.'.$rs->distname.' จ.'.$rs->provname.'<p><img src="'.imed_model::patient_photo($rs->pid).'" class="patient-photo left" width="48" />'.$rs->rx.'</p>',
			date('Y-m-d H:i',$rs->created)
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>