<?php
/** Member check
 *
 * @param Array $_REQUEST
 * @return String
 */
function saveup_member_check($self) {
	$self->theme->title='ตรวจสอบความถูกต้องของชื่อและที่อยู่สมาชิก';
	$ret .= '<div id="saveup-member-check">
<form method="get" action="'.url('saveup/member/check').'">
หมายเลขสมาชิก : <input type="text" name="smid" value="'.htmlspecialchars($_REQUEST['smid']).'" autocomplete="off" /> ชื่อ - นามสกุล : <input type="text" name="sn" value="'.htmlspecialchars($_REQUEST['sn']).'" autocomplete="off" /> <button class="btn -primary"><i class="icon -search -white"></i><span>ค้นหา</span></button>
</form>
<p>ให้ป้อนหมายเลขสมาชิก และ ชื่อ - สกุล ที่ต้องการตรวจสอบ</p>
</div>';
	if (empty($_REQUEST['smid']) && empty($_REQUEST['sn'])) return $ret;

	preg_match('#(.*)[\s](.*)#',$_REQUEST['sn'],$out);
	$firstname=trim(addslashes($out[1]));
	$lastname=trim(addslashes($out[2]));

	$stmt ='SELECT fu.* FROM %saveup_member% AS fu
						WHERE fu.mid=:mid AND fu.firstname=:firstname AND fu.lastname=:lastname
						ORDER BY fu.mid ASC LIMIT 1';
	$member=mydb::select($stmt,':mid',$_REQUEST['smid'],':firstname',$firstname,':lastname',$lastname);

	if (!$member->_empty) {
		$tables = new Table();
		$tables->caption='รายละเอียดสมาชิก';
		$tables->thead=array('id'=>'ข้อมูล','value'=>'รายละเอียด');
		$tables->rows[]=array('หมายเลขสมาชิก',$member->mid);
		$tables->rows[]=array('ชื่อ - สกุล',$member->prename.$member->firstname.' '.$member->lastname.($member->nickname?' ('.$member->nickname.')':''));
		$tables->rows[]=array('ที่อยู่ปัจจุบัน',$member->caddress.' อ.'.$member->camphure.' จ. '.$member->cprovince.' '.$member->czip);
		$tables->rows[]=array('โทรศัพท์',$member->phone);
		$tables->rows[]=array('ที่อยู่ (ทะเบียนบ้าน)',$member->address.' อ.'.$member->amphure.' จ. '.$member->province.' '.$member->zip);
		$tables->rows[]=array('เลขประจำตัวประชาชน',$member->idno);
		$tables->rows[]=array('วันเกิด',empty($member->birth)?'ไม่ระบุ':sg_date($member->birth,'ว ดดด ปปปป'));
		$tables->rows[]=array('ผู้รับผลประโยชน์',$member->beneficiary_name.'<br />'.$member->beneficiary_addr);
		$tables->rows[]=array('บุคคลที่ติดต่อได้',$member->contact_name);
		$tables->rows[]=array('วันที่เริ่มเป็นสมาชิก',empty($member->date_approve)?'ไม่ระบุ':sg_date($member->date_approve,'ว ดดด ปปปป'));
		$tables->rows[]=array('สถานภาพ',$member->status);

		$ret .= $tables->build();

		$ret.='<p>หากรายละเอียดสมาชิกไม่ถูกต้องหรือไม่ครบถ้วน กรุณาติดต่อกลุ่มออมทรัพย์นักพัฒนาภาคใต้ หรือ ส่งรายละเอียดไปที่อีเมล์ webmaster (ที่) softganz.com</p>';
	} else {
		$ret.=message('error','ไม่มีสมาชิกตามเงื่อนไขที่ระบุ : ท่านอาจจะป้อนหมายเลขสมาชิกไม่ตรงกับชื่อ-สกุล หรือ ชื่อ-สกุล ที่บันทึกไว้อาจะผิดพลาด');
	}
	return $ret;
}
?>