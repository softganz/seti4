<?php
/**
* Project :: Page Setting Command
* Created 2020-06-04
* Modify  2020-06-04
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_page_setting($self, $projectInfo = NULL) {
	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('access administrator pages');

	if (!$isAdmin) return message('error', 'Access Denied');

	$initCmdKey='project:SETTING:'.$tpid;

	if (post('setting')) {
		property($initCmdKey,post('setting'));
		return 'SAVED';
	}

	$ret = '<header class="header">'._HEADER_BACK.'<h3>Project Page Setting Command</h3></header>';

	$initCmd=property($initCmdKey);

	$form = new Form([
		'action' => url('project/'.$tpid.'/page.setting'),
		'class' => 'sg-form',
		'rel' => 'notify',
		'children' => [
			'setting' => [
				'type'=>'textarea',
				'label'=>'Setting JSON',
				'class'=>'-fill',
				'rows'=>20,
				'value'=>htmlspecialchars($initCmd),
			],
			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
				'container' => '{class: "-sg-text-right"}'
			],
		], // children
	]);

	$ret.=$form->build();

	$defaultSetting = '{
"isCreateChild": "MEMBER",
"showAddNewActionButton": true,
"forceDeleteProject": true,
"grant": {
	"by": "สสส.",
	"pass": "สจรส. ม.อ."
	},
"budget" : {
	"show": "public"
	},
"activity": {
	"field" : "objectiveDetail, mainactDetail, targetPreset, targetJoin, actionPreset, actionReal, outputPreset, outputReal, rate1, problem, recommendation, support, followerRecommendation,followerName"
	},
"strings": {
	"โครงการ": "นวัตกรรม"
	}
}';

	$ret .= '<textarea class="form-textarea -fill" rows="20">'.$defaultSetting.'</textarea>';

	$ret .= '
	budget.show = public : สาธารณะ - ทุกคนสามารถดูได้, member : สมาชิก - เฉพาะสมาชิกเท่านั้น, team : ทีมงาน - เฉพาะทีมงานโครงการเท่านั้น, admin : ผู้ดูแลระบบ - เฉพาะผู้ดูแลระบบเท่านั้น, org : องค์กร - เฉพาะสมาชิกของหน่วยงานเท่านั้น<br />
	';
	return $ret;
}
?>