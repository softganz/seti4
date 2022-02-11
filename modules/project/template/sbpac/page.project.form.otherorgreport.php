<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_otherorgreport($self,$topic,$para,$body,$showForm=true) {
		$tpid=$topic->tpid;
		if (!$tpid) return;
		$isEdit=user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
		$isEdit=($topic->project->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));

		$isAdd=i()->ok;
		$post=(object) post();

		if ($isEdit && (!$topic->project->date_approve || sg_date($topic->project->date_approve,'Y')=='')) {
			$ret.='<p class="notify">กรุณาระบุ <strong>"วันที่อนุมัติ"</strong> โดย<a href="'.url('paper/'.$topic->tpid).'">คลิกที่นี่</a> แล้วแก้ไข "วันที่อนุมัติ" ด้วยค่ะ</p>';
			return $ret;
		}

		if (post('act')=='delete' && post('trid')) {
			$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
			mydb::query($stmt,':trid',post('trid'));
			return 'ลบทิ้งเรียบร้อย';
		} else if ($post->msg) {
			$post->tpid=$tpid;
			$post->formid='report';
			$post->part='owner';
			$post->uid=i()->uid;
			$post->date1=sg_date($post->when,'Y-m-d');
			$post->text1=$post->msg;
			$post->text2=$post->problem;
			$post->text3=$post->recommendation;
			$post->num1=$post->paid;
			$post->num5=$post->percentdone;
			$post->created=date('U');
			$stmt='INSERT INTO %project_tr% (
									`tpid`, `formid`, `part`, `uid`, `date1`, `num1`, `num5`, `text1`, `text2`, `text3`, `created`
									) VALUES (
										:tpid, :formid, :part, :uid, :date1, :num1, :num5, :text1, :text2, :text3, :created
									)';
			mydb::query($stmt,$post);

			//$ret.=mydb()->_query;
			if ($isEdit) $ret.=__project_otherorgreport_form($tpid);
			$ret.=__project_otherorgreport_draw($tpid,$isEdit);
			return $ret;
		}

		if ($isEdit) {
			$inlineAttr['class']='inline-edit';
			$inlineAttr['data-update-url']=url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}
		$ret.='<div id="project-otherorgreport-info" '.sg_implode_attr($inlineAttr).'>'._NL;
		if ($isEdit && $showForm) $ret.=__project_otherorgreport_form($tpid);

		$ret.=__project_otherorgreport_draw($tpid,$isEdit);
		$ret.='</div>'._NL;

		//$ret.=print_o($topic,'$topic');
		//$ret.=print_o($post,'$post').print_o($_FILES,'$_FILES');
		return $ret;
}

function __project_otherorgreport_draw($tpid,$isEdit=false) {
	$stmt='SELECT
						tr.`trid`, tr.`uid`, u.`username`, u.`name` poster,
						`date1` `reportDate`, `num1` `paid`, `num5` `percentdone`
						, `text1` `msg`, `text2` `problem`, `text3` `recommendation`
					FROM %project_tr% tr
					LEFT JOIN %users% u USING(`uid`)
					WHERE `tpid`=:tpid AND `formid`="report" AND `part`="owner"
					ORDER BY `date1` DESC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$ret.='<ul class="project-report-items">'._NL;
	foreach ($dbs->items as $rs) {
		$ret.='<li class="project-report-item">'._NL;
		if ($isEdit) $ret.='<a href="'.url('paper/'.$tpid.'/owner/otherorgreport',array('act'=>'delete','trid'=>$rs->trid)).'" class="sg-action icon-delete" data-confirm="ต้องการลบข้อความนี้ กรุณายืนยัน" data-removeparent="li" data-rel="this">X</a>'._NL;
		$ret.='<div class="poster"><span class="owner-photo"><img class="owner-photo" src="'.model::user_photo($rs->username).'" width="24" alt="'.$rs->poster.'" /></span> '.$rs->poster.' @'.sg_date($rs->reportDate,'ว ดด ปป').'</div>'._NL;
		$ret.='<div class="summary"><h3>ผลการดำเนินงานประจำเดือน '.sg_date($rs->reportDate,'ดดด ปปปป').'</h3>';
		$ret.='<p><strong>จำนวนเงินเบิกจ่ายประจำเดือน '.view::inlineedit(array('group'=>'report:owner','fld'=>'num1', 'tr'=>$rs->trid, 'ret'=>'money', 'value'=>$rs->paid),number_format($rs->paid,2),$isEdit).' บาท ';
		$ret.='ผลการดำเนินงานร้อยละ '.view::inlineedit(array('group'=>'report:owner','fld'=>'num5', 'tr'=>$rs->trid, 'ret'=>'numeric', 'value'=>number_format($rs->percentdone)),number_format($rs->percentdone).'%',$isEdit,'select',array('0'=>'0%','10'=>'10%','20'=>'20%','30'=>'30%','40'=>'40%','50'=>'50%','60'=>'60%','70'=>'70%','80'=>'80%','90'=>'90%','100'=>'100%')).'</strong></p>';
		$ret.=view::inlineedit(array('group'=>'report:owner','fld'=>'text1', 'tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->msg),$rs->msg,$isEdit,'textarea');
		$ret.='<h3>ปัญหา อุปสรรค</h3>'.view::inlineedit(array('group'=>'report:owner','fld'=>'text2', 'tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->problem),$rs->problem,$isEdit,'textarea');
		$ret.='<h3>ข้อเสนอแนะ</h3>'.view::inlineedit(array('group'=>'report:owner','fld'=>'text3', 'tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->recommendation),$rs->recommendation,$isEdit,'textarea');
		$ret.='</div>'._NL;
		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __project_otherorgreport_form($tpid) {
	$startDate=$month=mydb::select('SELECT `date_approve` FROM %project% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->date_approve;
	if (empty($startDate)) $startDate=date('Ym-d');

	$d1 = strtotime($startDate);
	$d2 = strtotime(date('Y-m-d'));
	$min_date = min($d1, $d2);
	$max_date = max($d1, $d2);
	$i = 0;
/*
	do {
		$ret.= sg_date($min_date,'Y-m-t').'<br />';
	} while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date);
*/
	/*
	$begin = new DateTime( $startDate );

	$end = new DateTime( );
	$end = $end->modify( '+0 month' );
	$interval = DateInterval::createFromDateString('1 month');

	$period = new DatePeriod($begin, $interval, $end);
*/
	$ret.='<form id="project-otherorgreport-post" class="xsg-form" method="post" action="'.url('paper/'.$tpid.'/owner/otherorgreport').'" enctype="multipart/form-data" data-rel="#project-otherorgreport-info">'._NL;
	$ret.='<input type="hidden" name="areacode" id="project-otherorgreport-areacode" />'._NL;
	$ret.='<div class="form-item"><label>รายงานประจำเดือน</label><select class="form-select" name="when" id="project-otherorgreport-when">';
	$curMonth=date('Y-m-t');

	do {
		$month=sg_date($min_date,'Y-m-t');
		$ret.= '<option value="'.$month.'"'.($month==$curMonth?' selected="selected"':'').'>'.sg_date($month,'ดดด ปปปป').'</option>';

//		$ret.= sg_date($min_date,'Y-m-d').'<br />';
	} while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date);
/*
	foreach($period as $dt) {
		$month=$dt->format( "Y-m-t" );
		$ret.= '<option value="'.$month.'"'.($month==$curMonth?' selected="selected"':'').'>'.sg_date($month,'ดดด ปปปป').'</option>';
	}
*/
	$ret.='</select></div>'._NL;
	$ret.='<div id="form-project-otherorgreport-msg" class="form-item"><label>ผลการดำเนินงานประจำเดือน</label><textarea id="project-otherorgreport-msg" name="msg" class="form-textarea" rows="3" cols="20" placeholder="ผลการดำเนินงานประจำเดือน"></textarea></div>'._NL;
	$ret.='<div id="form-project-otherorgreport-problem" class="form-item"><label>ปัญหา อุปสรรค</label><textarea id="project-otherorgreport-problem" name="problem" class="form-textarea" rows="3" cols="20" placeholder="ปัญหา อุปสรรค"></textarea></div>'._NL;
	$ret.='<div id="form-project-otherorgreport-s" class="form-item"><label>ข้อเสนอแนะ</label><textarea id="project-otherorgreport-recommendation" name="recommendation" class="form-textarea" rows="3" cols="20" placeholder="ข้อเสนอแนะ"></textarea></div>'._NL;
	$ret.='<div id="form-project-otherorgreport-paid" class="form-item"><label>จำนวนเงินเบิกจ่ายประจำเดือน</label><input type="text" id="project-otherorgreport-paid" name="paid" class="form-text" rows="20" placeholder="0.00"> บาท</div>'._NL;
	$ret.='<div id="form-project-otherorgreport-percentdone" class="form-item"><label>ผลการดำเนินงาน (ร้อยละ)</label><select id="project-otherorgreport-percentdone" name="percentdone" class="form-select"><option value="0">0%</option><option value="10">10%</option><option value="20">20%</option><option value="30">30%</option><option value="40">40%</option><option value="50">50%</option><option value="60">60%</option><option value="70">70%</option><option value="80">80%</option><option value="90">90%</option><option value="100">100%</option></select></div>'._NL;
	$ret.='<div class="form-item"><button id="project-otherorgreport-submit" class="button floating">บันทึก</button></div>'._NL;
	$ret.='</form>'._NL;
	return $ret;
}
?>