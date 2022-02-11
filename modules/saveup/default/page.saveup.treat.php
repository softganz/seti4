<?php
/**
* saveup_treat class for treat accountung
*
* @package saveup
* @version 0.01a2
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-07-15
* @modify 2009-07-15
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

function saveup_treat($self) {
	R::View('saveup.toolbar',$self,'ค่ารักษาพยาบาล','treat');
	$ret.='<ul>
<li><a href="'.url('saveup/treat/list').'">รายการค่ารักษาพยาบาล</a></li>
<li><a href="'.url('saveup/treat/post').'">บันทึกรายการเบิกค่ารักษาพยาบาล</a></li>
</ul>';
	return $ret;
}
?>