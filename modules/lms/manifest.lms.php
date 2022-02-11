<?php
/**
 * LMS :: Learning Management System
 *
 * @package lms
 * @version 0.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2020-07-01
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('lms.version','0.00.0');
cfg('lms.release','20.7.1');

menu('lms','LMS Homepage','lms','__controller',1,'access lms','static');

cfg('lms.permission', 'administer lms,access lms,create lms content,edit lms student');

head('js.lms.js','<script type="text/javascript" src="/lms/js.lms.js"></script>');
?>