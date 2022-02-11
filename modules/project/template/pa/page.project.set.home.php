<?php
function project_set_home($self,$tpid=NULL,$action=NULL,$trid=NULL) {
	if ($tpid)
		R::Module('project.template',$self,$tpid);

	$projectInfo = R::Model('project.get',$tpid);

	R::View('project.toolbar',$self,$projectInfo->title,'set',$projectInfo);

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	$img = 'calendar.png';
	if (in_array($tpid, array(20,21,22,23))) $img='pa-plan-'.$tpid.'.jpg';

	if ($projectInfo->info->ischild) {
		$planUi = new Ui(NULL,'ui-card -sg-flex main-join project-plan-card');

		$planUi->add('<a class="" href="'.url('project/idea/create/'.$tpid).'"><img src="//softganz.com/img/img/'.$img.'" /></a><a class="btn -primary" href="'.url('project/idea/create/'.$tpid).'"><i class="icon -addbig -white"></i><span>เสนอแนวคิดโครงการ</span></a>');

		$planUi->add('<a class="" href="'.url('project/develop/create/'.$tpid).'"><img src="//softganz.com/img/img/'.$img.'" /></a><a class="btn -primary" href="'.url('project/develop/create/'.$tpid).'"><i class="icon -addbig -white"></i><span>เสนอโครงการ</span></a>');

		if ($isEdit) {
			$planUi->add('<a class="" href="'.url('project/create/'.$tpid).'"><img src="//softganz.com/img/img/'.$img.'" /></a><a class="btn -primary" href="'.url('project/create/'.$tpid).'"><i class="icon -addbig -white"></i><span>เพิ่มโครงการติดตาม</span></a>');
		}
		/*
		$ret.='<div class="col -md-4 -join">';
		$ret.='<a class="" href="'.url('project/set/'.$tpid.'/list').'"><img src="//softganz.com/img/img/pa-run02.jpg" /></a><a class="btn -primary" href="'.url('project/set/'.$tpid.'/list').'"><i class="icon -list -white"></i><span>โครงการย่อย</span></a>';
		$ret.='</div>';
		*/

		$ret .= '<div class="project-set-plan-list">'.$planUi->build().'</div>';
	}

	switch ($action) {
		case 'list':
			$ret .= __project_set_list(SG\getFirst($trid,$tpid));
			break;
		
		default:
			if (empty($tpid)) {
				$ret .= __project_set_plan_list();
				$ret .= R::Page('project.set.list',$self);
			} else {
				$basicInfo = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND formid = "info" AND `part` = "basic" LIMIT 1', ':tpid', $tpid);

				$ret.='<div class="project-set-detail'.($basicInfo->text1 ? ' -with-info' : '').'">'.__project_set_list(SG\getFirst($trid,$tpid)).'</div>';
				if ($basicInfo->text1) {
					$ret .= '<div id="basicinfo" class="box project-set-info">'.sg_text2html($basicInfo->text1).'</div>';
				}

				//R::View('project.toolbar',$self);
				//$ret.='<br clear="all" /><h3>แผนงานโครงการ</h3>';
				//$ret.=R::Page('project.form.main',$self,$projectInfo);
				if ($projectInfo->info->prtype!='แผนงาน') {
					$ret.='<div class="project-set-plan-list">';
					$ret.='<h3>กิจกรรมโครงการ</h3>';
					$ret.=R::Page('project.plan.tree',NULL,$tpid);
					$ret.='</div>';
				}
			}
			break;
	}

	//$ret.=$tpid.print_o($projectInfo);

	$ret.='<style type="text/css">
	.main-join .btn.-primary {width:260px;width: calc(80% - 32px);display:block;margin:16px auto;text-align:center;font-size:1.2em;font-family: "Mitr","RSU"}
	.main-join img {width:80%;display:block;margin:0 auto;}
	.project-set-plan-list {}
	.project-set-plan-list .ui-card {text-align:center;}
	.project-set-plan-list img {width:80%;display:block;margin:8px auto;}
	.project-set-plan-list>h3 {clear:both;padding:8px;margin:0 0 8px 0;background:#999;color:#fff;}
	#project-plan-item-master .sg-form.-no-print {display:none;}
	#project-plan-item-master .ui-menu.-main {display: none;}
	.module-project .ui-tree .ui-item.-header .title .icon {right:0;}

	.project-set-detail.-with-info {width: 50%; float: left;}
	.project-set-info {width: 45%; margin-left: 5%; float: right;}
	.module-project .box {margin:0;}
	.box>h4 {background-color:#eee; padding:8px; line-height: 1.6em; margin:8px 0;}
	</style>';
	return $ret;
}

// TODO : เพิ่มช่องให้เลือกจังหวัด อำเภอ
function __project_set_list($tpid = NULL) {
	$stmt = 'SELECT
		  p.`tpid`, p.`prtype`, p.`projectset`
		, t.`title`
		, (SELECT COUNT(*) FROM %calendar% c WHERE c.`tpid` = p.`tpid`) `activities`
		, (SELECT COUNT(*) FROM %project_tr% a WHERE a.`tpid` = p.`tpid` AND a.`formid`="activity") `actions`
		, t.`created`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `projectset` = :tpid
		ORDER BY CONVERT(`title` USING tis620) ASC';

	$dbs = mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อโครงการย่อย', 'activity -amt'=>'กิจกรรม','action -amt'=>'บันทึก','date'=>'วันที่เริ่มติดตาม');

	$planUi = new Ui(NULL,'ui-card -sg-flex project-plan-card');
	$setUi = new Ui(NULL,'ui-card -sg-flex project-set-card');

	$no = 0;

	foreach ($dbs->items as $rs) {
		$img = 'calendar.png';

		if (in_array($rs->tpid, array(20,21,22,23))) $img = 'pa-plan-'.$rs->tpid.'.jpg';

		if ($rs->prtype == 'แผนงาน') {
			$planUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img class="photo" src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else if ($rs->prtype == 'ชุดโครงการ') {
			$setUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else {
			$tables->rows[] = array(
				++$no,
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
				$rs->activities ? $rs->activities : '',
				$rs->actions ? $rs->actions : '',
				sg_date($rs->created,'ว ดด ปปปป')
			);
		}
	}

	$ret.='<div class="project-set-plan-list">';
	if ($planUi->count()) {
		$ret.='<h3>แผนงาน</h3>';
		$ret.=$planUi->build();
	}

	if ($setUi->count()) {
		$ret.='<h3>ชุดโครงการ</h3>';
		$ret.=$setUi->build();
	}

	if ($tables->rows) {
		$ret.='<h3>โครงการย่อย</h3>';
		$ret.=$tables->build();
	}

	$stmt='SELECT
		t.`tpid`, t.`title`, t.`created`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE t.`parent` = :parent ';
	$dbs = mydb::select($stmt,':parent',$tpid);
	
	if ($dbs->count()) {
		$ret .= '<h3>พัฒนาโครงการ</h3>';
		$tables = new Table();
		$no = 0;
		$tables->thead = array('no' => '', 'ชื่อพัฒนาโครงการ', 'date' => 'วันที่เริ่มพัฒนา');
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				++$no,
				'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
				sg_date($rs->created,'ว ดด ปปปป')
			);
		}

		$ret .= $tables->build();
	}

	$ret .= '</div>';

	//$ret.=print_o($dbs);

	return $ret;
}

// TODO : เพิ่มช่องให้เลือกจังหวัด อำเภอ
function __project_set_plan_list($tpid=NULL) {
	mydb::where('`prtype` = "แผนงาน"');
	if ($tpid) mydb::where('`projectset` = :tpid', ':tpid', $tpid);
	$stmt = 'SELECT p.`tpid`, p.`prtype`, p.`projectset`, t.`title`, t.`created`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY CONVERT(`title` USING tis620) ASC';

	$dbs = mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead = array('no'=>'','ชื่อโครงการย่อย','date'=>'วันที่เริ่มติดตาม');
	$planUi = new Ui(NULL,'ui-card -sg-flex project-plan-card');
	$setUi = new Ui(NULL,'ui-card -sg-flex project-set-card');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$img = 'calendar.png';
		if (in_array($rs->tpid, array(20,21,22,23))) $img='pa-plan-'.$rs->tpid.'.jpg';
		if ($rs->prtype == 'แผนงาน') {
			$planUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img class="photo" src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:""}');
		} else if ($rs->prtype == 'ชุดโครงการ') {
			$setUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:""}');
		} else {
			$tables->rows[] = array(
				++$no,
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
				sg_date($rs->created,'ว ดด ปปปป')
			);
		}
	}

	$ret .= '<div class="project-set-plan-list">';
	if ($planUi->count()) {
		$ret .= '<h3>แผนงาน</h3>';
		$ret .= $planUi->build();
	}
	$ret .= '</div>';

	/*
	if ($setUi->count()) {
		$ret.='<h3>ชุดโครงการ</h3>';
		$ret.=$setUi->build();
	}

	if ($tables->rows) {
		$ret.='<h3>โครงการย่อย</h3>';
		$ret.=$tables->build();
	}

	$stmt='SELECT
					*
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE t.`parent`=:parent ';
	$dbs=mydb::select($stmt,':parent',$tpid);
	if ($dbs->count()) {
		$ret.='<h3>พัฒนาโครงการ</h3>';
		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อพัฒนาโครงการ','date'=>'วันที่เริ่มพัฒนา');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												++$no,
												'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
												sg_date($rs->created,'ว ดด ปปปป')
												);
		}
		$ret.=$tables->build();
	}
	$ret.='</div>';
	*/
	//$ret.=print_o($dbs);

	return $ret;
}
?>