<?php
function project_trainer($self,$uid=NULL,$action=NULL,$tr=NULL) {
	R::view('project.toolbar',$self,'พี่เลี้ยงกองทุนสุขภาพตำบล','fund');

	if (!$uid) return __project_trainer_home();

	$iuid=i()->uid;
	// สิทธิในการดูรายงานพี่เลี้ยง admin,เจ้าหน้าที่กองทุน,พี่เลี้ยงกองทุน
	$isRight=false;
	$isEdit=false;

	$stmt='SELECT * FROM %org_officer% WHERE `orgid` IN (SELECT `orgid` FROM %org_officer% WHERE `uid`=:uid AND `membership`="trainer");-- {key:"uid"}';
	$officers=mydb::select($stmt,':uid',$uid)->items;
	//$ret.='i='.$iuid.print_o($officers,'officers');
	if (user_access('administer projects')) {
		$isEdit=$isRight=true;
		//$ret.='I am is Admin';
	} else if ($iuid && array_key_exists($iuid, $officers) && $officers[$iuid]->membership=='trainer') {
		$isEdit=$isRight=true;
		//$ret.='I am is Owner';
	} else if ($iuid && array_key_exists($iuid, $officers)) {
		$isRight=true;
		//$ret.='I am is Officer';
	}
	switch ($action) {
		case 'fund' :
			$ret.=__project_trainer_fund($uid);
			$ret.=__project_trainer_project($tr);
			break;

		case 'report' :
			$ret.=__project_trainer_report($uid,$isRight,$isEdit);
			break;

		case 'post' :
			$ret.=__project_trainer_post($uid,$isRight,$isEdit);
			return $ret;
			break;

		case 'edit':
			$ret=array();
			$post=post();
			$post['uid']=$uid;
			$stmt='UPDATE %project_tr% SET `text1`=:value WHERE `trid`=:tr AND `uid`=:uid AND `formid`="trainer" AND `part`="report" LIMIT 1';
			mydb::query($stmt,$post);
			$ret['debug']=mydb()->_query;
			$ret['debug'].=print_o(post(),'post()');
			$ret['value']=sg_text2html($post['value']);
			$ret['msg']='บันทึกเรียบร้อย';
			return $ret;
			break;

		default :
			$ret.='<div class="project-trainer -report">'.__project_trainer_report($uid,$isRight,$isEdit).'</div>';
			$ret.='<div class="project-trainer -fund">'.__project_trainer_fund($uid).'</div>';

			break;
	}

	if ($fundid) {

		//$ret.=print_o($dbs,'$dbs');
	}
	$ret.='<style type="text/css">
	@media (min-width:40em){
		.project-trainer.-report {width:50%; float:left;}
		.project-trainer.-fund {margin-left:52%;}
	}
	</style>';
	return $ret;
}

function __project_trainer_home() {
	$ret.='<h3>Trainer home page</h3>';
	return $ret;
}

function __project_trainer_fund($uid) {
	$stmt='SELECT of.*, o.`orgid`, o.`name` , o.`shortname` `fundid` FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid`=:uid AND of.`membership`="trainer"';
	$dbs=mydb::select($stmt,':uid',$uid);

	foreach ($dbs->items as $rs) {
		$ret.='<h3><a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->name.'</a></h3>';
		// Show fund project
		$stmt='SELECT t.`tpid`, t.`title`, p.`budget`, SUM(pd.`amount`) `totalPaid`
			FROM %topic% t
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %project_paiddoc% pd USING(`tpid`)
			WHERE t.`type`="project" AND `orgid`=:orgid
			GROUP BY `tpid`
			ORDER BY `tpid` DESC;
			-- {sum:"budget,totalPaid"}';
		$dbs=mydb::select($stmt,':orgid',$rs->orgid);
		$tables = new Table();
		$tables->thead=array('โครงการ','money -budget'=>'งบประมาณ','money -balance'=>'คงเหลือ');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
				number_format($rs->budget,2),
				number_format($rs->budget-$rs->totalPaid,2),
			);
		}
		$tables->tfoot[]=array('รวม '.$dbs->_num_rows.' โครงการ',number_format($dbs->sum->budget,2),number_format($dbs->sum->totalPaid,2));
		$ret.=$tables->build();
	}
	return $ret;
}

function __project_trainer_project($orgId) {
	$fundInfo = R::Model('project.fund.get',$orgId);
	//project_model::set_toolbar($self,'พี่เลี้ยงกองทุนสุขภาพตำบล',$fundInfo);
	$ret.='<h3>'.$fundInfo->name.'</h3>';
	$ret.='<h3>บันทึกพี่เลี้ยง</h3>';

	$ret.='<h3>โครงการในกองทุน</h3>';
	$stmt='SELECT p.`tpid`, p.`budget`, t.`title`, o.`name`, SUM(pd.`amount`) `totalPaid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_paiddoc% pd USING(`tpid`)
		WHERE t.`orgid`=:orgid
		GROUP BY `tpid`
		ORDER BY `tpid` DESC;
		-- {sum:"budget,totalPaid"}';
	$dbs=mydb::select($stmt,':orgid',$fundInfo->orgid);
	$tables = new Table();
	$tables->thead=array('no'=>'','โครงการ','money -budget'=>'งบประมาณ','money -balance'=>'คงเหลือ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a><br />'.$rs->name,
			number_format($rs->budget,2),
			number_format($rs->budget-$rs->totalPaid,2),
		);
	}
	$tables->tfoot[]=array('<td></td>','รวม '.$dbs->_num_rows.' โครงการ',number_format($dbs->sum->budget,2),number_format($dbs->sum->totalPaid,2));
	$ret.=$tables->build();
	return $ret;
}

function __project_trainer_report($uid,$isRight,$isEdit) {
	if (!$isRight) return '';
	$ret.='<h3>บันทึกการติดตามของพี่เลี้ยง</h3>';

	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/trainer/'.$uid.'/edit');
		if (post('debug')) $inlineAttr['data-debug']='yes';
		//head('<script>tpid=null;</script>');
	}
	$ret.='<div id="project-trainerreport-info" '.sg_implode_attr($inlineAttr).'>'._NL;
	if ($isEdit) $ret.=__project_trainer_form($uid);
	$ret.=__project_trainer_draw($uid,$isRight,$isEdit);
	$ret.='</div>'._NL;
	return $ret;
}

function __project_trainer_post($uid,$isRight,$isEdit) {
	$post=(object) post('data');

	if (post('act')=='delete' && post('trid')) {
		$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid AND `uid`=:uid AND `formid`="trainer" AND `part`="report" LIMIT 1';
		mydb::query($stmt,':trid', post('trid'), ':uid',$uid);
		return 'ลบทิ้งเรียบร้อย'.mydb()->_query;
	} else if ($post->followtype || $post->msg) {
		$post->tpid=$tpid;
		$post->refid=$post->orgid;
		$post->formid='trainer';
		$post->part='report';
		$post->uid=i()->uid;
		$post->rate=SG\getFirst($post->rate,NULL);
		$post->when=sg_date($post->when,'Y-m-d');
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
				(`tpid`, `refid`, `formid`, `part`, `uid`, `date1`, `detail1`, `detail2`, `rate1`, `text1`, `created`)
			VALUES
				(:tpid, :refid, :formid, :part, :uid, :when, :followtype, :attention, :rate, :msg, :created)';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.print_o($post,'$post');
		if ($isEdit) $ret.=__project_trainer_form($uid,$isRight,$isEdit);
		$ret.=__project_trainer_draw($uid,$isRight,$isEdit);
		return $ret;
	}

	return $ret;
}

function __project_trainer_form($uid) {
	$followCate=model::get_category_by_group('project:followtype','catid');

	$fundList=mydb::select('SELECT of.*,o.`name` FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid`=:uid AND of.`membership`="trainer"',
		':uid',i()->uid
	);

	$form = new Form([
		'variable' => 'data',
		'action' => url('project/trainer/'.$uid.'/post'),
		'id' => 'project-trainerreport-post',
		'class' => 'sg-form -no-print',
		'rel' => '#project-trainerreport-info',
		'children' => [
			'orgid' => [
				'type' => $fundList->_num_rows<=5?'radio':'select',
				'label' => 'ชื่อกองทุน :',
				'options' => (function($fundList) {
					$options = [];
					if ($fundList->_num_rows>5) $options['']='===เลือกกองทุน===';
					foreach ($fundList->items as $item) $options[$item->orgid]=$item->name;
					return $options;
				})($fundList),
			],
			'followtype' => [
				'type' => 'hidden',
				'label' => 'ประเภทการติดตาม :',
				'require' => true,
				'options' => array_merge(array(''=>'===เลือกประเภทการติดตาม==='),$followCate),
			],
			'attention' => [
				'type' => 'text',
				'label' => 'ชื่อผู้ที่ติดต่อด้วย',
				'class' => '-fill',
				'placeholder' => 'ระบุชื่อผู้ที่ติดต่อด้วย',
			],

			/*
			$form->followfrom->type='textfield';
			$form->followfrom->label='ผลการติดตาม :';
			$form->followfrom->require=true;
			$form->followfrom->pretext='<span>เอกสารสัญญาโครงการ : </span><br />
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="21"> ได้รับเอกสารเรียบร้อย
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="22"> เอกสารไม่เรียบร้อย<br />
			<span>การส่งรายงานและกระบวนการเบิกจ่าย :</span><br />
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="31"> ได้รับรายงานแล้ว
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="32"> อยู่ระหว่างการตรวจรายงาน
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="33"> อยู่ระหว่างการตั้งเบิกจ่าย
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="34"> โอนเงินงวดแล้ว<br />
			<span>อื่น ๆ :</span><br />
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="11"> เรียบร้อย
			<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="12"> ต้องแก้ไข'._NL;
			//$form->followfrom->options=
			*/
			'msg' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดการติดตาม',
				'rows' => 3,
				'class' => '-fill',
				'placeholder' => 'รายละเอียดเหตุการณ์และผลสรุปที่สำคัญของการติดตาม',
			],
			'when' => [
				'type' => 'text',
				'label' => 'วันที่ติดตาม',
				'class' => 'sg-datepicker',
				'value' => date('Y-m-d'),
			],
			'save' => [
				'type' => 'button',
				'value' => 'โพสท์',
			],
		],
	]);

	$ret .= $form->build();

	return $ret;
}

function __project_trainer_draw($uid,$isRight=false,$isEdit=false) {
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