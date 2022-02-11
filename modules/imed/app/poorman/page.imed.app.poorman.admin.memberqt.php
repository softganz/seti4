<?php
function imed_app_poorman_admin_memberqt($self) {
	$paraUser = post('u');

	R::View('imed.toolbar',$self,'แบบสอบถามของสมาชิก','app.poorman');

	$isAdmin=user_access('administer imeds');
	if (!$isAdmin) return message('error', 'access denied');

	$formId = 4;		// แบบฟอร์ม ศปจ.
	$qtstatusList=R::Model('imed.qt.status');

	if (!$paraUser) {
		$stmt='SELECT `uid`, `name`, COUNT(*) `total`
					FROM %qtmast% q
						LEFT JOIN %users% u USING(`uid`)
					WHERE `qtform` = :qtform
					GROUP BY `uid`
					ORDER BY CONVERT(`name` USING tis620) ASC;
					-- {sum:"total"}';
		$dbs=mydb::select($stmt, ':qtform', $formId);

		$tables = new Table();
		$tables->thead=array('ชื่อสมาชิก','amt'=>'จำนวน');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												'<a class="sg-action" href="'.url('imed/app/poorman/admin/memberqt',array('u'=>$rs->uid)).'" data-rel="#main" data-webview="'.htmlspecialchars($rs->name).'">'.$rs->name.'</a>',
												$rs->total
												);
		}
		$tables->tfoot[]=array('รวม',$dbs->sum->total);

		$self->theme->sidebar = $tables->build();
	}

	if ($paraUser) {
		mydb::where('q.`qtform` = :qtform', ':qtform', $formId);
		mydb::where('q.`uid` = :uid', ':uid', $paraUser);
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
		$tables->thead=array('no'=>'ลำดับ','ชื่อ นามสกุล','จังหวัด','center -status'=>'สถานะ');
		foreach ($dbs->items as $rs) {
			$isEdit=($isAdmin || i()->uid==$rs->uid) && $rs->qtstatus<_COMPLETE;
			$tables->rows[]=array(
												++$no,
												'<a class="sg-action" href="'.url('imed/app/poorman/form/'.$rs->qtref).'">'.$rs->fullname.'</a>'
												.'<br /><span class="timestamp">โดย '.$rs->poster
												.' @'.sg_date($rs->created,'ว ดด ปป H:i')
												.'</span>',
												$rs->provname,
												$qtstatusList[$rs->qtstatus],
												);
		}
		$ret.=$tables->build();
	}
	//$ret.=print_o($dbs);

	$ret .= '<style type="text/css">
	@media (min-width: 40em) {
		.sidebar {position: fixed; height: 100%; overflow: scroll;}
	}
	</style>';
	return $ret;
}
?>