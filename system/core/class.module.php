<?php
/**
 * module class is a base class for all module
 *
 * @package none
 * @version 1.2
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-08-07
 * @modify 2010-05-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class Module {
	var $module = NULL;
	var $version = NULL;
	var $theme = NULL;

	function __construct($moduleName = NULL) {
		$this->theme = (Object)[
			'option' => cfg('topic.property')->option,
			'header' => (Object) [],
			'ribbon' => (Object) [],
		];

		if ($moduleName) $this->module = $moduleName;
		else if (empty($this->module)) $this->module = get_class($this);
		$this->version();
		if (cfg('page_id') == '') cfg('page_id',$this->module);
	}

	function package() { return $this->module; }
	function version() { return $this->version ? $this->version : $this->version = cfg($this->module.'.version'); }
} // End of class module
?>