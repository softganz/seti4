<?php
/**
* Create Project From Development
*
* @param Object $self
* @param Int $tpid
* @return String
*/

function project_develop_createproject($self, $tpid) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;

	$debug = true;
	$tagname = 'develop';

	R::View('project.toolbar', $self, $devInfo->info->title, 'develop', $devInfo);

	$isWebAdmin = $devInfo->RIGHT & _IS_ADMIN; //i()->admin;

	if (empty($tpid)) return 'No project';

	$isCreateFollow = R::Model('project.right.develop.createfollow',$devInfo);

	if (!$isCreateFollow) return message('error','access denied');

	$isRemoveProjectData = $isWebAdmin && post('delproject');

	if ($isRemoveProjectData && SG\confirm()) {
		$stmt = 'DELETE FROM %project% WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %bigdata% WHERE `keyname` = "project.info" AND `keyid` = :tpid';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %project_prov% WHERE `tagname` = "info" AND `tpid` = :tpid';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` LIKE "info"';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %topic_parent% WHERE `tpid` = :tpid';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :tpid AND `tagname` LIKE "info%"';
		mydb::query($stmt, ':tpid', $tpid);

		$stmt = 'DELETE FROM %calendar% WHERE `tpid` = :tpid';
		mydb::query($stmt, ':tpid', $tpid);

		//debugMsg(mydb()->_query);

		$devInfo = R::Model('project.develop.get', $tpid);
	}



	if (!$isRemoveProjectData && $devInfo->info->followId) return message('error','โครงการนี้อยู่ในกระบวนการติดตามโครงการเรียบร้อยแล้ว ไม่สามารถสร้างซ้ำได้');

	// Show message if not confirm
	if (SG\confirm()) {
		$result = R::Model('project.develop.follow.create', $devInfo,'{debug: false}');

		if ($isRemoveProjectData) {
			$ret .= print_o($result, '$result');
		} else {
			$ret .= location('project/'.$tpid);
		}

	} else {
		$ret .= '<h3>สร้างติดตามโครงการจากพัฒนาโครงการ</h3>';
		$ret .= '<br /><br /><p>หากต้องการดำเนินการต่อ กรุณาคลิก ยืนยันการสร้างติดตามโครงการ</p><br />';
		$ret .= '<nav class="nav -sg-text-right"> <a class="btn -link -cancel" href="'.url('project/develop/'.$tpid).'"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></a> ';
		if ($isRemoveProjectData) {
			$ret .= '<a class="sg-action btn -danger" href="'.url('project/develop/'.$tpid.'/createproject',array('delproject' => 'yes')).'" data-rel="#main" data-confirm="ต้องการสร้างติดตามโครงการ โดยลบข้อมูลติดตามโครงการทิ้งก่อน กรุณายืนยัน?"><i class="icon -save -white"></i><span>สร้างโครงการติดตามโดยลบข้อมูลติดตามโครงการก่อน</span></a>';
		} else {
			$ret .= '<a class="btn -primary" href="'.url('project/develop/'.$tpid.'/createproject',array('confirm' => 'yes')).'"><i class="icon -save -white"></i><span>ยืนยันการสร้างโครงการติดตาม</span></a>';
		}
		$ret .= '</nav>';

		$ret .= '<br /><br /><p>คำเตือน : การสร้างติดตามโครงการจากพัฒนาโครงการ จะสามารถทำได้เพียงครั้งเดียวเท่านั้น โดยจะสำเนาข้อมูลจากพัฒนาโครงการ ไปเป็นข้อมูลการติดตามโครงการ หากมีการแก้ไขพัฒนาโครงการในภายหลัง จะไม่ส่งผลต่อข้อมูลติดตามโครงการ</p>';
	}

	//$ret .= print_o($devInfo, '$devInfo');
	return $ret;
}

?>