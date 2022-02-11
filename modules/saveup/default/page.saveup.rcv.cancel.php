<?php
/**
 * Cancel recieve - cancel recieve and item from remove member card
 *
 * @param String $rcvId
 * @param Array $_POST
 * @param String
 */
function saveup_rcv_cancel($self, $rcvId) {
	$rcvInfo = is_object($rcvId) ? $rcvId : R::Model('saveup.rcv.get',$rcvId);
	$rcvId = $rcvInfo->rcvid;

	R::View('saveup.toolbar',$self,'ยกเลิกใบรับเงิน','rcv',$rcvInfo);

	$isEdit = user_access('administer saveups','edit own saveup content',$rcvInfo->uid);

	if ( !$rcvInfo ) return message('error','ไม่มีใบรับเงินตามที่ระบุ');

	if (!$isEdit) return  message('error','Access denied','saveup');

	if ($rcvInfo->trans) return message('error', 'ใบรับเงินมีรายการรับเงิน ไม่สามารถยกเลิกหรือลบทิ้งได้');

	$form = new Form([
		'action' => url('saveup/rcv/'.$rcvId.'/delete'),
		'id' => 'saveup-rcv-delete',
		'class' => 'sg-form',
		'checkValid' => true,
		'confirm' => [
			'type' => 'radio',
			'label' => 'คุณต้องการยกเลิกใบรับเงินเลขที่ "<strong>'.$rcvInfo->rcvno.' @'.sg_date($rcvInfo->rcvdate,cfg('date.format')).'</strong>" ใช่หรือไม่?',
			'require' => true,
			'options' => array('no' => 'ไม่ ฉันไม่ต้องการยกเลิก', 'yes' => 'ใช่ ฉันต้องการยกเลิก'),
		],
		'memo' => [
			'type' => 'textarea',
			'label' => 'หมายเหตุ',
			'class' => '-fill',
			'rows' => 10,
			'value' => $rcvInfo->memo,
			'placeholder' => 'ระบุเหตุที่ต้องยกเลิก',
		],
		'submit' => [
			'type' => 'button',
			'class' => '-danger',
			'value' => '<i class="icon -material -white">delete</i><span>ดำเนินการยกเลิกใบรับเงิน</span>',
			'container' => '{class: "-sg-text-right"}',
		],
	]);

	$ret .= $form->build();

	return $ret;
}
?>