<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_app_pocenter($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$centerInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');

	R::View('imed.toolbar',$self,'@ศูนย์กายอุปกรณ์','none');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	//$ret .= '<div class="imed-sidebar"><h3>'.$orgInfo->name.'</h3>'.R::View('imed.menu.group',$orgInfo)->build().'</div>';


	$ret .= '<div id="imed-app" class="imed-app">'._NL;


	if (!$centerInfo) {
		if ($isAdmin) {
			$ret .= '<div class="-sg-text-center" style="padding: 32px 0;"><b>"'.$orgInfo->name.'"</b> ยังไม่มีการลงทะเบียนศูนย์กายอุปกรณ์<br />ต้องการลงทะเบียนศูนย์กายอุปกรณ์หรือไม่?<br /><br /><br />';
			$ret .= '<nav class="nav -sg-text-center"><a class="sg-action btn -primary" href="'.url('imed/pocenter/create/'.$orgId).'" data-rel="#main" data-ret="'.url('imed/pocenter/'.$orgId).'" data-title="ลงทะเบียนศูนย์กายอุปกรณ์" data-confirm="ต้องการลงทะเบียนศูนย์กายอุปกรณ์ กรุณายืนยัน?"><i class="icon -material -white">add</i><span>ลงทะเบียนศูนย์กายอุปกรณ์</span></a></nav>';
			$ret .= '</div>';
		} else {
			$ret .= 'ไม่มีข้อมูลศูนย์กายอุปกรณ์';
		}
		$ret .= '</div>';
		return $ret;
	}


	$ret .= R::Page('imed.pocenter.stock.list',NULL, $orgInfo);

	//$ret .= print_o($centerInfo,'$centerInfo');
	$ret .= '</div><!-- imed-app -->';

	return $ret;
}
?>