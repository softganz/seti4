<?php
/**
* Project :: View Follow Information
* Created 2021-01-27
* Modify  2021-02-02
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/app/follow/{id}
*/

$debug = true;

function project_app_follow_view_v1($self, $projectInfo) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$getPeriod = SG\getFirst(post('period'), date('m') - 1);
	$getChangwat = post('prov');

	//$periodInfo = R::Model('project.period.get', $projectId, $getPeriod);
	//$getMonth = SG\getFirst(post('m'), date('Y-m'));
	$getMonth = sg_date('2021-'.sprintf('%02d', ($getPeriod+1)).'-01','Y-m');
	//debugMsg('2021-'.sprintf('%02d', ($getPeriod+1)).'-01');
	//debugMsg('$getMonth = '.$getMonth.($getPeriod+1));

	//$periodList = R::Model('project.period.get', $projectId);
	//debugMsg($projectInfo,'$projectInfo');
	//debugMsg($periodInfo,'$periodInfo');

	$isAdmin = is_admin('project');
	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	$approvePeriod = date('m') - 1;
	$maxPeriod = 0;

	$cfgFollow = cfg('project')->follow;

	if ($projectInfo->info->prtype == 'ชุดโครงการ') {
		$childCondition = new stdClass();
		$childCondition->childOf = $projectId;
		$childCondition->status = "process";
		$childCondition->changwat = $getChangwat;

		$childList = R::Model('project.follows', $childCondition, '{order: "CONVERT(t.`title` USING tis620)", sort: "ASC", items: "*", debug: false}');

		list($tambonCard, $followCard, $orgCard) = __project_app_follow_list($childList);

		if ($followCard->count()) {
			foreach ($childList->items as $rs) $childId[] = $rs->tpid;

			$projectIdList = 'SET:'.implode(',',$childId);

			$stmt = 'SELECT `period`, `date1` `dateStart`, `date2` `dateEnd`
				FROM %project_tr% p
				WHERE `tpid` IN (:projectIdList) AND `formid` = "info" AND `part` = "period"
				GROUP BY `period`
				ORDER BY `period` ASC;
				-- {key: "period"}';
			$periodList = mydb::select($stmt, ':projectIdList', $projectIdList)->items;
			//debugMsg(mydb()->_query);
			//debugMsg($periodList, '$periodList');

			$firstDateOfMonth = sg_date($periodList[$getPeriod]->dateEnd, 'Y-m-01');
			$endDateOfMonth = sg_date($periodList[$getPeriod]->dateEnd, 'Y-m-t');

			$maxPeriod = end($periodList)->period;

			$stmt = 'SELECT
				-- a.`tpid`, a.`trid`, a.`date1`, pd.`period`, pd.`date1` `dateStart`, pd.`date2` `dateEnd`
				pd.`tpid`
				, COUNT(a.`trid`) `actions`
				, COUNT(DISTINCT a.`date1`) `days`
				, pd.`flag`
				, pd.`detail3` `approveDate`
				, pd.`detail4` `paidDate`
				, pd.`refcode` `paidStatus`
				FROM %project_tr% pd
				LEFT JOIN %project_tr% a ON a.`tpid` = pd.`tpid` AND a.`formid` = "activity" AND a.`part` = "owner" AND a.`date1` BETWEEN pd.`date1` AND pd.`date2`
				WHERE pd.`tpid` IN (:projectIdList) AND pd.`formid` = "info" AND pd.`part` = "period" AND pd.`period` = :period
				GROUP BY `tpid`
				-- {key: "tpid"}';
			$actionList = mydb::select($stmt, ':projectIdList', $projectIdList, ':period', $getPeriod)->items;

			// debugMsg(mydb()->_query);
			// debugMsg($actionList, '$actionList');

			$stmt = 'SELECT `trid`, `tpid`, `date1` `assignMonth` FROM %project_tr% WHERE `formid` = "info" AND `part` = "assign" AND `tpid` IN (:projectIdList) AND `date1` = :firstDateOfMonth; -- {key: "tpid"}';
			$assignList = mydb::select($stmt, ':projectIdList', $projectIdList, ':firstDateOfMonth', $firstDateOfMonth)->items;
			//debugMsg($assignList, '$assignList');

			$stmt = 'SELECT `qtref`, `qtdate`, `tpid` FROM %qtmast% WHERE `tpid` IN (:projectIdList) AND `qtdate` = :endDateOfMonth AND `qtform` = "psi"; -- {key: "tpid"}';
			$kpiList = mydb::select($stmt, ':projectIdList', $projectIdList, ':endDateOfMonth', $endDateOfMonth)->items;
			//debugMsg($kpiList, '$kpiList');

		}
	}



	// View model
	$toolbar = new Toolbar($self, $projectInfo->title.' ('.$cfgFollow->ownerType->{$projectInfo->info->ownertype}->title.')');
	$toolbarNav = new Ui();
	$toolbarMoreNav = new Ui();

	$pageNav = new Ui();
	$pageNav->config('container', '{tag: "nav", class: "nav -page -sg-text-center"}');

	$ret = '';

	if ($projectInfo->info->prtype == 'โครงการ') {
		if ($isEdit) {
			$toolbarNav->add('<a class="sg-action" href="'.url('project/app/month/'.$projectId).'" data-webview="'.$projectInfo->title.'"><i class="icon -material">add_task</i><span>สรุปงาน</span></a>');
		}

		if ($isAdmin) {
			$toolbarMoreNav->add('<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียดโครงการ</span></a>');
			if (in_array($projectInfo->info->ownertype, [_PROJECT_OWNERTYPE_GRADUATE,_PROJECT_OWNERTYPE_STUDENT,_PROJECT_OWNERTYPE_PEOPLE])) $toolbarMoreNav->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.tambon.move').'" data-rel="box" data-width="480"><i class="icon -material">drive_file_move</i><span>ย้ายตำบล</span></a>');
		}

		$toolbar->addNav('main', $toolbarNav);
		if ($toolbarMoreNav->count()) $toolbar->addNav('more', $toolbarMoreNav);

		$ret .= '<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', array('id' => $projectId)).'">'._NL;
		$ret .= '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>';
		$ret .= '</section><!-- project-app-activity -->';
	} else {
		if ($isEdit && $projectInfo->info->ownertype == _PROJECT_OWNERTYPE_UNIVERSITY) {
			$pageNav->add('<a class="sg-action btn'.(!$followCard->count() ? ' -primary' : '').'" href="'.url('project/'.$projectId.'/info.tambon.add').'" data-rel="box" data-width="480" data-height="90%" data-webview="โครงการตำบล"><i class="icon -material">loupe</i><span>เพิ่มโครงการตำบล</span></a>');
		} else if ($isEdit && $projectInfo->info->ownertype == _PROJECT_OWNERTYPE_TAMBON) {
			$pageNav->add('<a class="sg-action btn'.(!$followCard->count() ? ' -primary' : '').'" href="'.url('project/'.$projectId.'/info.child.add').'" data-rel="box" data-width="480" data-webview="ผู้รับจ้าง"><i class="icon -material">person_add</i><span>เพิ่มผู้รับจ้าง</span></a>');
		}

		if ($tambonCard->count()) {
			$stmt = 'SELECT t.`tpid`, t.`areacode`, t.`title`, p.`ownertype`, LEFT(t.`areacode`,2) `changwatCode`, cop.`provname` `changwatName`, COUNT(*) `totalProject`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
				WHERE t.`parent` = :projectId AND p.`ownertype` = "tambon"
				GROUP BY `changwatCode`
				ORDER BY CONVERT(`changwatName` USING tis620)';
			$dbs = mydb::select($stmt, ':projectId', $projectId);
			$tambonNav = new Ui();
			$tambonNav->config('container', '{tag: "nav", class: "nav"}');
			foreach ($dbs->items as $rs) {
				$selectChangwat .= '<option value="'.$rs->changwatCode.'"'.($rs->changwatCode == $getChangwat ? ' selected="selected"' : '').'>'.$rs->changwatName.' ('.$rs->totalProject.' ตำบล)</option>';
			}
			$tambonNav->add('<form method="get" action="'.url('project/app/follow/'.$projectId).'"><select class="form-select" name="prov" onChange="this.form.submit()"><option value="">==ทุกจังหวัด==</option>'.$selectChangwat.'</select></form>');
			$ret .= '<div class="header"><h3>ตำบล</h3>'.$tambonNav->build().'</div>';
			$ret .= $tambonCard->build();
		}

		if ($orgCard->count()) {
			$ret .= '<div class="header"><h3>ส่วนงาน</h3></div>';
			$ret .= $orgCard->build();
		}

		if ($projectInfo->link) {
			$linkCard = new Ui('div', 'ui-card');
			$linkCard->header('<h3>ตำบล</h3>');
			foreach ($projectInfo->link as $rs) {
				$linkCard->add(
					'<div class="detail"><a href="'.url('project/app/follow/'.$rs->projectId).'">'.$rs->title.'</div></a>',
					array(
						'class' => 'sg-action',
						'href' => url('project/app/follow/'.$rs->projectId),
						'data-webview' => $rs->title,
					)
				);
			}
			$ret .= $linkCard->build();
		}

		if ($followCard->count()) {

			$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('period' => $getPeriod > 1 ? $getPeriod - 1 : 1)).'"><i class="icon -material">navigate_before</i><span>&nbsp</span></a>');
			$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('period' => $getPeriod)).'"><i class="icon -material">calendar_today</i><span>'.sg_date($periodList[$getPeriod]->dateEnd,'ดด ปปปป').'</span></a>');
			$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('period' => $getPeriod < $maxPeriod ? $getPeriod+1 : $maxPeriod)).'"><i class="icon -material">navigate_next</i><span>&nbsp</span></a>');

			if ($isEdit) {
			}

			$tables = new Table();
			$tables->thead = array(
				'name -nowrap' => 'ผู้รับจ้าง',
				'type -center' => 'ประเภท',
				'days -nowrap -center' => 'วัน/ครั้ง',
				'assign -nowrap -center' => 'มอบงาน',
				'kpi -nowrap -center' => 'ตัวชี้วัด',
				//'send  -nowrap -center' => 'ส่ง',
				'check -nowrap -center' => 'ตรวจ',
				'approve -nowrap -center' => 'รับรอง',
			);

			foreach ($childList->items as $rs) {
				if ($rs->prtype != 'โครงการ') continue;
				$userPeriodInfo = $actionList[$rs->tpid];

				if ($assignList[$rs->tpid]) {
					$assignIcon = '<i class="icon -material -green">check_circle</i>';
				} else if ($isEdit && !$assignList[$rs->tpid]) {
					$assignIcon = '<i class="icon -material -gray">add_circle</i>';
				} else {
					$assignIcon = '<i class="icon -material -gray">check_circle</i>';
				}

				$kpiIcon = ($hasKpi = $kpiList[$rs->tpid]) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">check_circle</i>';

				$sendIcon = ($hasSend = $userPeriodInfo->flag >= _PROJECT_PERIOD_FLAG_SEND) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">check_circle</i>';

				$checkedIcon = ($hasChecked = $userPeriodInfo->flag >= _PROJECT_PERIOD_FLAG_MANAGER) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">check_circle</i>';
				if ($getPeriod > $approvePeriod) {
					$checkedIcon = '';
				} else if ($isEdit && !$hasChecked && $getPeriod <= $approvePeriod) {
					$checkedIcon = '<i class="icon -material -gray">add_circle</i>';
				}


				$approvedIcon = ($hasApproved = $userPeriodInfo->flag >= _PROJECT_PERIOD_FLAG_GRANT) ? '<i class="icon -material -green">check_circle</i>' : '<i class="icon -material -gray">check_circle</i>';
				if ($getPeriod > $approvePeriod) {
					$approvedIcon = '';
				} else if ($isEdit && $hasChecked && !$hasApproved) {
					$approvedIcon = '<i class="icon -material -gray">add_circle</i>';
				}

				$tables->rows[] = array(
					'<a class="sg-action" href="'.url('project/app/follow/'.$rs->tpid).'" data-webview="'.$rs->title.'">'.$rs->title.'</a>',
					$cfgFollow->ownerType->{$rs->ownertype}->title,
					$userPeriodInfo->actions ? $userPeriodInfo->days.'/'.$userPeriodInfo->actions : '-',
					($isEdit ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.assign.form/'.$assignList[$rs->tpid]->trid, array('id' => $rs->projectId, 'assignMonth' => $getMonth)).'" data-rel="box" data-width="480">'.$assignIcon.'</a>' : $assignIcon),
					$hasKpi ? '<a class="sg-action" href="'.url('project/'.$rs->projectId.'/info.kpi/'.$hasKpi->qtref).'" data-rel="box" data-width="480">'.$kpiIcon.'</a>' : $kpiIcon,
					//$hasSend ? '<a class="sg-action" href="'.url('project/'.$rs->projectId.'/info.send/'.$hasSend->trid).'" data-rel="box" data-width="480">'.$sendIcon.'</a>' : $sendIcon,
					$isEdit && !$userPeriodInfo->approveDate && $getPeriod <= $approvePeriod ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.check/'.$getPeriod, array('child' => $rs->projectId)).'" data-rel="box" data-width="480">'.$checkedIcon.'</a>' : $checkedIcon,
					$isEdit && $hasChecked ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.approve/'.$getPeriod, array('child' => $rs->projectId)).'" data-rel="box" data-width="480">'.$approvedIcon.'</a>' : $approvedIcon,
				);
			}

			$ret .= '<div style="overflow: auto;">'.$tables->build().'</div>';

			if ($isEdit) {
				$pageNav->add('<a class="sg-action btn -primary" href="'.url('project/app/follow/'.$projectId.'/report.send/'.$getPeriod).'" data-webview="ใบตรวจงาน"><i class="icon -material">print</i><span>พิมพ์ใบตรวจงาน</span></a>');
			}
			$parentInfo = R::Model('project.get', $projectInfo->info->parent, '{data: "info"}');
			$parentProperty = SG\json_decode($parentInfo->info->property);
			//$ret .= print_o($parentProperty, '$parentProperty');
		}

		$toolbarNav->add('<a class="sg-action" href="'.url('project/app/follow/'.$projectId.'/plan').'" data-webview="แผนงาน"><i class="icon -material">event</i><span>แผนกิจกรรม</span></a>');
		$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId.'/report.summary', array('m' => $getMonth)).'"><i class="icon -material">description</i><span>สรุปโครงการ</span></a>');
		if ($isEdit) {
			$toolbarNav->add('<a href="'.url('project/'.$projectId.'/info.dashboard.app').'"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');
		}

		if ($isAdmin) {
			$toolbarMoreNav->add('<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียดโครงการ</span></a>');
		}

		$toolbar->addNav('main', $toolbarNav);

		if ($toolbarMoreNav->count()) $toolbar->addNav('more', $toolbarMoreNav);

		//$ret .= '@'.date('H:i:s');

	}

	$ret .= $pageNav->build();

	$ret .= '<p>โครงการเข้าสู่ระบบโดย <img src="'.model::user_photo($projectInfo->info->username).'" width="24" height="24" alt="'.htmlspecialchars($projectInfo->info->ownerName).'" /> '.$projectInfo->info->ownerName.' เมื่อวันที่ '.sg_date($projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>';

	//$ret .= print_o($actionList, '$actionList');
	//$ret .= print_o($childList, '$childList');
	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>