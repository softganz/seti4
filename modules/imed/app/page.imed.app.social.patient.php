<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ImedAppSocialPatient extends Page {
	var $orgId;

	function __construct($orgId) {
		$this->orgId = $orgId;
	}

	function build() {
	// $ret .= R::Page('imed.social.patient',NULL, $orgId, '{page: "app"}');

	// $ret .= '<style type="text/css">
	// .imed-box {display: none;}
	// .header .ui-item.-visit {display: none;}
	// </style>';

	// return $ret;

	return new Scaffold([
		'appBar' => new AppBar([
			'title' => '@Patient of Group',
		]), // AppBar
		'body' => new Widget([
			'children' => [
				R::Page('imed.social.patient',NULL, $this->orgId, '{page: "app"}'),
				'<style type="text/css">
				.imed-box {display: none;}
				.header .ui-item.-visit {display: none;}
				</style>'
			],
		]), // Widget
	]);
	}
}
?>