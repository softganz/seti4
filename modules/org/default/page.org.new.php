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

class OrgNew extends Page {
	function build() {

		$isCreatable = user_access('create org content');

		if (!$isCreatable) return message('error', 'Access Denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างองค์กรใหม่',
			]),
			'body' => new Form([
				'action' => url('org/create'),
				'variable' => 'data',
				'id' => 'org-add-form',
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'reload:'.url('org/last'),
				'children' => [
					'<header class="header -box -hidden">'._HEADER_BACK.'<h3>สร้างองค์กรใหม่</h3></header>',
					'srcpage' => ['type'=>'hidden','name'=>'srcpage','value'=>'org.new'],
					'parent' => ['type' => 'hidden', 'value' => SG\getFirst(post('parent'),$data->parent)],
					'orgid' => ['type' => 'hidden', 'value' => $data->orgid],
					'officer' => ['type' => 'hidden', 'value' => 'ADMIN'],
					'areacode' => ['type' => 'hidden'],
					'name' => [
						'type' => 'text',
						'label' => 'ชื่อองค์กร/หน่วยงาน',
						'class' => 'sg-autocomplete -fill',
						'require' => true,
						'value' => htmlspecialchars($data->name),
						'description' => 'กรุณาป้อนชื่อหน่วยงานของท่าน หากหน่วยงานของท่านมีในรายการแล้ว กรุณาเลือกจากรายการที่แสดง',
						'placeholder' => 'ระบุชื่อองค์กร/หน่วยงาน',
						'attr' => [
							'data-altfld'=>'edit-data-orgid',
							'data-query'=>url('org/api/org'),
						],
					],
					'shortname' => [
						'type' => 'text',
						'label' => 'ชื่อย่อ',
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อย่อเป็นภาษาอังกฤษ หรือ รหัสองค์กร',
					],
					'sector' => [
						'type' => 'select',
						'label' => 'ประเภทองค์กร:',
						'class' => '-fill',
						'options' => R::Model('category.get','sector','catid', '{selectText: "== เลือกประเภทองค์กร =="}'),
						'value' => $data->sector,
					],
					'address' => [
						'type' => 'text',
						'label' => 'ที่อยู่',
						'class' => 'sg-address -fill',
						'attr' => array('data-altfld' => 'edit-data-areacode'),
						'value' => htmlspecialchars($data->address),
						'placeholder' => 'เช่น 0 ม.0 ต.ตัวอย่าง (แล้วเลือกจากรายการแสดงด้านล่าง)',
					],
					'phone' => [
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'value' => htmlspecialchars($data->phone),
						'placeholder' => '0000000000',
					],
					'email' => [
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'value' => htmlspecialchars($data->email),
						'placeholder' => 'name@example.com',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>สร้างหน่วยงานใหม่</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
						'container' => array('class'=>'-sg-text-right'),
					],
				], // children
			]), // Form
		]);
	}
}
?>