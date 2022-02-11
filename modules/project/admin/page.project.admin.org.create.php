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

class ProjectAdminOrgCreate extends Page {
	function build() {
		$post = (object)post('org');

		// Create new organization
		if ($post->name) {
			$result = R::Model('org.create',$post);
			if ($result->_error) {
				return message('error',$result->_error);
			} else {
				location('project/admin/org/'.$result->orgid);
			}
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างหน่วยงานใหม่',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Form([
				'variable' => 'org',
				'action' => url('project/admin/org/create'),
				'id' => 'org-add-org',
				'class' => 'sg-form',
				'rel' => 'none',
				'done' => 'load: #main | close',
				'checkValid' => true,
				'children' => [
					'parent' => ['type' => 'hidden', 'value' => $post->parent],
					'name' => [
						'type' => 'text',
						'label' => 'ชื่อหน่วยงาน',
						'class' => '-fill',
						'require' => true,
						'value' => $post->name,
					],
					'shortname' => [
						'type' => 'text',
						'label' => 'ชื่อย่อ',
						'class' => '-fill',
						'value' => htmlspecialchars($post->shortname),
					],
					'parentname' => [
						'type' => 'text',
						'label' => 'ชื่อหน่วยงานต้นสังกัด',
						'class' => 'sg-autocomplete -fill',
						'value' => $post->parentname,
						'description' => 'กรุณาป้อนชื่อหน่วยงานต้นสังกัดและเลือกจากรายการที่แสดง',
						'attr' => [
							'data-altfld' => 'edit-org-parent',
							'data-query' => url('api/org','sectorX=other'),
						],
					],
					'sector' => [
						'type' => 'radio',
						'label' => 'ประเภทองค์กร:',
						'options' => project_base::$orgTypeList,
						'value' => SG\getFirst($post->sector,99),
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>บันทึกชื่อหน่วยงานใหม่</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>