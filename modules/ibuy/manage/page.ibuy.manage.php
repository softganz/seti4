<?php
/**
* ibuy_manage class for product management
*
* @package ibuy
* @subpackage ibuy_manage
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-09-09
* @modify 2009-12-09
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

function ibuy_manage($self) {
	$self->theme->title='iBuy Management';

	$ret.='<h3>สินค้า</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('ibuy/product/post').'">เพิ่มสินค้าใหม่</a>');
	$ui->add('<a href="'.url('admin/content/taxonomy/list/2').'">จัดการหมวดสินค้า</a>');
	$ui->add('<a href="'.url('admin/content/taxonomy/list/3').'">จัดการยี่ห้อสินค้า</a>');
	$ret.=$ui->build('ul');

	$ret.='<h3>รายงาน</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('ibuy/report/bestseller').'">50 อันดับสินค้าขายดี</a>');
	$ui->add('<a href="'.url('ibuy/report/order').'">รายงานการสั่งซื้อสินค้า</a>');
	$ui->add('<a href="'.url('ibuy/report/totalsale/byproduct').'">รายงานยอดขายสินค้า - แยกตามชื่อสินค้า</a>');
	$ui->add('<a href="'.url('ibuy/report/totalsale/bymonth').'">รายงานยอดขายสินค้า - แยกตามเดือน-ปี</a>');
	$ui->add('<a href="'.url('ibuy/report/sale/thismonth').'">รายงานการคำนวณส่วนลดและค่าการตลาดประจำเดือน</a>');
	$ui->add('<a href="'.url('ibuy/report/noavailable').'">รายงานสินค้างดจำหน่าย</a>');
	$ui->add('<a href="'.url('ibuy/report/discount').'">รายงานการคำนวณส่วนลด</a>');
	$ret.=$ui->build('ul');

	$ret.='<h3>อื่น ๆ</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('ibuy/customer').'">รายชื่อลูกค้าทั่วไป</a>');
	$ui->add('<a href="'.url('ibuy/resaler').'">รายชื่อตัวแทนจำหน่าย</a>');
	$ui->add('<a href="'.url('ibuy/franchise').'">รายชื่อเฟรนไขส์</a>');
	$ui->add('<a href="'.url('admin/user/list').'">รายชื่อสมาชิก</a>');
	$ui->add('<a href="'.url('ibuy/manage/generate/code').'">Generate Franchise Register Code</a>');
	$ui->add('<a href="'.url('ibuy/manage/monthly/process').'">ประมวลผลประจำเดือน</a>');
	$ui->add('<a href="'.url('ibuy/manage/config').'">Setting</a>');
	$ui->add('<a href="'.url('admin').'">จัดการเว็บไซท์</a>');

	$ret.=$ui->build('ul');
	return $ret;
}
?>