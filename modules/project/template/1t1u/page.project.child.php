<?php
/**
* iMed :: My Patient List bt Card
* Created 2020-09-28
* Modify  2020-09-28
*
* @param Object $self
* @return String
*
* @usage imed/my/patient/card
*/

$debug = true;

function project_child($self, $projectInfo = NULL) {
	if (!i()->ok) return;

	if (is_object($projectInfo)) {
		$projectId = $projectInfo->projectId;		
	} else {
		$projectId = $projectInfo;
		unset($projectInfo);
	}


	$ret = '';

	if ($projectId) {
		mydb::where('t.`parent` = :parent', ':parent', $projectId);
	} else {
		mydb::where('(t.`uid` = :uid OR tu.`uid` = :uid)', ':uid', i()->uid);
	}

	mydb::where('p.`project_status` = "กำลังดำเนินโครงการ"');

	$stmt = 'SELECT
		p.`tpid`, t.`title`, u.`username`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = p.`tpid` AND `formid` = "activity" AND `part` = "owner") `actionTime`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u ON u.`uid` = t.`uid`
			LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
		%WHERE%
		GROUP BY `tpid`
		LIMIT 20';

	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$cardUi = new Ui('div', 'ui-card');
	$cardUi->addConfig('nav', '{class: "nav -patient-card"}');

	foreach ($dbs->items as $rs) {
		$cardStr = '';

		$cardStr .=  '<div class="imed-patient-photo">'
			. '<a class="sg-action" href="'.url('project/app/follow/'.$rs->tpid).'" data-webview="'.htmlspecialchars($rs->title).'" title="'.htmlspecialchars($rs->title).'"><img class="-photo" src="'.model::user_photo($rs->username).'" width="100%" height="100%"/></a>'
			. '<span class="-name">'.$rs->title.'</span>'
			. '<span class="-number">'.$rs->actionTime.'</span>'
			. '</div>';

		$cardUi->add($cardStr);
	}

	$ret .= $cardUi->build();

	//$ret .= print_o($dbs, '$dbs');

	$ret .= '<style type="text/css">
	.nav.-patient-card {width: 100%; overflow: scroll;}
	.nav.-patient-card .ui-card {display: flex; flex-wrap: nowrap;}
	.nav.-patient-card .ui-item {margin: 0 4px; border-radius: 16px; position: relative; padding: 4px; text-align: center;}
	.nav.-patient-card .ui-item:first-child {margin-left: 8px;}
	.nav.-patient-card .ui-item:last-child {margin-right: 16px;}
	.nav.-patient-card span {display: block; position: absolute;}
	.nav.-patient-card .-name {bottom: 0; left: 0; right: 0; font-size: 0.7em; height: 1.5em; overflow: hidden;}
	.nav.-patient-card .-number {top: 4px; right: 4px; width: 2em; height: 2em; line-height: 2em; text-align: center; border-radius: 50%; background-color: #ccc; font-size: 0.6em;}
	.nav.-patient-card .-photo {width: 64px; height: 64px; margin: 0px auto 8px; display: block; border-radius: 50%; border: 2px #eee solid;}
	</style>';

	return $ret;
}
?>