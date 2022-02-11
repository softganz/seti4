<?php
/**
* Project : App Account
* Created 2021-01-21
* Modify  2021-09-08
*
* @param Object $self
* @return String
*
* @usage project/app/my
*/

$debug = true;

function project_app_my($self) {
	// Data Model
	if (!i()->ok) return R::View('signform', '{showTime: false, time: -1}');

	$isWebAdmin = is_admin();
	$isAdmin = is_admin('project');
	$isAccessDev = in_array(i()->username, array('softganz','momo'));
	$isLocalHost = _DOMAIN_SHORT == 'localhost';



	// View Model
	$ui = new Ui();


	$ret = '<div class="my-profile-wrapper">';

	$ret .= '<div class="-photo"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('project/app/my').'"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '<div class="-name">'.i()->name.'</div>';
	$ret .= '</div>';

	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

	$mainUi->header('<h3>My Account</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('project/my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -material">account_circle</i><span>รายละเอียด</span></a>');
	$mainUi->add('<a class="sg-action'.(i()->username == 'demo' ? ' -disabled' : '').'" href="'.(i()->username == 'demo' ? 'javascript:void(0)' : url('my/change/password')).'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -material">visibility</i><span>{tr:Change Password}</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-done="reload:'.url('project/app/my').' | moveto:0,0"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');

	$ret .= $mainUi->build();


	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
	$mainUi->header('<h3>รายการของฉัน</h3>');
	$ret .= $mainUi->build();


	$otherUi = new Ui();
	$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
	$otherUi->header('<h3>อื่นๆ</h3>');

	$otherUi->add('<a class="sg-action" href="'.url('my/clear/cache').'" data-webview="ล้างแคช" data-options=\'{clearCache: true}\' data-rel="box" data-width="480"><i class="icon -material">clear</i><span>ล้างแคช</span></a>');

	$ret .= $otherUi->build();


	if ($isAdmin) {
		//if (i()->username == 'softganz') debugMsg(pageInfo());

		$adminUi = new Ui();
		$adminUi->addConfig('nav', '{class: "nav -app-menu"}');
		$adminUi->header('<h3>ผู้จัดการระบบ</h3>');

		if ($isWebAdmin) {
			$adminUi->add('<a class="sg-action" href="'.url('project/admin/monitor/realtime').'" data-webview="Realtime Monitor" data-options=\'{history: true}\'><i class="icon -material">alarm_on</i><span>Realtime</span></a>');
			$adminUi->add('<a href="'.url('admin/user/list').'"><i class="icon -material">groups</i><span>สมาชิก</span></a>');
		}

		if (i()->username == 'softganz') {
			if (_DOMAIN_SHORT == 'localhost') {
				if (R()->appAgent) {
					$adminUi->add('<a href="'.url('project/app/my',array('setting:app' => '{}')).'"><i class="icon -material">web</i><span>www</span></a>');
				} else {
					$adminUi->add('<a href="'.url('project/app/my',array('setting:app' => '{OS:%22Android%22,ver:%220.2.00%22,type:%22OTOU%22,dev:%22Softganz%22,theme:%22dark%22}')).'"><i class="icon -material">android</i><span>App</span></a>');
				}
			}

			// if (R()->appAgent->OS == 'Android') {
			// 	$host = preg_match('/^([0-9a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
			// 	$isProduction = $host == '1t1u';
			// 	$adminUi->add('<a class="sg-action" data-rel="none" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'" data-done="load:#main"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');
			// }
		}


		$bankUpdate = mydb::select(
			'SELECT COUNT(*) `total` FROM %bigdata% WHERE `keyname` = "project.info" AND `fldname` = "bankcheck" LIMIT 1'
		);


		$ret .= $adminUi->build();

		$stmt = 'SELECT `appsrc`,`appagent`,COUNT(*) `totalAction`, COUNT(DISTINCT `uid`) `totalUser`
			FROM %project_tr%
			WHERE `formid` = "activity" AND `created` >= :created
			GROUP BY `appsrc`,`appagent`
			ORDER BY `appsrc`, `appagent`;
			-- {sum: "totalAction,totalUser"}';
		$appServiceCount = mydb::select($stmt, ':created', date('U') - 31*24*60*60);

		$tables = new Table();
		$tables->addClass('-project-activity-source');
		$tables->thead = array(
			'App Agent',
			'totalUser -amt'=>'สมาชิก', 'userPercent -amt' => '%',
			'totalAction -amt'=>'กิจกรรม', 'actionPercent -amt' => '%'
		);
		foreach ($appServiceCount->items as $rs) {
			$tables->rows[] = array(
				$rs->appagent,
				number_format($rs->totalUser),
				number_format(($rs->totalUser/$appServiceCount->sum->totalUser)*100,2).'%',
				number_format($rs->totalAction),
				number_format(($rs->totalAction/$appServiceCount->sum->totalAction)*100,2).'%',
			);
		}
		$tables->tfoot[] = array(
			'รวม',
			number_format($appServiceCount->sum->totalUser),
			'',
			number_format($appServiceCount->sum->totalAction),
			''
		);
		$ret .= '<nav class="nav -app-menu"><header class="header"><h3>Service from source</h3><nav><ul><li><a class="btn -link">Last 31 Days</a></li></ul></nav></header>'.$tables->build().'</nav>';

		$stmt = 'SELECT
			FROM_UNIXTIME(`created`, "%Y-%m-%d") `postDate`
			, COUNT(*) `totalAction`
			, COUNT(DISTINCT `uid`) `totalUser`
			FROM %project_tr% a
			WHERE a.`formid` = "activity"
			GROUP BY `postDate`
			ORDER BY `postDate` DESC
			LIMIT 30;
			';

		$activityCount = mydb::select($stmt);

		$tables = new Table();
		$tables->thead = array('postdate -date' => 'วันที่', 'users -amt' => 'สมาชิก', 'total -amt' => 'กิจกรรม');
		foreach ($activityCount->items as $rs) {
			$tables->rows[] = array(
				sg_date($rs->postDate, 'ว ดด ปปปป'),
				number_format($rs->totalUser),
				number_format($rs->totalAction),
			);
		}

		$ret .= '<nav class="nav -app-menu"><header class="header"><h3>การบันทึกกิจกรรม</h3><nav><ul><li><a class="btn -link">Last 31 Days</a></li></ul></nav></header>'.$tables->build().'</nav>';

	}

	$ret .= '<style type="text/css">
	.item {font-size: 0.8em;}
	</style>';
	return $ret;
}
?>