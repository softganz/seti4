<?php
/**
 * ad class for advertising management
 *
 * @package ad
 * @version 1.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-06-06
 * @modify 2012-04-28
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('ad.version','4.00');
cfg('ad.release','18.7.24');

/*
menu('ad/*','Advertisment','ad','view',1,true,'dynamic');
menu('ad/ * /click','Advertisment click','ad','click',1,true,'dynamic');
menu('ad/get','Get ad','ad','get_ad',1,true,'static');
menu('ad/list','List all ad','ad','listing',2,true,'static');
menu('ad','Advertisment','ad','listing',1,true,'dynamic');

menu('ad/post','Create advertisment','ad','post',2,'create ad content','dynamic');
menu('ad/report','Advertisment Report','ad','__controller',1,'create ad content','dynamic');
*/

menu('ad/*/click','Advertisment Click','ad','__controller',1,true,'dynamic');
menu('ad/report','Advertisment Report','ad','__controller',1,'create ad content','dynamic');
menu('ad','Advertisment','ad','__controller',1,'access ads','dynamic');

cfg('ad.permission','access ads,administer ads,create ad content,edit own ad');

define('_AD_FORMAT_FILE','image/gif,image/jpeg,image/png,image/pjpeg,application/x-shockwave-flash');

include_once('class.ad.model.php');

?>