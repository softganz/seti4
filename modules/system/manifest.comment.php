<?php
/**
 * Comment class for comment management
 *
 * @package comment
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

cfg('comment.version','4.00.00');
cfg('comment.release','17.7.29');

menu('comment','Comment management','comment','__controller',1,'access comments','static');

cfg('comment.permission','access comments,administer comments,hide comments,post comments,post comments without approval,edit own comment');
?>