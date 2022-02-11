<?php
/**
* Vew personal health information
* All member can post home visit but if no right, only view own message
*
* @param Integer $psnId
* @return String
*/

import('model:imed.visit');

function imed_patient_history($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

	$uid = i()->uid;

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;


	$visitInfo = ImedVisitModel::items(['psnId' => $psnId],['items' => 100]);

	$headerUi = new Ui();
	$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/need').'" data-rel="#imed-app"><i class="icon -material -circle -green">how_to_reg</i><span></i><span class="-hidden">ความต้องการ</span></a>');
	$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/vitalsign').'" data-rel="#imed-app"><i class="icon -local -vitalsign -white '.($hasVitalsign?'-has':'-not').'"></i><span></i><span class="-hidden">สัญญาณชีพ</span></a>');
	$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/barthel').'" data-rel="#imed-app"><i class="icon -local -barthel-no"></i><span></i><span class="-hidden">ดัชนีบาร์เธล</span></a>');
	$ret .= '<header class="header"><h3>ประวัติการเยี่ยมบ้าน</h3><nav class="nav">'.$headerUi->build().'</nav></header>'._NL;


	$ret .= R::PageWidget('imed.visits', [['psnId' => $psnId]])->build();

	// $ret .= '<div id="imed-my-note" class="sg-load" data-url="'.url('imed/visits', ['pid' => $psnId, 'ref' => 'web']).'" data-replace="true">'._NL
	// 	. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
	// 	. '</div><!-- imed-my-note -->';


	// $ui = new Ui('div', 'ui-card imed-my-note sg-inline-edit');
	// $ui->addData('update-url', url('imed/edit/patient'));
	// $ui->addId('imed-my-note');
	// if (debug('inline')) $ui->addData('debug', 'inline');

	// foreach ($visitInfo->items as $rs) {
	// 	if ($isAccess || $rs->uid==$uid) {
	// 		$ui->add(R::View('imed.visit.render',$rs), '{class: "", id: "noteUnit-'.$rs->seq.'"}');
	// 	}
	// }
	// $ret .= $ui->build().'<!-- imed-my-note -->';

	/*
	$ret .= '<div id="imed-my-note" '.sg_implode_attr($inlineAttr).'>';

	$no = 0;
	foreach ($visitInfo->items as $rs) {
		if ($isAccess) {
			$ret .= R::View('imed.visit.render',$rs);
		} else if ($rs->uid==$uid) {
			$ret .= R::View('imed.visit.render',$rs);
		}
	}
	$ret .= '</div><!-- imed-my-note -->'._NL;
	*/

	return $ret;
}
?>