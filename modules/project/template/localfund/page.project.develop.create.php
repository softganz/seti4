<?php
/**
* Project develope create new
*
* @param Object $self
* @return String
*/

function project_develop_create($self) {
	R::View('project.toolbar',$self,'เริ่มพัฒนาโครงการใหม่ (กองทุนตำบล)','develop');

	$maxDevProjectAllow=cfg('PROJECT.DEVELOP.MAX_PER_USER');

	if (!user_access('create project proposal')) return message('error','access denied');
	if (!user_access('administer contents')) {
		$dbs=mydb::select('SELECT `tpid` FROM %topic% WHERE type="project-develop" AND uid=:uid AND `status` IN (1,2,3)',':uid',i()->uid);
		if ($maxDevProjectAllow==1 && $dbs->_num_rows==1) {
			location('project/develop/'.$dbs->items[0]->tpid);
		} else if ($maxDevProjectAllow==0 || $dbs->_num_rows<$maxDevProjectAllow) {
			// Allow to add new develop project
		} else {
			location('project/my');
		}
	}

	$ret.='<h3>รายละเอียดโครงการที่จะเริ่มพัฒนาใหม่</h3>';

	$curDate=date('Y-m-d H:i');

	if ( ($curDate>=cfg('project.develop.startdate') && $curDate<=cfg('project.develop.enddate')) ) {
		; // do nothing
	} else {
		$msg='ปิดรับการพัฒนาโครงการ : ขออภัย : ช่วงนี้งดรับพัฒนาโครงการใหม่<br />';

		$startDate=cfg('project.develop.startdate');
		$endDate=cfg('project.develop.enddate');
		if (empty($startDate) || $startDate<$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไปยังไม่ได้กำหนด';
		else if ($startDate>$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไป คือ <strong>'.sg_date($startDate,'ว ดดด ปปปป H:i').' น. - '.sg_date($endDate,'ว ดดด ปปปป H:i').' น.</strong>';

		$ret.=message('error',$msg);

		//.(cfg('project.develop.startdate')?'<br />ช่วงเวลาเปิดรับพัฒนาโครงการคือ '.sg_date(cfg('project.develop.startdate'),'ว ดดด ปปปป H:i').' น. ถึง '.sg_date(cfg('project.develop.enddate'),'ว ดดด ปปปป H:i').' น.':''));
		return $ret;
	}

	// Insert new development project
	$post=(object)post('topic');
	if ($post->title) {
		$data = new stdClass();
		$data->title = $post->title;
		$data->budget = $post->budget;
		$data->changwat = $post->changwat;
		$data->projectset = $post->parent;
		$data->date_approve = post('date_approve') ? sg_date(post('date_approve'), 'Y-m-d') : NULL;
		if ($data->date_approve) {
			$data->pryear = sg_date($data->date_approve,'Y');
		}

		$result = R::Model('project.develop.create', $data);

		if (post('refid')) {
			$stmt = 'UPDATE %project_tr% SET `refid` = :tpid WHERE `trid` = :refid LIMIT 1';
			mydb::query($stmt, ':tpid', $result->tpid, ':refid', post('refid'));
			//$ret.=mydb()->_query;
		}

		// $ret.=print_o($result,'$result');
		// $ret.=print_o($post,'$post');
		// $ret.=print_o($data,'$data');
		// $ret.=print_o($fundInfo,'$fundInfo');

		location('project/develop/'.$result->tpid.'/view/edit');
		return $ret;
	}

	return $ret;
}
?>