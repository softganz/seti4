<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_help_input_address($self) {
	return '<h3>วิธีการป้อนที่อยู่</h3><ul><li>ป้อนบ้านเลขที่ หมู่บ้าน ซอย ถนน หมู่ที่ โดยเคาะช่องว่าง 1 ครั้งระหว่างแต่รายการ</li><li>ป้อนตำบลโดยการพิมพ์ <strong>ตำบล</strong> หรือ <strong>ต.</strong> ชื่อตำบล แล้วเลือกจากรายการที่แสดงด้านล่างกล่องป้อนข้อความทุกครั้ง</li><li>กรณีที่มีชื่อตำบลอยู่แล้ว ให้ลบคำที่อยู่หลังตำบลออกให้หมด แล้วจึงป้อนชื่อตำบลและเลือกจากรายการที่แสดงด้านล่างกล่องป้อนความข้อความ</li></ul>';
}
?>