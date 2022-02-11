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

cfg('garage.version','0.40.1');
cfg('garage.release','2010-10-20');

menu('garage','Garage Homepage','garage','__controller',1,'access garage content','static','{verify: "garage.verify"}');

cfg('garage.permission','administer garages,access garage content,create garage content,edit own garage content');

class GarageVar {
	public static $jobStatusList = array(
			0 => 'รอรถ',
			1 => 'รับรถ',
			2 => 'เสนอราคา',
			3 => 'ตกลงราคา',
			4 => 'รออะไหล่',
			5 => 'สถานะซ่อม',
			6 => 'คืนรถ',
			7 => 'วางบิล',
			8 => 'รอเก็บเงิน',
			9 => 'รับเงิน',
			10 => 'ปิดงาน',
		);

	public static $jobProcessList = array(
			1 => 'รอเคาะ',
			2 => 'เคาะ',
			3 => 'พื้น',
			4 => 'พ่น',
			5 => 'ประกอบ',
			6 => 'ขัดสี',
			7 => 'ล้างรถ',
			8 => 'QC',
		);
}
?>