<?php
/**
 * template.init class for init module
 *
 * @package template
 * @version 0.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2001-01-01
 * @modify 2001-01-01
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('docs.version','0.00');
cfg('docs.release','1.1.1');

menu('docs','Docs Home','docs','__controller',1,true,'static');

class docs extends module {
var $module='docs';

	function __construct() {
		cfg('page_id','docs');
		$this->theme->option->title=true;
	}

}
?>