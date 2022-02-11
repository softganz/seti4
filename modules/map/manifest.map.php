<?php
/**
 * map class for GIS on web
 *
 * @package map
 * @version 0.40
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-08-21
 * @modify 2016-07-15
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('map.version','0.10');
cfg('map.release','9.11.13');

menu('map','Map','map','__controller',1,'access maps','static');

include_once('class.map.model.php');

cfg('map.permission', 'administer maps,create maps,edit own maps content,access maps,access full maps');


class map_base extends module {

}

class map extends map_base {
	public function __construct() {
		parent::__construct();
		page_class('-apps');
		cfg('page_id','app-map');
		$this->theme->title='แผนที่เครือข่าย';
	}
}

?>