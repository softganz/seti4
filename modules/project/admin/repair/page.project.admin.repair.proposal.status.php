<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_admin_repair_proposal_status($self) {
	R::View('project.toolbar',$self,'Project Setting','admin');
	$self->theme->sidebar=R::View('project.admin.menu','setting');

	$ret = '<h3>ปรับปรุงสถานะโครงการที่ผ่านการเป็นโครงการติดตาม</h3>';


	if (!SG\confirm()) {
		$ret .= '<p>ปรับปรุงสถานะโครงการที่ผ่านการเป็นโครงการติดตาม?<br />กรุณายืนยันการปรับปรุง<br /><nav class="nav -page -sg-text-right"><a class="btn -danger" href="'.url('project/admin/repair/proposal/status',array('confirm'=>'Yes')).'"><i class="icon -material -white">done</i><span>ยืนยันการปรับปรุง</span></a></nav></p><p><b>คำเตือน</b> ควรสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการปรับปรุงข้อมูล</p>';
		return $ret;
	}


	$stmt = 'UPDATE %project_dev% d
		RIGHT JOIN `sgz_project` p USING(`tpid`)
		SET d.`status` = 10';

	mydb::query($stmt);

	$ret .= message('status', 'ดำเนินการเรียบร้อย');
	$ret .= mydb()->_query;

	return $ret;
}
?>