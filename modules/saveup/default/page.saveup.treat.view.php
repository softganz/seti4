<?php
/**
* View Saveup Treat
* Created 2019-09-01
* Modify  2019-09-12
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function saveup_treat_view($self,$tid) {
	$treatInfo = saveup_model::get_treat_by_id($tid);

	if ($treatInfo->_empty) return message('error',$this->theme->title='รายการเบิกค่ารักษาพยาบาล #'.$tid.' ไม่มี.');

	R::View('saveup.toolbar',$self,'รายละเอียดค่ารักษาพยาบาล','treat',$treatInfo);

	$payTypeList = saveup_var::$payType;

	$tables = new Table();
	$tables->addClass('item-info');
	$tables->rows[]=array('เลขที่เอกสาร',$treatInfo->ref);
	$tables->rows[]=array('วันที่อนุมัติ',sg_date($treatInfo->date,'ว ดด ปป'));
	$tables->rows[]=array('ชื่อ - สกุล',$treatInfo->name.' ['.$treatInfo->mid.']');
	$tables->rows[]=array('จำนวนเงิน',number_format($treatInfo->amount,2));
	$tables->rows[]=array('ประเภทค่ารักษาพยาบาล',$payTypeList[$treatInfo->paytype]);
	$tables->rows[]=array('เพื่อเป็นค่า',$treatInfo->payfor);
	$tables->rows[]=array('รักษาโรค',$treatInfo->disease);
	$tables->rows[]=array('สถานพยาบาล',$treatInfo->clinic);
	$tables->rows[]=array('อำเภอ',$treatInfo->amphure);
	$tables->rows[]=array('จังหวัด',$treatInfo->province);
	$tables->rows[]=array('จำนวนใบเสร็จ',$treatInfo->bills);
	$tables->rows[]=array('เมื่อวันที่',$treatInfo->billdate?sg_date($treatInfo->billdate,'ว ดด ปป'):'');
	$tables->rows[]=array('หมายเหตุ',$treatInfo->remark);

	$ret .= $tables->build();

	$ret .= R::Page('saveup.treat.year', NULL, $treatInfo->mid);
	return $ret;
}
?>