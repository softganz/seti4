<?php
/**
 * My relate project planning such as Owner, Trainer
 *
 * @return String
 */
function project_my_planning($self, $action = NULL) {
	R::View('project.toolbar',$self,'แผนงาน','my',$projectInfo,'{modulenav:false}');

	$isPlanningProject = false;
	$statusList = project_base::$statusList;

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');


	if ($action) {
		switch ($action) {
			case 'new':
				$ret .= R::Page('project.planning.new',$self);
				break;

			default:
				# code...
				break;
		}
		return $ret;
	}


	//$ret .= '<h3>รายชื่อแผนงาน</h3>';

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
	$tables->thead=array('', 'year -date'=>'ปี', 'title'=>'ชื่อแผนงาน', 'created -date'=>'วันที่สร้าง', 'changed -date'=>'แก้ไขล่าสุด','สร้างโดย');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			$rs->pryear+543,
			'<a href="'.url('project/planning/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
			sg_date($rs->created,'ว ดด ปป'),
			$rs->changed?sg_date($rs->changed,'ว ดด ปป H:i').' น.':'',
			$rs->name,
		);
	}

	$ret .= $tables->build();

	$maxPlanningProject=cfg('PROJECT.PLANNING.MAX_PER_USER');
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

	$isCreatePlanning = user_access('create project planning')
		&&  in_array('my/planning', explode(',', cfg('PROJECT.PLANNING.ADD_FROM_PAGE')));

	if ($isCreatePlanning) {
		$ret .= (new FloatingActionButton([
			'children' => ['<a class="sg-action btn -floating" href="'.url('project/planning/new').'" title="Create New Planning" data-rel="box" data-width="480"><i class="icon -material">add"></i><span>สร้างประเมินระดับแผนงาน</span></a>'],
		]))->build();
		//'<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/planning/new').'" title="Create New Planning"><i class="icon -addbig -white"></i></a></nav>';
	}

	$ret.='<style type="text/css">
	table.project-develop-list {margin:0 0 40px 0;box-shadow:2px 2px 2px #aaa;}
	.project-develop-list td {vertical-align:middle;}
	</style>';
	return $ret;
}
?>