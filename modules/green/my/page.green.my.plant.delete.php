<?php
/**
* Green : Delete Plant Information
* Created 2020-11-09
* Modify  2020-11-09
*
* @param Object $self
* @param Int $plantId
* @return String
*
* @usage green/my/plant/delete/{$Id}
*/

$debug = true;

function green_my_plant_delete($self, $plantId) {

	$plantInfo = R::Model('green.plant.get', $plantId);

	if (!$plantInfo) return 'ไม่มีรายการ';

	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $plantInfo->uid == i()->uid;

	$ret .= '<p>ลบรายการ?</p>';

	$ret .= '<nav class="nav -page -sg-text-center" style="padding: 32px;"><a class="sg-action btn -danger -fill" href="'.url('green/my/info/activity.delete/'.$plantInfo->msgId).'" data-rel="none" data-done="back" data-title="ลบรายการ" data-confirm="กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบรายการ</span></a></nav>';
	return $ret;
}
?>