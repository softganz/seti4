<?php
/**
* Admin :: User's Ban List
* Created 2021-12-05
* Modify  2021-12-05
*
* @return Widget
*
* @usage admin/user/ban
*/

class AdminLogBan extends Page {
	function build() {
		if (cfg('core.version.code') < 5) return print_o(cfg('ban.ip'),'$banIpList');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Users Ban',
				'navigator' => 	R::View('admin.default.nav'),
			]),
			'body' => new Table([
				'class' => '-center',
				'thead' => ['IP', 'Start', 'End'],
				'children' => array_map(
					function($item, $ip) {
						return [
							$ip,
							sg_date($item->start, 'Y-m-d H:i:s'),
							sg_date($item->end, 'Y-m-d H:i:s'),
						];
					},
					$banIpList = (Array) cfg('ban.ip'),
					array_keys($banIpList)
				),
			]),
		]);
	}
}
?>