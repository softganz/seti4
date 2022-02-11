<?php
/**
* System :: Information
* Created 2021-12-17
* Modify  2021-12-17
*
* @return Widget
*
* @usage system/info
*/

class SystemInfo extends Page {
	function build() {
		return [
			'coreName' => 'Seti',
			'coreVersion' => cfg('core.version'),
			'databaseVersion' => cfg('version.install'),
		];
	}
}
?>