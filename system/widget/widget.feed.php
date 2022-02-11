<?php
/**
 * Widget widget_feed
 *
 * @package core
 * @version 0.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-11-04
 * @modify 2011-11-04
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 *
 * Widget feed
 * 
 * @param String $para
 * 	header=Header
 * 	limit=Limit (default all)
 * 	order=Order Field
 * 	sort=ASC|DESC
 * @return String
 */
function widget_feed() {
	$para=$para=para(func_get_args(),'header=Feeds','data-items=10');
	$ret='<a href="'.$para->url.'">Loding...</a>';
	return array($ret,$para);
}
?>