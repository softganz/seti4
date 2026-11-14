<?php
/**
 * module class is a base class for all module
 *
 * @package none
 * @version 1.2
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created :: 2007-08-07
 * @modify  :: 2025-10-01
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class Module {
	public $module = null;
	public $version = null;
	public $appBar = null;
	public $appBarText = null;
	public $sideBar = null;
	public $theme = null;

	function __construct($moduleName = null) {
		$this->theme = (object) [
			'option' => cfg('topic.property')->option,
			'header' => (object) [],
			'ribbon' => (object) []
		];

		if ($moduleName) {
			$this->module = $moduleName;
		} elseif (empty($this->module)) {
			$this->module = get_class($this);
		}
		$this->version();
		if (cfg('page_id') == '') {
			cfg('page_id', $this->module);
		}
	}

	function package() {
		return $this->module;
	}
	function version() {
		return $this->version
			? $this->version
			: ($this->version = cfg($this->module . '.version'));
	}
} // End of class module
?>
