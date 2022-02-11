<?php
/**
* Vew personal health information
*
* @param Integer $id
* @return String
*/
function imed_poorman_info($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));
	$qtref = post('qt');

	$personInfo = R::Model('imed.patient.get', $psnId);

	$zones = imed_model::get_user_zone(i()->uid,'imed.poorman');

	$isAdmin = is_admin('imed');
	//R::View('imed.toolbar',$self,'คนยากลำบาก','none');

	//$ret .= print_o($personInfo,'$personInfo');
	//$ret .= print_o($zones,'$zones');
	$isInMyZone = imed_model::in_my_zone($zones,$personInfo->info->changwat,$personInfo->info->ampur,$personInfo->info->tambon);

	mydb::where('`qtform` = 4 AND `psnid` = :psnid', ':psnid', $psnId);

	if (!($isAdmin || $isInMyZone)) {
		mydb::where('`uid` = :uid', ':uid', i()->uid);
	}

	$stmt = 'SELECT `qtdate`, `psnid`, `qtref`, `qtstatus`
		FROM %qtmast%
		%WHERE%';

	$dbs = mydb::select($stmt);

	//$ret.=print_o($dbs,'$dbs');

	$statusList = array(
		_START => 'กำลังป้อน',
		_DRAFT => 'แก้ไข',
		_WAITING => 'รอตรวจ',
		_COMPLETE => 'อนุมัติ',
		_CANCEL => 'ยกเลิก',
		_REJECT => 'ไม่ผ่าน'
	);

	$floatingMenu .= '<div class="btn-floating -right-bottom">'
		. '<a class="sg-action btn -floating -circle48" href="'
		. url('imed/poorman/qt/create/'.$psnId,array('ref' => 'imed'))
		. '" data-rel="#imed-app" data-done="moveto: 0,0" data-webview="เพิ่มแบบสอบถามคนยากลำบาก" data-title="เพิ่มแบบสอบถามคนยากลำบาก" data-confirm="ต้องการเพิ่มแบบสอบถามคนยากลำบาก กรุณายืนยัน?"><i class="icon -addbig -white"></i></a></div>';

	if ($qtref) {
		$ret .= R::Page('imed.app.poorman.form',$self,$qtref);
	} else if ($dbs->_num_rows >= 1) {
		$tables = new Table();
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				sg_date($rs->qtdate,'ว ดด ปปปป'),
					'<a class="sg-action" href="'.url('imed/poorman/info/'.$psnId, array('qt' => $rs->qtref, 'ref'=>'imed')).'" data-rel="#imed-app" data-done="moveto: 0,0" data-webview="แบบสอบถามคนยากลำบาก">แบบสอบถาม #'.$rs->qtref.'</a>',
					$statusList[$rs->qtstatus],
				);
		}
		$ret .= $tables->build();
	} else {
		$ret .= '<p class="notify">ไม่มีข้อมูลคนยากลำบาก</p>';
	}


	if (empty($qtref)) $ret .= $floatingMenu;

	//$ret .= '<a href="'.url('imed/app/poorman/form').'">ADD</a>';

	$ret .= '<style type="text/css">
	.form-item.-edit-save {display:none;}
	.xbtn-floating.-poorman-app {display: none;}
	</style>';
	return $ret;
}
?>