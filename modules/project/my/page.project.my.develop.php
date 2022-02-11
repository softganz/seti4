<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my_develop($self, $action = NULL) {
	R::View('project.toolbar',$self,'พัฒนาโครงการ','my',$projectInfo,'{modulenav:false}');

	$isDevelopProject=false;
	$statusList=project_base::$statusList;

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');


	if ($action) {
		switch ($action) {
			case 'create':
				$ret .= R::Page('project.develop.create',NULL);
				break;
			
			default:
				# code...
				break;
		}
		return $ret;
	}



	$stmt='SELECT t.*, d.*, u.`username`, u.`name`,tu.`uid` tuid, tu.`membership`
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %users% u USING(uid)
						LEFT JOIN %topic_user% tu ON tu.`tpid`=t.`tpid` AND tu.`uid`=:uid
						-- AND tu.`membership`="Trainer"
					WHERE `type`="project-develop" AND (t.`uid`=:uid OR tu.`uid`=:uid)
					ORDER BY `changed` DESC';
	$dbs=mydb::select($stmt,':uid',i()->uid);

	if ($dbs->_num_rows) {
		$ret.='<h3>รายชื่อพัฒนาโครงการ</h3>';
		$tables = new Table();
		$tables->addClass('project-develop-list');
		$tables->thead=array('', 'year -date'=>'ปี', 'title'=>'ชื่อโครงการพัฒนา', 'created -date'=>'วันที่เริ่มพัฒนา', 'changed -date'=>'แก้ไขล่าสุด','พัฒนาโดย','สถานะโครงการ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
												$rs->pryear+543,
												'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
												sg_date($rs->created,'ว ดด ปป'),
												$rs->changed?sg_date($rs->changed,'ว ดด ปป H:i').' น.':'',
												$rs->name,
												$statusList[$rs->status]
													);
		}
		$ret .= $tables->build();
	}


	/*
	$pageUi=new Ui(NULL,'ui-nav -atright');
	if ($isDevelopProject) $pageUi->add('<a class="btn" href="'.url('project/develop/create').'"><i class="icon -add"></i><span>เพิ่มพัฒนาโครงการใหม่</span></a>');
	if (user_access('create project content')) $pageUi->add('<a class="btn" href="'.url('paper/post/project').'"><i class="icon -add"></i><span>เพิ่มโครงการติดตามใหม่</span></a>');
	$ret.='<nav class="nav -page -no-print">'._NL.$pageUi->build().'</nav>'._NL;
	*/




	$isCreateDevelop = user_access('create project proposal')
											&&  in_array('my/develop', explode(',', cfg('PROJECT.DEVELOP.ADD_FROM_PAGE')));

	if ($isCreateDevelop) {
		$ret.='<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/develop/create').'" title="Create New Project Development"><i class="icon -addbig -white"></i></a></nav>';
	}

	$ret.='<style type="text/css">
	table.project-develop-list {margin:0 0 40px 0;box-shadow:2px 2px 2px #aaa;}
	.project-develop-list td {vertical-align:middle;}
	</style>';
	return $ret;
}
?>