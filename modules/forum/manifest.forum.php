<?php
/**
 * Forum
 *
 * @package forum
 * @version 4.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2020-04-01
 * @modify 2020-04-03
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('forum.version','4.00.0');
cfg('forum.release','20.4.1');

menu('forum','Forum Homepage','forum','__controller',1,'access forums','static');

cfg('forum.permission', 'access forums,create forum content,administer forums,edit own forum content');
?>