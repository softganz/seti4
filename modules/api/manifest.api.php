<?php
/**
 * Public Monitor class
 *
 * @package publicmon
 * @version 0.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2018-08-10
 * @modify 2018-08-10
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('api.version','0.00.0');
cfg('api.release','29.9.18');

//menu('api/admin','Public Monitor','publicmon','__controller',1,'administer publicmons','static');
menu('api','Public API','api','__controller',1,true,'static');
?>