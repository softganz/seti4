<?php
function project_set_home($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$planSelect = post('pn');
	$provinceSelect = post('pv');

	if ($tpid) {
		R::Module('project.template', $self, $tpid);
		$projectInfo = R::Model('project.get', $tpid);
	}

	R::View('project.toolbar', $self, $projectInfo->title, 'set', $projectInfo);

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;


	$projectIssues = array(
		1=>'ประเด็นที่ 1 งานรักษาความปลอดภัยชีวิตและทรัพย์สิน',
		'ประเด็นที่ 2 งานอำนวยความยุติธรรมและเยียวยา',
		'ประเด็นที่ 3 งานสร้างความเข้าใจทั้งในและต่างประเทศและเรื่องสิทธิมนุษยชน',
		'ประเด็นที่ 4 งานการศึกษา ศาสนา และศิลปวัฒนธรรม',
		'ประเด็นที่ 5 งานพัฒนาตามศักยภาพของพื้นที่และคุณภาพชีวิตประชาชน',
		'ประเด็นที่ 6 งานแสวงหาทางออกจากความขัดแย้งโดยสันติวิธี',
		'ประเด็นที่ 7 งานขับเคลื่อนการพัฒนาโครงการเมืองต้นแบบ สามเหลี่ยม มั่นคง มั่งคั่ง ยั่งยืน',
		'ประเด็นที่ 8 งานขับเคลื่อนนโยบายการแก้ไขปัญหา จชต. ปี 60-62',
		'ประเด็นที่ 9 งานป้องกันและแก้ไขปัญหายาเสพติด',
		'ประเด็นที่ 10 งานพัฒนาสร้างศักยภาพองค์กรภาคประชาสังคม'
	);

	$navbar = '<!--navbar start-->';
	$navBar .= '<nav class="nav -navbar">';

	$form = new Form(NULL, url('project/set/'.$tpid), NULL, '-inlineitem');
	$form->addConfig('method', 'GET');

	$form->addField(
					'pn',
					array(
						'type' => 'select',
						'options' => array('' => '== ทุกประเด็น ==') + $projectIssues,
						'value' => $planSelect
					)
				);

	$stmt = 'SELECT DISTINCT p.`changwat`, cop.`provname` `changwatName`, COUNT(*) `total` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` WHERE `projectset` = :projectset AND `changwat` != "" GROUP BY `changwat` ORDER BY CONVERT(`changwat` USING tis620)';
	$dbs = mydb::select($stmt, ':projectset', $tpid);

	$areaList = array('' => '== ทุกจังหวัด ==');
	foreach ($dbs->items as $rs) {
		$areaList[$rs->changwat] = $rs->changwatName.' ('.number_format($rs->total).' โครงการ)';
	}

	$form->addField(
					'pv',
					array(
						'type' => 'select',
						'options' => $areaList,
						'value' => $provinceSelect,
					)
				);

	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i><span>GO</span>'));
	$navBar .= $form->build();
	$navBar .= '</nav><!--navbar end-->'._NL;

	$self->theme->navbar = $navBar;

	if (empty($tpid)) {
		$ret .= __project_set_plan_list();
		$ret .= '<br clear="all" />';
		$ret .= R::Page('project.set.list',$self);
		return $ret;
	}


	mydb::where('`projectset` = :tpid AND `prtype` = "โครงการ"');
	if ($provinceSelect)
		mydb::where('p.`changwat` = :changwat', ':changwat', $provinceSelect);
	if ($planSelect)
		mydb::where('p.`supporttype` = :supporttype', ':supporttype', $planSelect);

	$stmt = 'SELECT
					  p.`tpid`, p.`prtype`, p.`projectset`, p.`supporttype`
					, t.`title`
					, cop.`provname` `changwatName`
					, (SELECT COUNT(*) FROM %calendar% c WHERE c.`tpid` = p.`tpid`) `activities`
					, (SELECT COUNT(*) FROM %project_tr% a WHERE a.`tpid` = p.`tpid` AND a.`formid`="activity") `actions`
					, t.`created`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
					%WHERE%
					ORDER BY CONVERT(`title` USING tis620) ASC';
	$dbs = mydb::select($stmt,':tpid',$tpid);


	$ret .= '<div class="project-set-detail">';

	$tables = new Table();
	$tables->thead=array('no'=>'','โครงการย่อย', 'changwat -center' => 'จังหวัด', 'activity -amt'=>'กิจกรรม','action -amt'=>'บันทึก','date'=>'เริ่มติดตาม');
	$planUi=new Ui(NULL,'ui-card row -flex project-plan-card');
	$setUi=new Ui(NULL,'ui-card row -flex project-set-card');
	$no=0;
	foreach ($dbs->items as $rs) {
		$img='pa-run01.jpg';
		if (in_array($rs->tpid, array(20,21,22,23))) $img='pa-plan-'.$rs->tpid.'.jpg';
		if ($rs->prtype=='แผนงาน') {
			$planUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img class="photo" src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else if ($rs->prtype=='ชุดโครงการ') {
			$setUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else {
			$tables->rows[]=array(
												++$no,
												'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>'
												. ($rs->supporttype ? '<br /><em>'.$projectIssues[$rs->supporttype].'</em>' : ''),
												$rs->changwatName,
												$rs->activities ? $rs->activities : '',
												$rs->actions ? $rs->actions : '',
												sg_date($rs->created,'ว ดด ปปปป')
												);
		}
	}

	$ret.=$tables->build();

	$ret .= '</div><!-- project-set-detail -->';

	//$ret.=$tpid.print_o($projectInfo);

	$ret.='<style type="text/css">
	.main-join .btn.-primary {width:260px;width: calc(80% - 32px);display:block;margin:16px auto;text-align:center;font-size:1.2em;font-family: "Mitr","RSU"}
	.main-join img {width:80%;display:block;margin:0 auto;}
	.container.-plan-list {}
	.container.-plan-list .ui-card {text-align:center;}
	.container.-plan-list img {width:80%;display:block;margin:8px auto;}
	.container.-plan-list>h3 {clear:both;padding:8px;margin:0 0 8px 0;background:#999;color:#fff;}
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
	$tables->thead=array('no'=>'','โครงการย่อย', 'activity -amt'=>'กิจกรรม','action -amt'=>'บันทึก','date'=>'เริ่มติดตาม');
	$planUi=new Ui(NULL,'ui-card row -flex project-plan-card');
	$setUi=new Ui(NULL,'ui-card row -flex project-set-card');
	$no=0;
	foreach ($dbs->items as $rs) {
		$img='pa-run01.jpg';
		if (in_array($rs->tpid, array(20,21,22,23))) $img='pa-plan-'.$rs->tpid.'.jpg';
		if ($rs->prtype=='แผนงาน') {
			$planUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img class="photo" src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else if ($rs->prtype=='ชุดโครงการ') {
			$setUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else {
			$tables->rows[]=array(
												++$no,
												'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
												$rs->activities ? $rs->activities : '',
												$rs->actions ? $rs->actions : '',
												sg_date($rs->created,'ว ดด ปปปป')
												);
		}
	}

	$ret.='<div class="container -plan-list">';
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

	//$ret.=print_o($dbs);

	return $ret;
}

// TODO : เพิ่มช่องให้เลือกจังหวัด อำเภอ
function __project_set_plan_list($tpid=NULL) {
	mydb::where('`prtype` = "แผนงาน"');
	if ($tpid) mydb::where('`projectset`=:tpid', ':tpid', $tpid);
	$stmt='SELECT p.`tpid`, p.`prtype`, p.`projectset`, t.`title`, t.`created`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					%WHERE%
					ORDER BY CONVERT(`title` USING tis620) ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อโครงการย่อย','date'=>'วันที่เริ่มติดตาม');
	$planUi=new Ui(NULL,'ui-card row -flex project-plan-card');
	$setUi=new Ui(NULL,'ui-card row -flex project-set-card');
	$no=0;
	foreach ($dbs->items as $rs) {
		$img='pa-run01.jpg';
		if (in_array($rs->tpid, array(20,21,22,23))) $img='pa-plan-'.$rs->tpid.'.jpg';
		if ($rs->prtype=='แผนงาน') {
			$planUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img class="photo" src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else if ($rs->prtype=='ชุดโครงการ') {
			$setUi->add('<h3><a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" />'.$rs->title.'</a></h3>','{class:"col -md-3"}');
		} else {
			$tables->rows[]=array(
												++$no,
												'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
												sg_date($rs->created,'ว ดด ปปปป')
												);
		}
	}

	$ret.='<div class="container -plan-list">';
	if ($planUi->count()) {
		$ret.='<h3>แผนงาน</h3>';
		$ret.=$planUi->build();
	}

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