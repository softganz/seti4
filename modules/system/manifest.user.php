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

cfg('user.version','4.00.00');
cfg('user.release','18.8.3');

menu('user/register','Member register','user','__controller',1,'register new member' ,'static');
menu('user','User Management','user','__controller',1,true,'static');

cfg('user.permission','access user profiles,administer users,change own username,change own profile,register new member');
?>