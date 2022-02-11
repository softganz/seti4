<?php
/**
 * projectcfg class for project management
 *
 * @package project
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2014-07-04
 * @modify 2014-07-04
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class projectcfg {

	static function enable($section) {
		static $enables;
		if (!$enables) {
			foreach (explode(';', cfg('project.enable')) as $item) {
				$item=trim($item);
				list($key,$value)=explode('=', $item);
				if ($key) $enables[$key]=$value;
			}
		}
		return $enables[$section];
	}
}
?>