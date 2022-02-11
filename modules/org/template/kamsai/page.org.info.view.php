<?php
/**
* Org :: Information
* Created 2021-10-13
* Modify  2021-12-04
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.view
*/

import('widget:org.nav.php');

class OrgInfoView extends Page {
	var $orgId;
	var $isEditMode;
	var $orgInfo;
	var $right;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->action = post('action');
		$this->right = (Object) [
			'edit' => $orgInfo->RIGHT & _IS_EDITABLE,
			'admin' => $orgInfo->is->orgadmin,
		];
		$this->isEditMode = $this->right->edit && $this->action === 'edit';
	}

	function build() {
		if (!$this->orgId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลตามที่ระบุ']);

		$orgInfo = $this->orgInfo;

		$isViewOnly = $this->action === 'view';
		$isEditMode = $this->right->edit && $this->action === 'edit';

		$orgConfig = cfg('org');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->orgInfo->name,
				// 'leading' => _HEADER_BACK,
				'navigator' => new OrgNavWidget($this->orgInfo),
				// 'boxHeader' => true,
			]),
			'body' => new Container([
				'class' => 'org-info'.($isEditMode ? ' sg-inline-edit' : ''),
				'id' => 'org-info',
				'attribute' => $isEditMode ? [
					'data-tpid' => $this->orgId,
					'data-update-url' => url('org/edit/info'),
					'data-url' => url('org/'.$this->orgId.'/info.view', ['action' => 'edit']),
					'data-debug' => debug('inline') ? 'inline' : NULL,
					] : [
						'data-url' => url('org/'.$this->orgId.'/info.view'),
					],
				'children' => [
					new Table([
						'class' => '-org-info',
						'children' => [
							[
								'ชื่อองค์กร',
								view::inlineedit(array('group'=>'org', 'fld'=>'name', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->name,$isEditMode),
							],
							[
								'สังกัด',
								view::inlineedit(array('group'=>'org', 'fld'=>'groupType', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->groupType,$isEditMode)
							],
							[
								'ที่อยู่โรงเรียน',
								view::inlineedit(
									[
										'group' => 'org',
										'fld' => 'house,areacode',
										'tr' => $orgInfo->orgid,
										'x-callback' => 'updateMap',
										'class' => '-fill',
										'areacode' => $orgInfo->info->areacode,
										'ret' => 'address',
										'options' => '{
												onblur: "none",
												autocomplete: {
													minLength: 5,
													target: "areacode",
													query: "'.url('api/address').'"
												},
												placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
											}',
									],
									$orgInfo->info->address,
									$isEditMode,
									'autocomplete'
								)
							],
							[
								'รหัสไปรษณีย์',
								view::inlineedit(array('group'=>'org', 'fld'=>'zipcode', 'tr'=>$orgInfo->orgid,'options'=>'{maxlength:5}'),$orgInfo->info->zipcode,$isEditMode)
							],
							[
								'โทรศัพท์',
								view::inlineedit(array('group'=>'org', 'fld'=>'phone', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->phone,$isEditMode)
							],
							[
								'โทรสาร',
								view::inlineedit(array('group'=>'org', 'fld'=>'fax', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->fax,$isEditMode)
							],
							[
								'อีเมล์',
								view::inlineedit(array('group'=>'org', 'fld'=>'email', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->email,$isEditMode)
							],
							[
								'เว็บไซต์',
								view::inlineedit(array('group'=>'org', 'fld'=>'website', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->website,$isEditMode)
							],
							[
								'เฟซบุ๊ค',
								view::inlineedit(array('group'=>'org', 'fld'=>'facebook', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->facebook,$isEditMode)
							],
							[
								'ชื่อผู้อำนวยการ',
								view::inlineedit(array('group'=>'org', 'fld'=>'managername', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->managername,$isEditMode)
							],
							[
								'ชื่อผู้ประสานงาน',
								view::inlineedit(array('group'=>'org', 'fld'=>'contactname', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->contactname,$isEditMode)
							],
							[
								'พิกัด GIS',
								view::inlineedit(array('group'=>'org', 'fld'=>'location', 'tr'=>$orgInfo->orgid, 'class'=>''),$orgInfo->info->location,$isEditMode).' <a class="sg-action" href="'.url('org/'.$this->orgId.'/info.map').'" data-rel="box" data-width="600" data-class-name="-map"><i class="icon -material">room</i></a>'
							],
						], // children
					]), // Table
					new FloatingActionButton([
						'child' => (function() {
							if ($isViewOnly) {
								// Do nothing
							} else if ($this->isEditMode) {
									return '<a class="sg-action btn -primary -circle48" href="'.url('org/'.$this->orgId.'/info.view', ['debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -material">done_all</i></a>';
							} else if ($this->right->edit) {
								return '<a class="sg-action btn -floating -circle48" href="'.url('org/'.$this->orgId.'/info.view', ['action'=> 'edit', 'debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -material">edit</i></a>';
							}
						})(), // child
					]), // FloatingActionButton
				], // children
			]), // Container
		]);
	}
}
?>