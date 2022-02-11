<?php
/**
* Reorder Project Proposal Plan Item
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Object $proposalInfo
* @param Int $tranId
* @return String
*/

$debug = true;

function project_proposal_info_plan_reorder($self, $proposalInfo, $tranId) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $proposalInfo->RIGHT & _IS_EDITABLE;

	$ret = '';

	$activity = $proposalInfo->activity[$tranId];

	if ($isEditable && SG\confirm()) {
		if ($tranId && $after = post('after')) {
			if ($after == 'top') {
				$to = post('min');
			} else {
				$to = $after + 1;
			}

			$stmt = 'UPDATE %project_tr% SET `sorder` = `sorder`+1
				WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity" AND `sorder` >= :to
				ORDER BY `sorder` ASC';
			mydb::query($stmt, ':tpid', $tpid, ':tagname', _PROPOSAL_TAGNAME, ':to', $to);
			//$ret .= mydb()->_query.'<br />';

			// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
			mydb::query('UPDATE %project_tr% SET `sorder` = :to WHERE `trid` = :trid LIMIT 1', ':trid', $tranId, ':to', $to);
			//$ret .= mydb()->_query.'<br />';

			// เรียงลำดับกิจกรรมใหม่
			mydb::query('SET @n := 0 ;');
			mydb::query('UPDATE %project_tr% SET `sorder` = @n := @n+1 WHERE `tpid`= :tpid AND `formid` = :tagname AND `part` = "activity" ORDER BY `sorder` ASC;', ':tpid', $tpid, ':tagname', _PROPOSAL_TAGNAME);
			//$ret .= mydb()->_query.'<br />';

			//$ret .= print_o(post(),'post()');
			$ret .= 'เรียงลำดับกิจกรรมใหม่เรียบร้อย';
		} else {
			$ret .= 'เรียงลำดับกิจกรรมผิดพลาด';
		}

		return $ret;
	}


	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>เปลี่ยนลำดับกิจกรรม</h3></header>';
	$ret .= '<h5>กิจกรรม : '.$activity->title.'</h5>';

	$form = new Form(NULL, url('project/proposal/'.$tpid.'/info.plan.reorder/'.$tranId), 'project-edit-movemainact', 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done','close | load->replace:#project-proposal-plan');

	$form->addField('confirm',array('type' => 'hidden', 'value' => 'yes'));

	$options = array();
	$options['top'] = 'บนสุด';
	$min = 999999999;
	foreach ($proposalInfo->activity as $item) {
		//$ret.=$tranId.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
		if ($tranId == $item->trid || $item->parent != $activity->parent) continue;
		$options[$item->sorder] = 'หลัง : '.$item->title;
		$min = $item->sorder < $min ? $item->sorder : $min;
	}

	$form->addField(
		'min',
		array(
			'type' => 'hidden',
			'value' => $min
		)
	);

	$form->addField(
		'after',
		array(
			'type' => 'radio',
			'label' => 'เลือกลำดับของกิจกรรมที่ต้องการย้ายกิจกรรมนี้ไป',
			'options' => $options
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext'=>'<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();
	//$ret.=print_o($proposalInfo->activity,'$proposalInfo->activity');

	return $ret;
}
?>