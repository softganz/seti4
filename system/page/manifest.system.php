<?php
/**
 * System class for system management
 *
 * @package user
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-12-17
 * @modify 2017-07-29
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('system.version','4.00.00');
cfg('system.release','17.7.29');

menu('system','System','system','__controller',1,true,'static');

cfg('system.permission','access debugging program,access administrator pages,administer access control,administer contents,upload document,upload photo,upload video');
?>