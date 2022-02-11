<?php
/**
 * Visit patient home
 *
 * @return String
 */
function imed_patient_load($self) {
	$result = array();

	$psnId = $result['id'] = post('id');

	$imedbox = '';
	//$imedbox .= print_o(post(),'post()');
	$imedbox .= '<textarea id="myVisitBox" class="writeBox" data-service="Home Visit" placeholder="@31/12/2560 ข้อความบันทึกการไปเยี่ยมบ้าน"></textarea>';
	$imedbox .= '<div class="toolbar">';
	$imedbox .= R::View('imed.patientmenu');
	$imedbox .= '<ul class="post">';
	$imedbox .= '<li><button class="btn -primary" style="border-radius: 4px;"><i class="icon -save -white"></i><br /><span>โพสท์</span></button></li>';
	$imedbox .= '</ul>';
	$imedbox .= '</div>';

	$result['toolbox'] = $imedbox;

	$psnInfo = R::Model('imed.patient.get',$psnId);

	$result['realname'] = $psnInfo->info->realname;
	$result['photo'] = imed_model::patient_photo($psnInfo->psnId);

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;

	if ($isAccess) {
		$result['psnInfo'] = $psnInfo;
		$result['info'] = R::Page('imed.patient.individual',NULL,$psnInfo);
	} else {
		$result['info'] = R::Page('imed.patient.history',NULL,$psnInfo);
	}
	return $result;
}
?>