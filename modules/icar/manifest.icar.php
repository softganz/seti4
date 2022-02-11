<?php
/**
 * icar class for car cost management
 *
 * @package icar
 * @version 0.00a
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2012-11-18
 * @modify 2012-11-25
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('icar.version','0.10a');
cfg('icar.release','12.11.25');

menu('icar/admin','Car cost control admin','icar','__controller',1,'administer icars','static','home');
menu('icar','Car cost control main page','icar','__controller',1,'access icars','static','home');

require_once('class.icar.model.php');
tr('load','icar');

head('sg.icar.js','<script type="text/javascript" src="/icar/sg.icar.js"></script>');

cfg('icar.permission', 'access icars,administer icars,create icar content,edit own icar content');

?>
