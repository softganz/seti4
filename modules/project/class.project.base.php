<?php
/**
 * project class for project management
 *
 * @package project
 * @version 0.22
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2010-05-25
 * @modify 2013-03-12
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class project_base extends Module {
	var $module = 'project';

	public static $statusList = array(
		-1 => 'ไม่ผ่านรอบแรก',
		1 => 'กำลังพัฒนา',
		2 => 'ส่งพิจารณา',
		3 => 'ปรับแก้',
		5 => 'ผ่าน',
		8 => 'ไม่ผ่าน',
		9 => 'ยกเลิก',
		10 => 'ได้รับการสนับสนุน'
	);

	public static $orgTypeList = array(
		1 => 'กระทรวง',
		2 => 'กรม',
		3 => 'กอง',
		4 => 'จังหวัด',
		5 => 'อปท./เทศบาล/อบต.',
		6 => 'เอกชน',
		7 => 'ชุมชน',
		8 => 'สถานศึกษา',
		9 => 'กองทุนสุขภาพตำบล',
		10 => 'มหาวิทยาลัย',
		99 => 'อื่น ๆ',
	);


	function __construct() {
		static $i;
		parent::__construct();
		cfg('page_id','project');
		if (!cfg('project.secondary')) cfg('web.secondary',false);
		$this->property=property('project');
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
		// echo $debug;
		//		print_o($caller,'$caller',1);
		//		print_o($traces,'$trace',1);
		$debug.='<hr />';
	}

}
?>