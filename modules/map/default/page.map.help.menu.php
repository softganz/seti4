<?php
/**
 * map help class for map networks mapping with crowd sourcing help
 *
 * @package map
 * @version 0.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2013-09-05
 * @modify 2013-09-05
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

function map_help_menu() {
	$ret='<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="box-close" title="ปิดหน้าต่าง"><i class="icon -close"></i></a></nav>';
	$ret.='<h2>ระบบช่วยเหลือ</h2>';
	$ret.='<div id="help-items">
<h3>การสร้างตำแหน่งใหม่บนแผนที่</h3>
<p><ul><li><strong>คลิก 2 ครั้ง (ดับเบิ้ลคลิก)</strong> บนแผนที่ตรงตำแหน่งที่ต้องการสร้างตำแหน่งใหม่</li><li>ป้อนข้อมูลในแต่ละช่อง แล้วคลิกปุ่มบันทึก</li></ul></p>
<h3>การแก้ไขข้อมูล</h3>
<p><ul><li><strong>คลิกบนหมุดที่ต้องการแก้ไข</strong> แล้วคลิกบนปุ่ม <strong>"แก้ไข"</strong> ในหน้าต่างที่แสดงรายละเอียด</li></ul></p>
<h3>การย่อ-ขยายแผนที่</h3>
<p>หมุน <strong>ปุ่มหมุนของเมาส์</strong> หรือ คลิก เครื่องหมาย <strong>+/-</strong> ที่มุมบนซ้ายของแผนที่</p>
</div>
<h3>การเลื่อนแผนที่</h3>
<p>คลิกเม้าส์บนแผนที่แล้วลาก หรือ คลิกลูกศรที่มุมบนซ้ายของแผนที่</p>
<h3>เกี่ยวกับ</h3>
<p><ul><li><a href="https://www.facebook.com/CrowdsourcingDisasterNetworksMapping" target="_blank">แสดงความคิดเห็น , ข้อเสนอแนะ</a></li><li><a href="https://www.facebook.com/CrowdsourcingDisasterNetworksMapping" target="_blank">ข้อมูลเพิ่มเติมเกี่ยวกับการทำแผนที่ด้วยกระบวนการถ่ายโอนงานให้มวลชน (Crowdsourcing)</a></li></ul></p>
</div>';

	return $ret;
}
?>