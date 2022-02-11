<?php
/**
* Org :: Setting Init Command
* Created 2021-08-12
* Modify  2021-08-12
*
* @param Object $orgInfo
* @return Widget
*
* @usage org//{id}/setting.homepage
*/

$debug = true;

class OrgSettingHomePage extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return 'PROCESS ERROR';

		$isAdmin = is_admin();

		if (!$isAdmin) return message('error', 'Access Denied');

		$homepageKey = 'org:HOMEPAGE:'.$this->orgId;

		if (post('init')) {
			property($homepageKey, post('init'));
			return 'SAVED';
		}

		return new Widget([
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>Organization Home Page</h3></header>',
				new Form([
					'action' => url('org/'.$this->orgId.'/setting.homepage'),
					'class' => 'sg-form',
					'rel' => 'notify',
					'children' => [
						'init' => [
							'type'=>'textarea',
							'label'=>'Initial Command',
							'class'=>'-fill',
							'rows'=>20,
							'value'=>htmlspecialchars(property($homepageKey)),
						],
						'save' => [
							'type'=>'button',
							'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
							'container' => '{class: "-sg-text-right"}',
						],
					], // children
				]), // Form
			], // children
		]);
	}
}
?>