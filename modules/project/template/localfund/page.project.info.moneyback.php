<?php
/**
* Show Project Money Back Detail
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_info_moneyback($self, $projectInfo, $tranId = NULL) {
	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PROCESS ERROR');

	$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN; 
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isMember = $isAdmin || $projectInfo->info->membershipType;
	$isOfficer = $isAdmin
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
		|| in_array($projectInfo->orgMemberShipType, array('ADMIN','OFFICER'));

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	if (!$isOfficer) return message('error', 'Access Denied');

	$conditions = new stdClass();
	if (post('ref')) {
		$conditions->refcode = post('ref');
	}

	$moneybackInfo = R::Model('project.moneyback.get', $tpid, $tranId, $conditions);

	if (!$moneybackInfo) return message('error', 'ไม่มีข้อมูลใบรับเงินคืน');

	$tranId = $moneybackInfo->tranId;

	$ui = new Ui();
	if (empty($moneybackInfo->rcvdate) || $moneybackInfo->rcvdate > $fundInfo->finclosemonth) {
		$ui->add('<a><i class="icon -material -gray">lock_open</i></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.moneyback.form/'.$tranId).'" data-rel="box"><i class="icon -material">edit</i></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/moneyback.remove/'.$tranId).'" data-rel="notify" data-done="close | reload" data-title="ลบใบรับเงินคืน" data-confirm="ต้องการลบใบรับเงินคืน กรุณายืนยัน?"><i class="icon -material">delete</i></a>');
	} else {
		$ui->add('<a><i class="icon -material -gray">lock</i></a>');
	}

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>ใบรับเงินคืน ('.$moneybackInfo->refcode.')</h3><nav class="nav">'.$ui->build().'</nav></header>';



	$stmt='SELECT tr.`trid`, tr.`detail2` `refcode`, tr.`detail1` `no`, tr.`date1` `date`, tr.`num1` `amount`, tr.`created`, u.`name` `posterName`
				FROM %project_tr% tr
					LEFT JOIN %users% u USING(`uid`)
				WHERE tr.`formid` = "info" AND tr.`part`="moneyback" AND tr.`tpid`=:tpid AND `trid`=:trid
				LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid,':trid',$tranId);

	$ret.='<p>เลขที่ใบรับเงิน '.$moneybackInfo->no.'</p>';
	$ret.='<p>เลขที่อ้างอิง '.$moneybackInfo->refcode.'</p>';
	$ret.='<p>วันที่ '. ($moneybackInfo->rcvdate ? sg_date($moneybackInfo->rcvdate,'ว ดดด ปปปป') : '??/??/????').'</p>';
	$ret.='<p>จำนวนเงินรับคืน '.number_format($moneybackInfo->amount,2).' บาท</p>';
	$ret.='<p>บันทึกข้อมูลโดย '.$moneybackInfo->posterName.'</p>';
	$ret.='<p>เมื่อวันที่ '.sg_date($moneybackInfo->created,'ว ดดด ปปปป H:i:s').'</p>';



	$glTran = R::Model('project.gl.tran.get', $moneybackInfo->refcode);
	$tables = new Table();
	$tables->caption = 'GL Transaction';
	$tables->thead = array('ID', 'GL Code', 'รายละเอียด', 'dr -money' => 'เดบิท', 'cr -money' => 'เครดิต');
	foreach ($glTran->items as $item) {
		$tables->rows[] = array(
			$item->pglid,
			$item->glcode,
			$item->glname,
			$item->amount > 0 ? number_format($item->amount,2) : '',
			$item->amount < 0 ? number_format(abs($item->amount),2) : '',
		);	
	}
	$ret .= $tables->build();

	//$ret.=print_o($moneybackInfo,'$moneybackInfo');
	//$ret.=print_o($glTran,'$glTran');

	return $ret;
}
?>