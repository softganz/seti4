<?php
/**
 * User class for web member user
 *
 * @package user
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2006-12-16
 * @modify 2018-09-03
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('my.version','4.00.00');
cfg('my.release','18.9.19');

menu('my/account/delete','My Account Delete Request','my.account.delete','__controller',1,true,'static');
menu('my','My Account Management','my','__controller',1,i()->ok,'static');
// menu('project/get','Get project from query','project.get','__controller',2,true,'static');
// MyAccountDelete
?>