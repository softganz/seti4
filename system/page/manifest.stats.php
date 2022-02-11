<?php
/**
 * Stats class for web statistic report
 *
 * @package stats
 * @version 4.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-01-08
 * @modify 2021-05-21
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('stats.version','4.0.01');
cfg('stats.release','2010-05-21');

menu('stats/report','Web Statistic Report','stats','__controller',1,'access statistic report','static');
menu('stats','Web Statistic','stats','__controller',1,'access statistic','static');
?>