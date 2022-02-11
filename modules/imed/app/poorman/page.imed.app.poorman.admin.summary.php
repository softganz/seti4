<?php
function imed_app_poorman_admin_summary($self) {
	R::View('imed.toolbar',$self,'จำนวนแบบสอบถาม','app.poorman');

	$isAdmin=user_access('administer imeds');
	$qtstatusList=R::Model('imed.qt.status');

	$stmt='SELECT `qtstatus`,COUNT(*) `total`
				FROM %qtmast%
				GROUP BY `qtstatus`;
				-- {sum:"total"}';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('center'=>'สถานะ','amt'=>'จำนวนแบบสอบถาม');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<a href="'.url('imed/app/poorman/admin/summary',array('status'=>$rs->qtstatus)).'">'.$qtstatusList[$rs->qtstatus].'</a>',
											$rs->total
											);
	}
	$tables->tfoot[]=array('รวม',$dbs->sum->total);
	$ret.=$tables->build();

	if (post('status')!='') {
		mydb::where('q.`qtstatus`=:status',':status',post('status'));
		if (!$isAdmin) mydb::where('q.`uid`=:uid',':uid',i()->uid);
		$stmt='SELECT
						q.`qtref`, q.`qtstatus`
					, p.`psnid`, CONCAT(p.`name`," ",p.`lname`) `fullname`
					, cop.`provname`
					, q.`created`
					, q.`uid`, u.`name` `poster`
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %users% u ON u.`uid`=q.`uid`
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					%WHERE%
					ORDER BY `qtref` DESC;
					';
		$dbs=mydb::select($stmt);
		$tables = new Table();
		$tables->thead=array('no'=>'ลำดับ','ชื่อ นามสกุล','จังหวัด','center -status'=>'สถานะ','');
		foreach ($dbs->items as $rs) {
			$isEdit=($isAdmin || i()->uid==$rs->uid) && $rs->qtstatus<_COMPLETE;
			$tables->rows[]=array(
												++$no,
												'<a href="'.url('imed/app/poorman/form/'.$rs->qtref).'">'.$rs->fullname.'</a>'
												.'<br /><span class="timestamp">โดย '.$rs->poster
												.' @'.sg_date($rs->created,'ว ดด ปป H:i')
												.'</span>',
												$rs->provname,
												$qtstatusList[$rs->qtstatus],
												$isEdit?'<a class="sg-action" href="'.url('imed/poorman/edit/'.$rs->qtref.'/cancel').'" data-rel="box" title="ยกเลิกแบบสอบถาม"><i class="icon -cancel"></i></a>':'',
												);
		}
		$ret.=$tables->build();
	}
	//$ret.=print_o($dbs);
	return $ret;
}
?>