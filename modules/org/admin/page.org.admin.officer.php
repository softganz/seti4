<?php
/**
 * org admin page
 *
 * @package org
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2015-02-14
 * @modify 2015-02-14
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
function org_admin_officer($self) {
	$self->theme->title='รายชื่อเจ้าหน้าที่องค์กร';
	$isAdmin=user_access('access administrator pages');
	if (!$isAdmin) return message('error','access denied');

	$stmt='SELECT of.*, u.`uid`, u.`username`, u.`name` name, o.`name` orgName
					FROM %org_officer% of
						LEFT JOIN %users% u USING ( uid )
						LEFT JOIN %db_org% o USING(`orgid`)
					ORDER BY CONVERT(u.`name` USING tis620) ASC
					';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('ชื่อสมาชิก','องค์กร','ประเภท');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->name.($isAdmin?' ('.$rs->username.')':''),
											'<a href="'.url('org/'.$rs->orgid).'">'.$rs->orgName.'</a>',
											$rs->membership
											);
	}
	$ret.=$tables->build();
	return $ret;
}
?>