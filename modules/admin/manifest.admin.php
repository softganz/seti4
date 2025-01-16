<?php
/**
 * admin class for site administration
 *
 * @package admin
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-04-22
 * @modify 2025-01-16
 * Version 2
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
*/

cfg('admin.version','4.1.01');
cfg('admin.release','2025-01-16');

menu('admin/install','Admin Install Site Database','admin','__controller',1,true,'static');
menu('admin/user','Admin Install Site Database','admin','__controller',1,'administer users','static');
menu('admin','Admin menu','admin','__controller',1,'access administrator pages','static');

head('googlead','<script></script>');
?>