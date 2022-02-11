<?php
function project_admin_report_trainer($self, $reportType = NULL) {
	R::View('project.toolbar',$self,'รายงานบันทึกพี่เลี้ยง','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');

	if (post('id')) {
		$ret=__project_admin_report_trainer_detail(post('id'));
		return $ret;
	}

	if (empty($reportType)) {
		$ui=new Ui(NULL,'tabs');
		$ui->add('<a href="'.url('project/admin/report/trainer/record').'">บันทึกติดตามกองทุนของพี่เลี้ยง</a>','{class:"active"}');
		$ui->add('<a href="'.url('project/admin/report/trainer/activity').'">บันทึกกิจกรรมโครงการพี่เลี้ยง</a>');
		$ui->add('<a href="'.url('project/admin/report/trainer/comment').'">บันทึกความคิดเห็นปิดโครงการ</a>');
		$ret.='<nav class="nav sg-tabs">'.$ui->build();
		$ret.='<div>';
	}

	switch ($reportType) {
		case 'activity':
			$ret.=__project_admin_report_trainer_activity();
			break;

		case 'comment':
			$ret.=__project_admin_report_trainer_comment();
			break;
		
		default:
			$ret.=__project_admin_report_trainer_record();
			break;
	}
	if (empty($reportType)) {
		$ret.='</div>';
		$ret.='</nav>';
	}
	return $ret;
}

function __project_admin_report_trainer_record() {
		$stmt='SELECT
						  tr.`trid`
						, tr.`uid`
						, o.`name` orgName
						, u.`username`
						, u.`name` posterName
						, COUNT(*) `totalReport`
					FROM %project_tr% tr
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_org% o ON o.`orgid`=tr.`refid`
					WHERE tr.`formid`="trainer" AND tr.`part`="report"
					GROUP BY `uid`
					ORDER BY `trid` DESC;
					-- {sum:"totalReport"}';
	$dbs=mydb::select($stmt,':uid',$uid);

	$tables = new Table();
	$tables->caption='บันทึกการติดตามกองทุนของพี่เลี้ยง';
	$tables->thead=array('no'=>'','ชื่อพี่เลี้ยง','amt'=>'จำนวนบันทึก','icons'=>'');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											$rs->posterName,
											$rs->totalReport,
											'<a class="sg-action" href="'.url('project/admin/report/trainer',array('id'=>$rs->uid)).'" data-rel="box"><i class="icon -viewdoc"></i></a>',
											);
	}
	$tables->tfoot[]=array('<td></td>','รวม',number_format($dbs->sum->totalReport),'');
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_admin_report_trainer_activity() {
		$stmt='SELECT
						  tr.`trid`
						, tr.`uid`
						, u.`username`
						, u.`name` posterName
						, COUNT(*) `totalReport`
					FROM %project_tr% tr
						RIGHT JOIN %users% u ON u.`uid`=tr.`uid` AND u.`roles`="trainer"
					WHERE tr.`formid`="activity" AND tr.`part`="owner"
					GROUP BY `uid`
					ORDER BY `trid` DESC;
					-- {sum:"totalReport"}';
	$dbs=mydb::select($stmt,':uid',$uid);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->caption='บันทึกกิจกรรมในโครงการพี่เลี้ยง';
	$tables->thead=array('no'=>'','ชื่อพี่เลี้ยง','amt'=>'จำนวนบันทึก','icons'=>'');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											$rs->posterName,
											$rs->totalReport,
											//'<a class="sg-action" href="'.url('project/admin/report/trainer',array('id'=>$rs->uid)).'" data-rel="box"><i class="icon -viewdoc"></i></a>',
											);
	}
	$tables->tfoot[]=array('<td></td>','รวม',number_format($dbs->sum->totalReport),'');
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_admin_report_trainer_comment() {
		$stmt='SELECT
						  tr.`trid`
						, tr.`uid`
						, o.`name` orgName
						, u.`username`
						, u.`name` posterName
						, COUNT(*) `totalReport`
					FROM %project_tr% tr
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_org% o ON o.`orgid`=tr.`refid`
					WHERE tr.`formid`="trainer" AND tr.`part`="comment"
					GROUP BY `uid`
					ORDER BY `trid` DESC;
					-- {sum:"totalReport"}';
	$dbs=mydb::select($stmt,':uid',$uid);

	$tables = new Table();
	$tables->caption='บันทึกความคิดเห็นปิดโครงการ';
	$tables->thead=array('no'=>'','ชื่อพี่เลี้ยง','amt'=>'จำนวนบันทึก','icons'=>'');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											$rs->posterName,
											$rs->totalReport,
											//'<a class="sg-action" href="'.url('project/admin/report/trainer',array('id'=>$rs->uid)).'" data-rel="box"><i class="icon -viewdoc"></i></a>',
											);
	}
	$tables->tfoot[]=array('<td></td>','รวม',number_format($dbs->sum->totalReport),'');
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __project_admin_report_trainer_detail($uid) {
		$stmt='SELECT
						  tr.`trid`
						, tr.`uid`
						, o.`name` orgName
						, u.`username`
						, u.`name` posterName
						, `date1` `reportDate`
						, `rate1` `rate`
						, `detail1` `followfrom`
						, `detail2` `attention`
						, `detail3` `followtype`
						, `text1` `msg`
						, fc.`name` `catName`
						, fp.`name` `catParentName`
						, fs.`name` `rateName`
					FROM %project_tr% tr
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %db_org% o ON o.`orgid`=tr.`refid`
						LEFT JOIN %tag% fc ON tr.`detail1`=fc.`catid` AND fc.`taggroup`="project:followtype"
						LEFT JOIN %tag% fp ON fp.`catid`=fc.`catparent` AND fp.`taggroup`="project:followtype"
						LEFT JOIN %tag% fs ON fs.`catid`=tr.`rate1` AND fs.`taggroup`="project:followstatus"
					WHERE tr.`uid`=:uid AND tr.`formid`="trainer" AND tr.`part`="report"
					ORDER BY `trid` DESC';
	$dbs=mydb::select($stmt,':uid',$uid);
	//$ret.=print_o($dbs,'$dbs');

	$ret.='<ul class="project-report-items">'._NL;
	foreach ($dbs->items as $item) {
		$ret.='<li class="project-report-item">'._NL;
		if ($isEdit) $ret.='<span class="iconset"><a href="'.url('project/trainer/'.$uid.'/post',array('act'=>'delete','trid'=>$item->trid)).'" class="sg-action" data-confirm="ต้องการลบข้อความนี้ กรุณายืนยัน" data-removeparent="li" data-rel="this"><i class="icon -delete"></i></a></span>'._NL;
		$ret.='<div class="poster" style="margin-bottom:10px;"><span class=" owner-photo"><img class="profile owner-photo" src="'.model::user_photo($item->username).'" width="40" height="40" style="display:block;float:left;margin-right:10px;border-radius:50%;" /></span><strong>'.$item->name.'</strong> '.$item->posterName.'<br />@'.sg_date($item->reportDate,'ว ดด ปป').'</div>'._NL;
		$ret.='<div class="clear"><strong>กองทุน : '.$item->orgName.'</strong><br />ชื่อผู้ติดต่อ : '.$item->attention.'<br />ประเภทการติดตาม : '.$item->catName.'<br />ผลการติดตาม : '.$item->rateName.'</div>';
		$ret.='<div class="summary">'.view::inlineedit(array('group'=>'trainer:report','fld'=>'text1', 'tr'=>$item->trid, 'tpid'=>-1, 'ret'=>'html','class'=>'-fill'),$item->msg,$isEdit,'textarea').'</div><br clear="all" />'._NL;

		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;
	return $ret;
}
?>