<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_rehab_pocenter($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$centerInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;


	if (!$centerInfo) {
		if ($isAdmin) {
			$ret .= '<div class="-sg-text-center" style="padding: 32px 0;"><b>"'.$orgInfo->name.'"</b> ยังไม่มีการลงทะเบียนศูนย์กายอุปกรณ์<br />ต้องการลงทะเบียนศูนย์กายอุปกรณ์หรือไม่?<br /><br /><br />';
			$ret .= '<nav class="nav -sg-text-center"><a class="sg-action btn -primary" href="'.url('imed/pocenter/create/'.$orgId).'" data-rel="#imed-app" data-ret="'.url('imed/rehab/'.$orgId.'/pocenter').'" data-title="ลงทะเบียนศูนย์กายอุปกรณ์" data-confirm="ต้องการลงทะเบียนศูนย์กายอุปกรณ์ กรุณายืนยัน?"><i class="icon -material -white">add</i><span>ลงทะเบียนศูนย์กายอุปกรณ์</span></a></nav>';
			$ret .= '</div>';
		} else {
			$ret .= 'ไม่มีข้อมูลศูนย์กายอุปกรณ์';
		}
		$ret .= '</div>';
		return $ret;
	}

	$ret .= R::Page('imed.pocenter.stock.list',NULL, $orgInfo);


	//$ret .= print_o($centerInfo,'$centerInfo');

	return $ret;
}
?>