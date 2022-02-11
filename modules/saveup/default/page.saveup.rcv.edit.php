<?php
/**
 * Edit recieve
 *
 * @param String $rcvId
 * @param Array $_POST
 * @return String
 */
function saveup_rcv_edit($self, $rcvId) {
	$rcvInfo = is_object($rcvId) ? $rcvId : R::Model('saveup.rcv.get',$rcvId);
	$rcvId = $rcvInfo->rcvid;

	R::View('saveup.toolbar',$self,'ใบรับเงิน','rcv',$rcvInfo);


	if ( $rcvInfo->_empty ) return $ret.message('error','ไม่มีใบรับเงินตามที่ระบุ');

	$tables = new Table();
	$tables->id='rcvmast';
	$tables->rows[]=array('วันที่',sg_date($rcvInfo->rcvdate,'ว ดดด ปปปป'),'เลขที่',$rcvInfo->rcvno,'รวมเงิน',number_format($rcvInfo->total,2).' บาท');
	if ($rs->memo) $tables->rows[]=array('บันทึก',$rcvInfo->memo);
	$ret .= $tables->build();

	$ret.=message('error','กำลังอยู่ในระหว่างการตัดสินใจว่าจะให้สามารถแก้ไขได้หรือไม่?');

	$tables = new Table();
	$tables->id='rcvtr';
	$tables->thead=array('no'=>'ลำดับ','รหัสสมาชิก','ชื่อสมาชิก','รหัสรายการ','รายละเอียด','money amt'=>'จำนวนเงิน');
	foreach ($rcvInfo->trans as $rs) {
		$tables->rows[]=array($rs->aid,$rs->mid,$rs->name,$rs->glcode,$rs->desc,number_format($rs->amt,2));
		$total+=$rs->amt;
	}
	$tables->rows[]=array('','','','','รวมทั้งสิ้น','<strong>'.number_format($total,2).'</strong>');
	$ret .= $tables->build();

	return $ret;
}
?>