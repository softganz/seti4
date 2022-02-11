<?php
function project_develop_delete($self, $tpid) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid);
	$tpid = $devInfo->tpid;

	R::View('project.toolbar',$self,$devInfo->info->title,'develop',$devInfo);

	if (empty($tpid)) return $ret.message('error','ขออภัย : ไม่มีโครงการที่กำลังพัฒนาอยู่ในระบบ');

	$isDeletable = R::Model('project.right.develop.delete',$devInfo);

	if (!$isDeletable) return message('error','access denied');

	if ($devInfo->info->followId) return message('error','โครงการเข้าสู่ระบบติดตามแล้ว ไม่สามารถลบทิ้งได้!!!!');


	if ($tpid && SG\confirm()) {
		// Start delete project develop
		$ret .= message('notify','ลบพัฒนาโครงการเรียบร้อย');

		if (empty($devInfo->info->followId)) {
			// No project
			mydb::query('DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid);

			mydb::query('DELETE FROM %topic_revisions% WHERE tpid = :tpid LIMIT 1',':tpid',$tpid);

			mydb::query('DELETE FROM %topic_user% WHERE `tpid` = :tpid',':tpid',$tpid);
		} else {
			// Have project
			// Do nothing
		}

		mydb::query('DELETE FROM %project_dev% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid);

		mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "develop"',':tpid',$tpid);

		mydb::query('DELETE FROM %bigdata% WHERE `keyname` = "project.develop" AND `keyid` = :tpid',':tpid',$tpid);

		mydb::query('UPDATE %project_tr% SET `refid` = NULL WHERE `formid`="info" AND `part`="project" AND `refid` = :tpid ', ':tpid',$tpid);

		model::watch_log(
			'project',
			'Proposal Delete',
			'Project id '.$tpid.' - '.$devInfo->title.' was removed by '.i()->name.'('.i()->uid.')'
		);

		location('project/my/develop');
	}
	return $ret;
}
?>