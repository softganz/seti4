<?php
/**
* iMed :: Group Menu
* Created 2021-08-17
* Modify  2021-08-17
*
* @param Object $orgInfo
* @return Widget
*
* @usage imed/group/{id}/menu
*/

$debug = true;

class ImedGroupMenu extends Page {
	var $refApp;
	var $orgId;
	var $orgInfo;
	var $urlView = 'imed/group/';
	var $urlPatientView;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		parent::__construct();
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, page: "web"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		$orgInfo = $this->orgInfo;
		$orgId = $orgInfo->orgid;

		if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

		$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
		$isMember = $isAdmin || $orgInfo->is->socialtype;

		if (!($isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.$this->orgInfo->name,
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => 'imed-group-menu',
				'dataUrl' => url($this->urlView.$orgId.'/member'),
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => '@Menu',
							]), //
						], // children
					]), // Card
				],
			]),
		]);
	}
}
?>