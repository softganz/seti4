<?php
/**
 * garage class for Garage Job Management
 *
 * @package garage
 * @version 0.00.1
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2016-12-25
 * @modify 2016-12-25
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('garage.version','0.10.0');
cfg('garage.release','17.3.9');

menu('garage','Garage Homepage','garage','__controller',1,'access garage content','static');

class garage extends module {
	public static $jobStatusList=array(
								0=>'รอรถ',
								1=>'รับรถ',
								2=>'เสนอราคา',
								3=>'ตกลงราคา',
								4=>'รออะไหล่',
								5=>'รอส่งรถคืน',
								6=>'คืนรถ',
								7=>'วางบิล',
								8=>'รอเก็บเงิน',
								9=>'รับเงิน',
								10=>'ปิดงาน',
								);
	function __construct() {
		static $i;
		parent::__construct();
		cfg('page_id','garage');
		if (!cfg('garage.secondary')) cfg('web.secondary',false);
		$this->property=property('garage');
		$this->theme->option->title=true;

		$debug.='<h2>Call of #'.++$i.'</h2>';

		$traces=debug_backtrace();
		$caller=$traces[0];

		$debug="<h2>Called by {$caller['function']}";
		if (isset($caller['class'])) $debug.=" in {$caller['class']}<br />";
		$debug.='</h2><p>';
		foreach ($traces as $trace) {
			$debug.=$trace['file'].':'.$trace['line'].' >> ';
			if (isset($trace['class'])) $debug.="{$trace['class']}::";
			$debug.="{$trace['function']}()<br />";
		}
		$debug.='</p>';
		$debug.='<hr />';
		//debugMsg($debug);
		//		print_o($caller,'$caller',1);
		//		print_o($traces,'$trace',1);
	}
}

?>