<?php
/**
* ibuy_service class for product management
*
* @package ibuy
* @subpackage ibuy_service
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-06-22
* @modify 2009-08-26
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/
function ibuy_service($self) {
	$self->theme->title='Service Center';
	if (user_access('administer ibuys')) {
		$ret.='<h3>รายงาน</h3>';
		$ret.='<ul>
<li><a href="'.url('ibuy/report/order').'">รายงานใบสั่งสินค้าทั้งหมด</a></li>
<li><a href="'.url('ibuy/report/totalsale/byproduct').'">รายงานยอดขายสินค้า - แยกตามชื่อสินค้า</a></li>
<li><a href="'.url('ibuy/report/totalsale/bymonth').'">รายงานยอดขายสินค้า - แยกตามเดือน-ปี</a></li>
<li><a href="'.url('ibuy/report/noavailable').'">รายงานสินค้างดจำหน่าย</a></li>
</ul>';
	} else {
		$ret.='ขออภัย เมนูนี้อยู่ในระหว่างการพัฒนาระบบ';
	}
	return $ret;
}
?>