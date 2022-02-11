<?php
/**
 * My relate project planning such as Owner, Trainer
 *
 * @return String
 */
function project_my_eval($self, $action = NULL) {
	R::View('project.toolbar',$self,'ติดตาม/ประเมินผล','my',$projectInfo,'{modulenav:false}');

	$isPlanningProject = false;
	$statusList = project_base::$statusList;

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');


	if ($action) {
		switch ($action) {
			case 'new':
				$ret .= R::Page('project.planning.new',NULL);
				break;

			default:
				# code...
				break;
		}
		return $ret;
	}



	$ret .= '<div class="project-my-eval-all ui-card">';

	// Project Planning
	$ret .= '<div class="ui-item"><h3><i class="icon -diagram"></i><span>รายการประเมินระดับแผนงาน</span></h3>';
	$stmt = 'SELECT t.*, p.*, u.`username`, u.`name`,tu.`uid` `tuid`, tu.`membership`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
					WHERE p.`prtype` = "แผนงาน" AND (t.`uid` = :uid OR tu.`uid` = :uid)
					ORDER BY `changed` DESC';
	$dbs = mydb::select($stmt,':uid',i()->uid);

	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('project-planning-list');
	$tables->thead=array('', 'year -date'=>'ปี', 'title'=>'ชื่อแผนงาน', 'created -date'=>'วันที่สร้าง');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			$rs->pryear+543,
			'<a href="'.url('project/planning/'.$rs->tpid).'"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
			sg_date($rs->created,'ว ดด ปป'),
		);
	}

	$ret .= $tables->build();

	$ret .= '<nav class="nav -card -sg-text-right"><a class="btn -link" href="'.url('project/my/planning').'"><i class="icon -list"></i></a> '
			//. '<a class="btn -link" href="'.url('project/my/planning/new').'"><i class="icon -add"></i></a>'
			. '</nav>';
	$ret .= '</div>';



	// Project Set
	$ret .= '<div class="ui-item"><h3><i class="icon -nature-people"></i><span>รายการประเมินระดับชุดโครงการ</span></h3>';

	$stmt = 'SELECT t.*, p.*, u.`username`, u.`name`,tu.`uid` `tuid`, tu.`membership`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
					WHERE p.`prtype` = "ชุดโครงการ" AND (t.`uid` = :uid OR tu.`uid` = :uid)
					ORDER BY `changed` DESC';
	$dbs = mydb::select($stmt,':uid',i()->uid);

	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('project-planning-list');
	$tables->thead=array('', 'year -date'=>'ปี', 'title'=>'ชุดโครงการ', 'created -date'=>'วันที่สร้าง');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			$rs->pryear+543,
			'<a href="'.url('project/set/'.$rs->tpid).'"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
			sg_date($rs->created,'ว ดด ปป'),
		);
	}

	$ret .= $tables->build();
	$ret .= '<nav class="nav -card -sg-text-right"><a class="btn -link" href="'.url('project/my/set').'"><i class="icon -list"></i></a> '
			//. '<a class="btn -link" href="'.url('project/my/set/new').'"><i class="icon -add"></i></a>'
			. '</nav>';

	$ret .= '</div>';




	// Project Following
	$ret .= '<div class="ui-item"><h3><i class="icon -walk"></i><span>รายการประเมินระดับโครงการ</span></h3>';

	$stmt = 'SELECT t.*, p.*, u.`username`, u.`name`,tu.`uid` `tuid`, tu.`membership`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
					WHERE p.`prtype` = "โครงการ" AND (t.`uid` = :uid OR tu.`uid` = :uid)
					ORDER BY `changed` DESC';
	$dbs = mydb::select($stmt,':uid',i()->uid);

	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('project-planning-list');
	$tables->thead=array('', 'year -date'=>'ปี', 'title'=>'โครงการ', 'created -date'=>'วันที่สร้าง');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
											$rs->pryear+543,
											'<a href="'.url('project/'.$rs->tpid).'"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
											sg_date($rs->created,'ว ดด ปป'),
										);
	}

	$ret .= $tables->build();
	$ret .= '<nav class="nav -card -sg-text-right"><a class="btn -link" href="'.url('project/my/project').'"><i class="icon -list"></i></a> '
			//. '<a class="btn -link" href="'.url('project/my/project/new').'"><i class="icon -add"></i></a>'
			. '</nav>';

	$ret .= '</div>';
	$ret .= '</div><!-- project-my-eval-all -->';



	//$ret .= '<h3>รายชื่อแผนงาน</h3>';



	$maxPlanningProject = cfg('PROJECT.PLANNING.MAX_PER_USER');
	if ($maxPlanningProject==0 || ($maxPlanningProject>0 && $dbs->_num_rows<$maxPlanningProject)) {
		//$developMsg='<p>มีโครงการพัฒนาอยู่ทั้งหมด '.$dbs->_num_rows.' โครงการ หากต้องการเริ่มพัฒนาโครงการใหม่ ให้ <a href="'.url('project/develop/create').'">คลิกที่นี่</a> เพื่อเริ่มต้นพัฒนาโครงการใหม่</p>';
		$isPlanningProject=true;
	}


	/*
	$pageUi=new Ui(NULL,'ui-nav -atright');
	if ($isPlanningProject) $pageUi->add('<a class="btn" href="'.url('project/develop/create').'"><i class="icon -add"></i><span>เพิ่มพัฒนาโครงการใหม่</span></a>');
	if (user_access('create project content')) $pageUi->add('<a class="btn" href="'.url('paper/post/project').'"><i class="icon -add"></i><span>เพิ่มโครงการติดตามใหม่</span></a>');
	$ret.='<nav class="nav -page -no-print">'._NL.$pageUi->build().'</nav>'._NL;
	*/

	/*
	$isCreatePlanning = user_access('create project planning')
											&&  in_array('my/planning', explode(',', cfg('PROJECT.EVAL.ADD_FROM_PAGE')));

	if ($isCreatePlanning) {
		$ret.='<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/eval/new').'" title="Create New Planning"><i class="icon -addbig -white"></i></a></nav>';
	}

	*/

	$ret .= '<style type="text/css">
	.ui-menu.-icons.-is-big {display: flex; flex-wrap: wrap; justify-content: space-between;}
	.ui-menu.-icons.-is-big>.ui-item {margin: 0 16px 16px 0;}
	.ui-menu.-icons.-is-big>.ui-item>a {padding: 16px;}
	.project-my-eval-all {display: flex; flex-wrap: wrap; justify-content: space-between;}

	.project-my-eval-all h3 {background-color: #ffe4c1;}
	.project-my-eval-all>.ui-item {width: 100%;}
	.nav.-card {padding: 0 4px;}
	@media (min-width:60em) { /* 960/16 = 60 */
		.project-my-eval-all>.ui-item {width: 32%;}
	}

	</style>';
	return $ret;
}
?>