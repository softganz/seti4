<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_help_patient_add($self) {
	$ret.='<h3>วิธีเพิ่มชื่อผู้ป่วย</h3>';
	$ret.='<ol><li>คลิกเมนู <strong>ผู้ป่วย</strong></li><li>แล้วป้อนชื่อและนามสกุลผู้ป่วยในช่อง <strong>"ป้อนชื่อผู้ป่วย"</strong> โดยไม่ต้องใส่คำนำหน้า เคาะเว้นวรรคระหว่างชื่อกับนามสกุล 1 ครั้ง</li><li>จะมีรายชื่อแสดงให้</li><li>หากมีรายชื่อ ให้คลิกเลือกชื่อ หากไม่มี ให้ป้อนชื่อ-นามสกุล ให้เรียบร้อย</li><li>แล้วคลิกปุ่ม <strong>+เพิ่มรายชื่อ</strong></li></ol>';
	return $ret;
}
?>