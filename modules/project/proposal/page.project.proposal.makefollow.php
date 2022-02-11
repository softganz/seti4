<?php
/**
* Create Project From Development
*
* @param Object $self
* @param Int $tpid
* @return String
*/

function project_proposal_makefollow($self, $proposalInfo) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	R::View('project.toolbar', $self, $proposalInfo->info->title, 'proposal', $proposalInfo);

	$isWebAdmin = $proposalInfo->RIGHT & _IS_ADMIN;

	$isCreateFollow = R::Model('project.right.develop.createfollow',$proposalInfo);

	if (!$isCreateFollow) return message('error','access denied');

	$isRemoveProjectData = $isWebAdmin && post('delproject');


	// Remove all follow data before create follow project
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

		$proposalInfo = R::Model('project.develop.get', $tpid);
	}



	if (!$isRemoveProjectData && $proposalInfo->followId) return message('error','โครงการนี้อยู่ในกระบวนการติดตามโครงการเรียบร้อยแล้ว ไม่สามารถสร้างซ้ำได้');

	// Show message if not confirm
	$ret .= '<header class="header"><h3>สร้างติดตามโครงการจากพัฒนาโครงการ</h3></header>';

	$ret .= '<p style="padding: 32px 0;">หากต้องการดำเนินการต่อ กรุณาคลิก ยืนยันการสร้างติดตามโครงการ</p><br />';
	$ret .= '<nav class="nav -sg-text-right"> <a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a> ';

	if ($isRemoveProjectData) {
		$ret .= '<a class="sg-action btn -danger" href="'.url('project/proposal/'.$tpid.'/info/makefollow',array('delproject' => 'yes')).'" data-rel="#main" data-title="ลบข้อมูลก่อนสร้างติดตามโครงการ" data-confirm="ต้องการสร้างติดตามโครงการ โดยลบข้อมูลติดตามโครงการทิ้งก่อน กรุณายืนยัน?"><i class="icon -save -white"></i><span>สร้างโครงการติดตามโดยลบข้อมูลติดตามโครงการก่อน</span></a>';
	} else {
		$ret .= '<a class="sg-action btn -primary" href="'.url('project/proposal/'.$tpid.'/info/makefollow',array('confirm' => 'yes')).'" data-rel="notify" data-done="reload:'.url('project/'.$tpid).'"><i class="icon -save -white"></i><span>ยืนยันการสร้างติดตามโครงการ</span></a>';
	}
	$ret .= '</nav>';

	$ret .= '<br /><br /><p>คำเตือน : การสร้างติดตามโครงการจากพัฒนาโครงการ จะสามารถทำได้เพียงครั้งเดียวเท่านั้น โดยจะสำเนาข้อมูลจากพัฒนาโครงการ ไปเป็นข้อมูลการติดตามโครงการ หากมีการแก้ไขพัฒนาโครงการในภายหลัง จะไม่ส่งผลต่อข้อมูลติดตามโครงการ</p>';

	//$ret .= print_o($proposalInfo, '$proposalInfo');
	return $ret;
}

?>