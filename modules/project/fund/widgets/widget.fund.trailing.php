<?php
/**
* Project Widget :: Fund Trailing Menu
* Created 2021-09-10
* Modify 	2021-09-10
*
* @param Object $fundInfo
* @return Widget
*
* @usage new FundTrailingWidget([])
*/

$debug = true;

class FundTrailingWidget extends Widget {
	var $fundInfo;

	function __construct($fundInfo = NULL) {
		$this->fundInfo = $fundInfo;
	}

	function build() {
		$orgId = $fundInfo->orgId;
		list($submenu) = explode('.',q(3));

		$isWebAdmin = user_access('administer projects');
		$isEdit = $fundInfo->right->edit;

		return new Row([
			'crossAxisAlignment' => 'center',
			'children' => [
				'<form id="search" class="search-box" method="get" action="'.url('project/fund/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ค้นหาชื่อหรือรหัสกองทุน" data-query="'.url('project/api/fund',array('r'=>'f')).'" data-callback="'.url('project/fund/').'" data-altfld="sid"><button class="btn" type="submit"><i class="icon -search"></i></button></form>',
				$isWebAdmin ? new Dropbox([
					'children' => [
						'<a href="'.url('project/fund/financial').'"><i class="icon -material">attach_money</i><span>บัญชี/การเงิน</span></a>',
						'<a class="" href="'.url('project/fund/setting').'"><i class="icon -material">settings</i><span>Setting</span></a>'
					], // children
				]) : NULL,
			], // children
		]);
	}
}
?>