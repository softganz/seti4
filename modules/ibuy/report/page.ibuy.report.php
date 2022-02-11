<?php
/**
 * ibuy_report class for product management
 *
 * @package ibuy
 * @subpackage ibuy_report
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-09-20
 * @modify 2009-12-09
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
function ibuy_report($self) {
	$self->theme->title='Report Center';
	$ret.='<div id="accordion">
<h3><a href="#">รายงานทั่วไป</a></h3>
<div>
<ul>
<li><a href="'.url('ibuy/report/bestseller').'">50 อันดับสินค้าขายดี</a></li>
</ul>
</div>';

	if (in_array('franchise',i()->roles)) {
		$ret.='<h3><a href="#">รายงานสำหรับเฟรนไขน์</a></h3>
<div>
<ul>
<li><a href="'.url('ibuy/report/monthly').'">ยอดซื้อสินค้าประจำเดือน</a></li>
</ul>
</div>';
	}

	if (in_array('resaler',i()->roles)) {
		$ret.='<h3><a href="#">รายงานสำหรับตัวแทนจำหน่าย</a></h3>
<div>
<ul>
<li><a href="'.url('ibuy/report/bestseller').'">50 อันดับสินค้าขายดี</a></li>
</ul>
</div>';
	}
	
	if (user_access('administer ibuys')) {
		$ret.='<h3><a href="#">รายงานสำหรับผู้ดูแลระบบ</a></h3><div>';
		$ret.='<ul>
<li><a href="'.url('ibuy/report/order').'">รายงานใบสั่งสินค้าทั้งหมด</a></li>
<li><a href="'.url('ibuy/report/totalsale/byproduct').'">รายงานยอดขายสินค้า - แยกตามชื่อสินค้า</a></li>
<li><a href="'.url('ibuy/report/totalsale/bymonth').'">รายงานยอดขายสินค้า - แยกตามเดือน-ปี</a></li>
<li><a href="'.url('ibuy/report/sale/thismonth').'">รายงานการคำนวณส่วนลดและค่าการตลาดประจำเดือน</a></li>
<li><a href="'.url('ibuy/report/noavailable').'">รายงานสินค้างดจำหน่าย</a></li>
<li><a href="'.url('ibuy/report/discount').'">รายงานการคำนวณส่วนลด</a></li>
</ul></div>';
	}
	$ret.='</div>';
	return $ret;
}
?>