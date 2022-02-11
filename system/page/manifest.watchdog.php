<?php
/**
 * Comment class for comment management
 *
 * @package watchdog
 * @version 4.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2006-12-10
 * @modify 2017-07-29
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('watchdog.version','4.00.00');
cfg('watchdog.release','17.7.29');

menu('watchdog','Watchdog Log Management','watchdog','__controller',1,'access logs','static');

cfg('watchdog.permission','access statistic,access statistic report,access logs,administer watchdogs');
?>