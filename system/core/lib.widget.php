<?php
/**
* SOFTGANZ :: lib.ui.php
*
* Copyright (c) 2000-2020 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*         : http://www.softganz.com/
* ============================================
* This module is core of web application
*
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================

--- Created 2007-07-09
--- Modify  2020-10-01
*/

$includeModelList = [
	'model.code.php',
];

foreach ($includeModelList as $file) load_lib($file, 'system/model');

$includeUiList = [
	'class.widget.php',
	'class.ui.php',
	'class.dropbox.php',
	'class.toolbar.php',
	'class.form.php',
	'class.table.php',
	'class.pagenavigator.php',
	'class.survey.php',
	'class.report.php',
];

foreach ($includeUiList as $file) load_lib($file, 'system/ui');

?>