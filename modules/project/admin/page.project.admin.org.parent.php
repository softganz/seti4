<?php
/**
* Module :: Description
* Created 2021-09-26
* Modify  2021-09-26
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ProjectAdminOrgParent extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (post('to')) {
			mydb::query(
				'UPDATE %db_org% SET `parent` = :parent WHERE `orgid` = :orgid LIMIT 1',
				[':orgid' => $this->orgId, ':parent' => post('to')]
			);
			return;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เลือกต้นสังกันใหม่',
			]),
			'body' => new Form([
				'action' => url('project/admin/org/'.$this->orgId.'/parent'),
				'class' => 'sg-form -sg-flex',
				'rel' => 'notify',
				'done' => 'load:.box-page | load:#main',
				'children' => [
					'toname' => [
						'type' => 'text',
						'class' => 'sg-autocomplete -fill',
						'attr' => [
							'data-altfld' => 'edit-to',
							'data-query' => url('api/org'),
						],
						'placeholder' => 'ค้นชื่อหน่วยงานต้นสังกัด',
						'style' => 'width: 100%;',
						'container' => '{style: "flex: 1;"}',
					],
					// 'to' => [
					// 	'type' => 'select',
					// 	'options' => mydb::select('SELECT `orgid`, `name`
					// 		FROM %db_org%
					// 		WHERE `orgid` != :orgId
					// 		ORDER BY `sector` ASC, CONVERT(`name` USING tis620) ASC;
					// 		-- {key: "orgid", value: "name"}',
					// 		[':orgId' => $this->orgId]
					// 	)->items,
					// 	'value' => $this->orgInfo->info->parent,
					// 	'style' => 'width: 100%;',
					// 	'container' => '{style: "flex: 1;"}',
					// ],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i>',
						'container' => '{style: "margin-left: 8px;"}',
					],
					'to' => ['type' => 'hidden', 'value' => $this->orgInfo->info->parent],
				], // children
			]), // Form
		]);
	}
}
?>