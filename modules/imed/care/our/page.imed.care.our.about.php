<?php
/**
* iMed Care :: How to use app
* Created 2021-08-03
* Modify  2021-08-03
*
* @return Widget
*
* @usage imed/care/our/about
*/

$debug = true;

class ImedCareOurAbout {
	function __construct() {}

	function build() {
		$isAdmin = is_admin('imed care');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เกี่ยวกับเรา',
			]),
			'body' => 'รายละเอียดเกี่ยวกับเรา',
		]); // Scaffold
	}
}
?>