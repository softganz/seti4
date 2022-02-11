<?php
/**
 * set manifest
 *
 * @package set
 * @version 0.40
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2013-07-15
 * @modify 2017-07-05
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('set.version','0.40.0');
cfg('set.release','5.8.20');
cfg('web.secondary',NULL);

menu('set/admin','Set Trade admin','set','__controller',1,'administer sets','static');
menu('set','Set Trade','set','__controller',1,true,'static');

cfg('set.permission', 'administer sets,access sets,create set content,edit own set content');

head('googlead','<script></script>');

if (cfg('set.graph') == "tradingview") {
	head('<script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>');
}
?>