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

class ImedGroupInfoHome extends Page {
	var $refApp;
	var $id = 'imed-group';
	var $urlView = 'imed/group/';
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		parent::__construct();
	}

	function build() {
		if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

		$orgId = $this->orgId;

		$isCreateGroup = is_admin('imed') || imed_model::get_user_zone(i()->uid);

		head('<style type="text/css">
			.nav.-page.-social {display: none;}
		</style>'
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@Social Groups',
				'navigator' => [
					new Ui([
						'children' => [
							'<a class="sg-action" href="'.url($this->urlView.$orgId.'/visit').'"><i class="icon -material">fact_check</i><span class="-hidden">{tr:เยี่ยมบ้าน}</span></a>',
							'<a class="sg-action" href="'.url($this->urlView.$orgId.'/patient').'" data-webview="ดูแล"><i class="icon -material">wheelchair_pickup</i><span class="-hidden">{tr:Patients}</span></a>',
							'<a class="sg-action" href="'.url($this->urlView.$orgId.'/member').'" data-webview="Group Members"><i class="icon -material">people</i><span class="-hidden">{tr:Members}</span></a>',
							// $isAdmin || $isPoCenter ? '<a class="sg-action" href="'.url('imed/app/pocenter/'.$orgId).'" data-rel="#main" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="-hidden">กายอุปกรณ์</span></a>' : '',
							'<a class="" href="'.url($this->urlView.$orgId.'/menu').'"><i class="icon -material">more_vert</i><span class="-hidden">Menu</span></a>',
						],
					]), // Ui
				], // navigator
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => $this->id,
				'children' => [
				], // children
			]), // Container
		]);
	}
}
?>