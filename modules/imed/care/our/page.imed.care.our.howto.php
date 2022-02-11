<?php
/**
* iMed Care :: How to use app
* Created 2021-08-03
* Modify  2021-08-03
*
* @return Widget
*
* @usage imed/care/our/howto
*/

$debug = true;

class ImedCareOurHowto {
	function __construct() {}

	function build() {
		$isAdmin = is_admin('imed care');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'วิธีการใช้บริการ',
			]),
			'body' => 'รายละเอียดวิธีการใช้บริการในขั้นตอนต่าง ๆ',
		]); // Scaffold
	}
}
?>