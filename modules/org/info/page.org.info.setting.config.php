<?php
/**
* Module :: Description
* Created 2021-11-09
* Modify  2021-11-09
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class OrgInfoSettingConfig extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return message('error', 'PROCESS ERROR');

		$isAdmin = is_admin();

		if (!$isAdmin) return message('error', 'Access Denied');

		$initCmdKey = 'org:SETTING:'.$this->orgId;

		if (post('setting')) {
			property($initCmdKey, post('setting'));
			return 'SAVED';
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Configuration',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('org/'.$this->orgId.'/info.setting.config'),
						'class' => 'sg-form',
						'rel' => 'notify',
						// 'done' => 'reload',
						'children' => [
							'setting' => [
								'type'=>'textarea',
								'label'=>'Setting JSON',
								'class'=>'-fill',
								'rows'=>20,
								'value'=> property($initCmdKey),
							],
							'save' => [
								'type'=>'button',
								'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}
}
?>