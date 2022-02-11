<?php
/**
* Module :: Description
* Created 2021-12-14
* Modify  2021-12-14
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

import('model:project.follow.php');
import('widget:appbar.nav.php');

class ProjectMyActionPost extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		$projectList = ProjectFollowModel::items(['userId' => 'member', 'status' => 'process']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'บันทึกกิจกรรม',
				'leading' => '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />',
				'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
				// 'leading' => _HEADER_BACK,
				// 'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => array_map(
					function($item) {
						return new Card([
							'children' => [
								new ListTile([
									'class' => '-sg-paddingnorm',
									'title' => $item->title,
									'trailing' => '<a class="sg-action btn -primary" href="'.url('project/my/action/form/'.$item->projectId).'" data-rel="#main"><i class="icon -material">add_circle_outline</i><span>เขียนบันทึกกิจกรรม</span></a>',
									// 'trailing' => '<a class="sg-action btn -primary" href="'.url('project/'.$item->projectId.'/info/action.post').'" data-rel="box->clear"><i class="icon -material">add_circle_outline</i><span>เขียนบันทึกกิจกรรม</span></a>',
								]), // ListTilte
							], // children
						]);
					},
					$projectList->items
				), // children
			]), // Widget
		]);
	}
}
?>
<?php
/**
* Post my action
* Created 2020-01-09
* Modify  2020-01-09
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_my_action_post($self, $tpid = NULL) {
	R::View('project.toolbar',$self,'กิจกรรมโครงการ','my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	if ($tpid) {
		//$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/action/post/'.$tpid).'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
		R::Module('project.template',$self,$tpid);
		$projectInfo = R::Model('project.get',$tpid);
		$self->theme->title = $projectInfo->title;
		if ($projectInfo->info->project_statuscode == 1) {
			//$ret .= '<h3>กิจกรรมตามแผนโครงการ</h3>';

			$options = new stdClass;
			$options->moneyform = 'row';
			$options->ret = SG\getFirst(post('ret'), url('project/my/action/list'));
			//,url('project/my/action/'.$tpid));
			$ret .= R::View('project.action.form',$projectInfo, NULL, NULL, $options);
		} else {
			location('project/my/action/'.$tpid);
		}
		//$ret.=print_o($projectInfo);
		return $ret;
		// view_project_action_form($projectInfo,$activityId=NULL,$data=NULL) {
	}



	//$ret.='<p class="notify">กรุณาเลือกโครงการเพื่อบันทึกกิจกรรม</p>';
	mydb::where('p.`prtype`="โครงการ" AND tu.`uid` = :uid',':uid',i()->uid);

	$stmt = 'SELECT
			*
		, p.`project_status`+0 `project_statuscode`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_user% tu USING(`tpid`)
		%WHERE%
		ORDER BY `tpid` DESC
		';

	$dbs = mydb::select($stmt);

	$ui = new Ui('div','ui-card project-list');
	foreach ($dbs->items as $rs) {
		$cardStr = '<h3 class="title"><a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a></h3>';
		$cardStr .= '<div class="project-date">ระยะเวลาดำเนินโครงการ '
			.($rs->date_from ? sg_date($rs->date_from, 'ว ดดด ปปปป') : '???')
			.' - '
			.($rs->date_end ? sg_date($rs->date_end, 'ว ดดด ปปปป') : '???')
			.'</div>';
		$cardStr .= '<div class="project-status">สถานะ '.$rs->project_status.'</div>';

		$cardUi = new Ui();
		//$cardUi->add('<a class="btn" href="'.url('paper/'.$rs->tpid).'"><i class="icon -viewdoc -gray"></i><span>ติดตาม</span></a>');
		$cardUi->add('<a class="btn" href="'.url('project/my/action/'.$rs->tpid.'/all').'"><i class="icon -person -gray"></i><span>กิจกรรม</span></a>');
		if ($rs->project_statuscode == 1) {
			$cardUi->add('<a class="btn -primary" href="'.url('project/my/action/post/'.$rs->tpid).'"><i class="icon -addbig -white"></i><span>บันทึกกิจกรรม</span></a>');
		} else {
			$cardUi->add('<div style="width:140px;"></div>');
		}
		$cardStr .= '<nav class="nav -card">'.$cardUi->build().'</nav>';
		$cardStr .= '<p>&nbsp;</p>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>