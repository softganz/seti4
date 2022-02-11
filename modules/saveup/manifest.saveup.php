<?php
/**
 * saveup.init class for saveup management
 *
 * @package saveup
 * @version 0.40
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2008-05-21
 * @modify 2019-04-25
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('saveup.version','0.40');
cfg('saveup.release','19.4.25');

cfg('web.secondary',cfg('saveup.secondary'));

define('_WESTCODE','westcode');

menu('saveup/member/check','SaveUp Member Check','saveup','__controller',1,true,'static');
menu('saveup/payment','Save payment form','saveup','__controller',1,true,'static');
menu('saveup/bank','SaveUp Bank','saveup','__controller',1,'access saveup content','static');
menu('saveup/app','SaveUp App','saveup','__controller',1,true,'static');
menu('saveup','SaveUp Home','saveup','__controller',1,'create saveup content','static');


cfg('saveup.permission', 'administer saveups,create saveup content,edit own saveup content,access saveup content');

require_once('class.saveup.model.php');

/********************************************
 * Class  :: saveup_base
********************************************/
class saveup_base extends module {
	var $module='saveup';

	function __construct() {
		cfg('page_id','saveup');
		$this->theme->option->title=true;
		$this->theme->option->container=true;
		R::View('saveup.toolbar',$this,'ระบบงานกลุ่มออมทรัพย์ '.cfg('saveup.version'));
	}
} // end of class saveup_base

// class saveup extends saveup_base {

// 	function __construct() {
// 		parent::__construct(); //Call parents constructor
// 	}
// } // end of class saveup

class saveup_var {
	public static $payType = array(
		1 => 'ยา',
		2 => 'รักษาพยาบาล',
		3 => 'รักษาฟัน',
		4 => 'เลนส์สายตา',
		5 => 'นวดแผนโบราณ',
		6 => 'บริการทางการแพทย์',
		7 => 'อุปกรณ์การแพทย์',
		8 => 'ยาและตรวจรักษา',
	);

}
?>