<?php
/**
 * project class for project management
 *
 * @package project
 * @version 2.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2017-07-17
 * @modify 2017-07-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('school.version','0.00.1');
cfg('school.release','17.7.16');

menu('school/my','My School Kids','school','__controller',1,'create school content','static');
menu('school','School Kids','school','__controller',1,'access schools','static');
?>