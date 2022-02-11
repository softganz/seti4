<?php
/**
* iMed Care :: Our Team
* Created 2021-08-03
* Modify  2021-08-03
*
* @return Widget
*
* @usage imed/care/our/team
*/

$debug = true;

class ImedCareOurTeam {
	function __construct() {}

	function build() {
		$isAdmin = is_admin('imed care');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ทีมงาน',
			]),
			'body' => 'รายละเอียดทีมงาน',
		]); // Scaffold
	}
}
?>