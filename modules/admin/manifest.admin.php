<?php
/**
 * admin class for site administration
 *
 * @package admin
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-04-22
 * @modify 2011-11-02
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
*/
cfg('admin.version','4.1.00');
cfg('admin.release','2021-09-17');

menu('admin/install','Admin Install Site Database','admin','__controller',1,true,'static');
menu('admin','Admin menu','admin','__controller',1,'access administrator pages','static');

?>