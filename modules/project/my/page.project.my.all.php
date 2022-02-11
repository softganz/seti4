<?php
/**
* Project :: My All
* Created 2021-12-20
* Modify  2021-12-20
*
* @return Widget
*
* @usage project/my/all
*/

import('widget:appbar.nav.php');
import('page:project.my.php');

class ProjectMyAll extends ProjectMy {
	// function build() {
	// 	$statusList = project_base::$statusList;

	// 	return new Scaffold([
	// 		'appBar' => new AppBar([
	// 			'title' => '@'.i()->name,
	// 			'leading' => '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />',
	// 			'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
	// 		]), // AppBar
	// 		'body' => new Widget([
	// 			'children' => [
	// 				new Table([

	// 				mydb::where('`type`="project-develop"');
	// 				mydb::where('(t.`uid`=:uid OR tu.`uid`=:uid)', ':uid', i()->uid);

	// 				$stmt = 'SELECT t.*, u.`username`, u.`name`,tu.`uid` tuid, tu.`membership`
	// 					FROM %topic% t
	// 						LEFT JOIN %users% u USING(uid)
	// 						LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
	// 					%WHERE%
	// 					ORDER BY `changed` DESC';

	// 				$dbs = mydb::select($stmt);
	// 				//$ret .= print_o($dbs,'$dbs');

	// 				if ($dbs->_num_rows) {
	// 					$ret.='<h3>รายชื่อพัฒนาโครงการ</h3>';
	// 					$tables = new Table();
	// 					$tables->addClass('project-develop-list box');
	// 					$tables->thead=array('', 'title'=>'ชื่อโครงการพัฒนา', 'created -date'=>'วันที่เริ่มพัฒนา', 'changed -date'=>'แก้ไขล่าสุด','พัฒนาโดย','สถานะโครงการ');
	// 					$no=0;
	// 					foreach ($dbs->items as $rs) {
	// 						$tables->rows[] = array(
	// 							'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
	// 							'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
	// 							sg_date($rs->created,'ว ดด ปป'),
	// 							$rs->changed?sg_date($rs->changed,'ว ดด ปป H:i').' น.':'',
	// 							$rs->name,
	// 							$statusList[$rs->status]
	// 						);
	// 					}
	// 					$ret .= $tables->build();
	// 				}

	// 			], // children
	// 		]), // Widget
	// 	]);
	// }
}
?>
<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my_all($self) {
	R::View('project.toolbar',$self,'โครงการในความรับผิดชอบ','my',$projectInfo,'{modulenav:false}');
	$isDevelopProject = false;
	$statusList = project_base::$statusList;

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	mydb::where('`type`="project-develop"');
	mydb::where('(t.`uid`=:uid OR tu.`uid`=:uid)', ':uid', i()->uid);

	$stmt = 'SELECT t.*, u.`username`, u.`name`,tu.`uid` tuid, tu.`membership`
		FROM %topic% t
			LEFT JOIN %users% u USING(uid)
			LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
		%WHERE%
		ORDER BY `changed` DESC';

	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	if ($dbs->_num_rows) {
		$ret.='<h3>รายชื่อพัฒนาโครงการ</h3>';
		$tables = new Table();
		$tables->addClass('project-develop-list box');
		$tables->thead=array('', 'title'=>'ชื่อโครงการพัฒนา', 'created -date'=>'วันที่เริ่มพัฒนา', 'changed -date'=>'แก้ไขล่าสุด','พัฒนาโดย','สถานะโครงการ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
				'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>',
				sg_date($rs->created,'ว ดด ปป'),
				$rs->changed?sg_date($rs->changed,'ว ดด ปป H:i').' น.':'',
				$rs->name,
				$statusList[$rs->status]
			);
		}
		$ret .= $tables->build();
	}


	//$pageUi=new Ui(NULL,'ui-nav -atright');

	//$pageUi->add('<a class="btn -primary" href="'.url('project/develop/nofund/create').'"><i class="icon -adddoc -white"></i><span>เริ่มพัฒนาโครงการ</span></a>');
		//if ($isDevelopProject) $pageUi->add('<a class="btn" href="'.url('project/develop/create').'"><i class="icon -add"></i><span>เพิ่มพัฒนาโครงการใหม่</span></a>');
	//if (user_access('create project content')) $pageUi->add('<a class="btn" href="'.url('paper/post/project').'"><i class="icon -add"></i><span>เพิ่มโครงการติดตามใหม่</span></a>');
	//$ret.='<nav class="nav -page -no-print">'._NL.$pageUi->build().'</nav>'._NL;




	$ret .= '<h3>รายชื่อติดตามและประเมินผลโครงการ</h3>';
	$ret .= '<div class="sg-load box" data-url="project/api/follow?u='.i()->uid.'"><div class="loader -rotate" style="width: 64px; height: 64px; margin: 16px auto; display: block;"></div></div>';


	$isCreateProject = user_access('create project content')
		&&  in_array('my/all', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

	if ($isCreateProject) {
		$ret.='<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/project/new',cfg('PROJECT.PROJECT.ADD_PARA')).'" title="Create New Project"><i class="icon -addbig -white"></i></a></nav>';
	}

	$ret.='<style type="text/css">
	.project-develop-list td {vertical-align:middle;}
	</style>';
	return $ret;
}
?>