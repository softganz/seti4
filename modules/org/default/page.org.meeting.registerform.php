<?php
/**
*  Invite meeting
*
* @param $_POST
* @return String
*/
function org_meeting_registerform($self,$orgId, $doid) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	if (!isset($rs)) $rs=R::Model('org.doing.get',$doid);;
	$isEdit=org_model::is_edit($rs->orgid,$rs->uid);
	if (!$isEdit) return message('error','access denied');

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','name -nowrap'=>'ชื่อ - นามสกุล','ที่อยู่/หน่วยงาน/องค์กร','โทรศัพท์','อีเมล์','signature'=>'ลายเซ็นต์');

	$tables->caption='<h2>แบบฟอร์มลงทะเบียน</h2><h3>'.$rs->doings.'</h3>'.$rs->place.' วันที่ '.sg_date($rs->atdate,'ว ดดด ปปปป').' เวลา '.substr($rs->fromtime,0,5).' น.</h3>';
	foreach ($rs->members as $item) {
		if ($item->regtype=="Walk In") continue;
		unset($row);
		$row[] = ++$no;
		$row[] = trim($item->prename.' '.$item->name.' '.$item->lname);
		$row[] = SG\implode_address($item,'short')
					. ($item->orgName ? '<br />'.$item->orgName : '');
		$row[] = $item->phone;
		$row[] = $item->email;
		$row[] = '&nbsp;';
		$tables->rows[]=$row;
	}
	$ret .= $tables->build();

	if (post('o')) {
		sendheader('application/octet-stream');
		mb_internal_encoding("UTF-8");
		header('Content-Disposition: attachment; filename="'.mb_substr($rs->doings,0,50).'-ลงทะเบียน.xls"');

		$ret='<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<HTML>
<HEAD>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta http-equiv="Content-Language" content="th" />
</HEAD>
<BODY>
'.$ret.'
</BODY>
</HTML>';
		die($ret);
	}
	$ret.='<style type="text/css">
	.item caption {background:#fff;}
	.item>thead>tr>th {white-space: nowrap; padding:4px;}
	@media print {
	table.item {border:1px #ccc solid;}
	.col-signature {widthL:1in; white-space:nowrap;}
	.col-name {white-space:nowrap;}
	.item>tbody>tr>td {padding:4px; border-right:1px #ccc solid;}
	#header-wrapper {display:none;}
	}
	</style>';
	$ret.='<p class="-no-print">หมายเหตุ : รายชื่อในแบบฟอร์มลงทะเบียน จะแสดงเฉพาะชื่อผู้ที่ถูกเชิญเข้าร่วมเท่านั้น</p>';
	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>