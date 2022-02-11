<?php
/**
 * org class for installation module
 *
 * @package org
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

class school_install {

	function permission() {
		return 'administer schools,access schools,create school content,edit own school content';
	}

	function install() {
		$ret='<h3>School Kids Installation</h3>';

		$stmt='CREATE TABLE IF NOT EXISTS %school% (
						`scoid` int(10) unsigned NOT NULL,
						`name` varchar(100) DEFAULT NULL,
						`village` char(2) DEFAULT NULL,
						`tambon` char(2) DEFAULT NULL,
						`ampur` char(2) DEFAULT NULL,
						`changwat` char(2) DEFAULT NULL,
						`location` point DEFAULT NULL,
						PRIMARY KEY (`scoid`),
						KEY `name` (`name`),
						);';
		mydb::query($stmt);
		$queryResult[]=mydb()->_query;



		$ret.='<p><strong>Installation completed.</strong></p>';
		$ret.='<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

		return $ret;
	}

	function _upgrade() {

	}
}
?>