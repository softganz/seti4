<?php
/**
* Project :: My Action
* Created 2021-12-14
* Modify  2021-12-14
*
* @param String $arg1
* @return Widget
*
* @usage project//my/action
*/

import('widget:appbar.nav.php');
import('model:project.follow.php');
import('widget:project.actions');

class ProjectMyAction extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		$getConditions = (Object) [];
		$getOptions = (Object) [
			'debug' => false,
			'order' => '`actionDate` DESC, `actionId` DESC',
		];

		if (in_array($action, array('info','view')) && $actionId) $getConditions->actionId = $actionId;
		else if ($tpid) $getConditions->projectId = $tpid;
		else if ($getAll) {
			$getOptions->start = $startItem;
			$getOptions->item = $showItems;
		} else {
			$getConditions->userId = i()->uid;
			//$tpid=mydb::select('SELECT GROUP_CONCAT(DISTINCT `tpid`) `tpids` FROM %project_tr% WHERE `formid`="activity" AND `uid`=:uid LIMIT 1',':uid',i()->uid)->tpids;
		}

		$actionList = R::Model('project.action.get',$getConditions,$getOptions);
		$projectList = ProjectFollowModel::items(['userId' => 'member', 'status' => 'process'])->items;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กิจกรรมโครงการ@'.i()->name,
				'leading' => '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />',
				'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
			]), // AppBar
			// 'floatingActionButton' => new FloatingActionButton([
			// 	'children' => ['<a class="sg-action btn -floating" href="'.url('project/my/action/post').'" data-rel="#main" data-width="full"><i class="icon -material">add</i><span>บันทึกกิจกรรม</span></a>'],
			// ]),
			'body' => new Widget([
				'children' => [
					new Card([
						'id' => 'project-chat-box',
						'class' => 'ui-card project-chat-box',
						'children' => [
							'<div class="ui-item">'
							. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
							. '<a class="x-sg-action form-text" href="'.url('project/my/action/post').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="#main" data-width="480" data-height="100%" x-data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
							. '<a class="x-sg-action btn -link" href="'.url('project/my/action/post').'" data-rel="#main" data-width="480" data-height="100%" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
							. '</div>'
						],
					]), // Card

					new ProjectActionsWidget([
						'children' => $actionList,
						'urlMore' => $activityCount && $activityCount == $showItems ? url('project/app/activity', ['u' => $this->userId, 'id' => $this->projectId, 'start' => $this->start+$activityCount]) : NULL,
					]),

					// new DebugMsg($actionList, '$actionList'),
				], // children
			]), // Widget
		]);
	}
}
?>
<?php
/**
 * Project Application
 *
 * @param Object $topic
 */

// TODO : แยกการลบบันทึกกิจกรรมสำหรับแต่ละ template โดยเฉพาะ riskfactor ที่จะต้องลบทั้งบันทึก,ปฏิทิน,กิจกรรมที่

function project_my_action($self,$tpid = NULL, $action = NULL, $actionId = NULL) {
	if ($tpid=='*') {$getAll=true;unset($tpid);}
	$startItem=SG\getFirst(post('start'),0);
	$showItems=10;
	$projectInfo=NULL;

	$isAdmin=user_access('administer projects');

	if ($tpid) {
		R::Module('project.template',$self,$tpid);
		$projectInfo=R::Model('project.get',$tpid);
	}

	R::View('project.toolbar',$self,'กิจกรรมโครงการ','my',$projectInfo,'{modulenav:false}');

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');


	$ret='';


	if ($action!='info') {
		if ($tpid) {
			$self->theme->title=$projectInfo->title;
			if ($projectInfo->info->project_statuscode==1) {
				$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/my/action/post/'.$tpid).'" data-rel="#main" title="บันทึกกิจกรรม"><i class="icon -addbig -white"></i></a></div>';
			}
		} else {
			$ret.='<div class="btn-floating -right-bottom"><a class="btn -floating -circle48" href="'.url('project/my/action/post').'" title="บันทึกกิจกรรม"><i class="icon -addbig -white"></i></a></div>';
		}
	}



	$getConditions = (Object) [];
	$getOptions = (Object) [
		'debug' => false,
		'order' => '`actionDate` DESC, `actionId` DESC',
	];

	if (in_array($action,array('info','view')) && $actionId) $getConditions->actionId = $actionId;
	else if ($tpid) $getConditions->projectId = $tpid;
	else if ($getAll) {
		$getOptions->start = $startItem;
		$getOptions->item = $showItems;
	} else {
		$getConditions->userId = i()->uid;
		//$tpid=mydb::select('SELECT GROUP_CONCAT(DISTINCT `tpid`) `tpids` FROM %project_tr% WHERE `formid`="activity" AND `uid`=:uid LIMIT 1',':uid',i()->uid)->tpids;
	}

	//$getConditions->uid=i()->uid;
	//$getConditions->part='owner';
	//$getConditions->period=2;

	//$getOptions->page=2;
	//$getOptions->item=10;

	//$getConditions->actionid=154985;
	//$ret.='TOPIC='.$tpid;
	//$ret.=mydb()->_query;

	$actionList=R::Model('project.action.get',$getConditions,$getOptions);
	//$a->items=$actionList;
	//$ret.=mydb::printtable($a);
	//$ret.=print_o($actionList,'$actionList');


	$ret.='<div class="ui-card project-action">'._NL;
	if (empty($actionList)) {
		$ret.='<p class="notify">ไม่มีบันทึกกิจกรรม</p>';
		if ($tpid)
			$ret.='<a class="btn" href="'.url('project/my/action/'.$tpid.'/all').'"><i class="icon -viewdoc"></i><span>ดูกิจกรรมทั้งหมดของโครงการ</span></a>';
	}

	foreach ($actionList as $rs) {
		$ret.=R::View('project.my.action.render',$projectInfo,$rs,$action);
	}
	$ret.='</div>';


	if ($getAll && count($actionList)==$showItems) {
		$ret.='<p><a class="sg-action btn -primary" href="'.url('project/my/action/*',array('start'=>$startItem+count($actionList))).'" data-rel="replace" style="margin:0 16px;display:block;text-align:center;"><span>More</span><i class="icon -forward -white"></i></a></p>';
	}

	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='<script type="text/javascript">
		$(document).ready(function() {
			$(".photoitem>li").each(function() {
				var width=Math.floor($(this).width());
				//console.log($(this).width()+" "+width)
				$(this).height(width+"px");
				$(this).children("a").width((width-2)+"px").height((width-2)+"px");
			});
		})
	</script>';

	return $ret;
}

	/*
	mydb::where('tr.`formid`="activity"');
	if ($action=='all') ;
	else if (!$isAdmin) mydb::where('tr.`uid`=:uid',':uid',i()->uid);
	if ($tpid) mydb::where('tr.`tpid`=:tpid',':tpid',$tpid);

	$stmt='SELECT
					  tr.`tpid`
					, tr.`trid`
					, tr.`uid`
					, u.`username`
					, u.`name` `poster`
					, t.`title`
					, tr.`text2` `real_work`
					, tr.`calid`
				--	, c.`title` `activityTitle`
					, tr.`detail1` `activityTitle`
					, tr.`date1` `action_date`
					, GROUP_CONCAT(DISTINCT pf.`fid`, "|" , pf.`file`) `photos`
					, t.`view`
					, (SELECT COUNT(*) FROM %project_tr% a WHERE a.`tpid`=tr.`tpid` AND `formid`="activity") `activitys`
					, tr.`created`
				FROM %project_tr% tr
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
					LEFT JOIN %users% u ON u.`uid`=tr.`uid`
					LEFT JOIN %topic_files% pf
						ON tr.`gallery` IS NOT NULL
							AND pf.`tpid`=tr.`tpid`
							AND pf.`gallery`=tr.`gallery`
							AND pf.`type`="photo"
				%WHERE%
				GROUP BY `trid`
				ORDER BY `date1` DESC
				LIMIT 50';
	//$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');
	*/
?>