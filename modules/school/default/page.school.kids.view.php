<?php
function school_kids_view($self,$orgid,$kidid) {
	if ($orgid && is_numeric($orgid)) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);
	$ret.='<h2>ข้อมูลนักเรียน : สมชาย สุขจริง</h2>';

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$tables = new Table();
	$tables->rows[]=array('ชื่อ นามสกุล','สมชาย สุขจริง');
	$tables->rows[]=array('เลขประจำตัวนักเรียน','590190');
	$tables->rows[]=array('เลข 13 หลัก','000000000000');
	$tables->rows[]=array('ชั้นเรียน','ประถมศึกษา 2');
	$ret.=$tables->build();

	$ret.='<h3>ข้อมูลน้ำหนัก/ส่วนสูง</h3>';
	$tables = new Table();
	$no=0;
	$tables->caption='รายการบันทึกน้ำหนัก/ส่วนสูง';
	$tables->thead=array('no'=>'','date'=>'วันที่เก็บข้อมูล','amt -weight'=>'น้ำหนัก','amt -height'=>'ส่วนสูง');
	$tables->rows[]=array(++$no,date('05/m/Y'),45,140);
	$tables->rows[]=array(++$no,date('08/m/Y'),45,140);
	$tables->rows[]=array(++$no,date('09/m/Y'),45,140);
	$tables->rows[]=array(++$no,date('10/m/Y'),45,140);
	$tables->rows[]=array(++$no,date('15/m/Y'),45,140);
	$ret.=$tables->build();

	$ret.='<h3>ข้อมูลแบบสอบถามการกินอาหารและการออกกำลังกาย</h3>';
	$tables = new Table();
	$no=0;
	$tables->caption='รายการแบบสอบถามการกินอาหารและการออกกำลังกาย';
	$tables->thead=array('no'=>'','date'=>'วันที่เก็บข้อมูล','amt -weight'=>'คะแนนรวม');
	$tables->rows[]=array(++$no,date('05/m/Y'),14);
	$tables->rows[]=array(++$no,date('08/m/Y'),12);
	$tables->rows[]=array(++$no,date('09/m/Y'),16);
	$tables->rows[]=array(++$no,date('10/m/Y'),10);
	$tables->rows[]=array(++$no,date('12/m/Y'),14);
	$tables->rows[]=array(++$no,date('16/m/Y'),13);
	$ret.=$tables->build();

	return $ret;
}
?>