<?php
/**
 * saveup_gl class for gl accountung
 *
 * @package saveup
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-06-29
 * @modify 2011-06-29
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
function saveup_gl($self) {
	R::View('saveup.toolbar',$self,'ระบบงานกลุ่มออมทรัพย์ '.cfg('saveup.version'));
	$ret.=R::View('saveup.menu.main');
	return $ret;
}
?>