<?php
/**
* Project develope create new
*
* @param Object $self
* @return String
*/

import('model:project.proposal.php');

function project_proposal_create($self, $projectSetId = NULL) {
	R::View('project.toolbar', $self, 'เริ่มพัฒนาโครงการใหม่', 'develop', NULL,'{showPrint: false}');

	$maxDevProjectAllow = cfg('PROJECT.DEVELOP.MAX_PER_USER');

	if (!user_access('create project proposal')) return message('error','access denied');

	if (!user_access('administer contents')) {
		$dbs = mydb::select('SELECT `tpid` FROM %topic% WHERE type = "project-develop" AND uid = :uid AND `status` IN (1,2,3)',':uid',i()->uid);
		if ($maxDevProjectAllow == 1 && $dbs->_num_rows == 1) {
			return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'จำนวนข้อเสนอโครงการเต็มโควต้าแล้ว!!!']);
		} else if ($maxDevProjectAllow == 0 || $dbs->_num_rows < $maxDevProjectAllow) {
			// Allow to add new develop project
		} else {
			return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'จำนวนข้อเสนอโครงการเต็มโควต้าแล้ว!!!']);
		}
	}



	// Check start date in config
	$curDate = date('Y-m-d H:i');
	$cfgStartDate = cfg('project.develop.startdate');

	if ($cfgStartdate) {
		if ( ($curDate >= cfg('project.develop.startdate') && $curDate<=cfg('project.develop.enddate')) ) {
			; // do nothing
		} else {
			$msg = 'ปิดรับการพัฒนาโครงการ : ขออภัย : ช่วงนี้งดรับพัฒนาโครงการใหม่<br />';

			$cfgEndDate=cfg('project.develop.enddate');
			if (empty($cfgStartDate) || $cfgStartDate<$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไปยังไม่ได้กำหนด';
			else if ($cfgStartDate>$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไป คือ <strong>'.sg_date($cfgStartDate,'ว ดดด ปปปป H:i').' น. - '.sg_date($cfgEndDate,'ว ดดด ปปปป H:i').' น.</strong>';

			$ret.=message('error',$msg);

			//.(cfg('project.develop.startdate')?'<br />ช่วงเวลาเปิดรับพัฒนาโครงการคือ '.sg_date(cfg('project.develop.startdate'),'ว ดดด ปปปป H:i').' น. ถึง '.sg_date(cfg('project.develop.enddate'),'ว ดดด ปปปป H:i').' น.':''));
			return $ret;
		}
	}

	// Create new project proposal
	$post = (object) post('topic');
	if ($post->title) {
		$data = (Object) [
			'title' => $post->title,
			'orgId' => $post->orgId,
			'budget' => $post->budget,
			'areacode' => $post->areacode,
			'changwat' => $post->changwat,
			'ampur' => $post->ampur,
			'tambon' => $post->tambon,
			'projectset' => $post->parent,
			'thread' => $post->previd,
			'pryear' => $post->pryear,
			'date_approve' => post('date_approve') ? sg_date(post('date_approve'), 'Y-m-d') : NULL,
		];
		if ($data->date_approve) {
			$data->pryear = sg_date($data->date_approve,'Y');
		}

		$result = ProjectProposalModel::create($data, '{debug: false}');

		if (post('refid')) {
			$stmt = 'UPDATE %project_tr% SET `refid` = :tpid WHERE `trid` = :refid LIMIT 1';
			mydb::query($stmt, ':tpid', $result->tpid, ':refid', post('refid'));
			//$ret.=mydb()->_query;
		}
		// $ret.=print_o($result,'$result');
		//$ret.=print_o($post,'$post');
		//$ret.=print_o($data,'$data');
		//$ret.=print_o($fundInfo,'$fundInfo');

		$_SESSION['mode'] = 'edit';
		location('project/proposal/'.$result->tpid);

		return $ret;
	}
}
?>