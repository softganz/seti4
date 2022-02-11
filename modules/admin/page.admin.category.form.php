<?php
/**
* admin :: Category Form
* Created 2020-12-22
* Modify  2020-12-22
*
* @param Object $self
* @param Int $tagId
* @return String
*
* @usage admin/category/{id|*}/form
*/

$debug = true;

function admin_category_form($self, $tagInfo = NULL) {
	// Data Model
	if (is_string($tagInfo) && $tagInfo == '*') {
		$tagInfo = new stdClass();
		$tagInfo->taggroup = post('g');
		$tagInfo->tid = '*';
	}

	$optionsParent = array(''=>'== Select Parent ==');
	$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catparent` IS NULL AND `name` != ""';
	foreach (mydb::select($stmt,':taggroup',$tagInfo->taggroup)->items as $v) {
		$optionsParent[$v->catid] = $v->name;
	}
	$optionsParent[$tagInfo->catparent] = '=== ไม่เปลี่ยนแปลง ===';
	$optionsParent['remove'] = '=== ยกเลิก ===';


	// View Model
	$ret = '<header class="header">'._HEADER_BACK.'<h3>Category</h3></header>';

	$form = new Form(NULL, url('admin/category/'.$tagInfo->tid.'/save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load->replace:#admin-category-list');
	$form->addData('checkValid', true);

	//$form->addField('tid', array('type'=>'hidden','value'=>$tagInfo->tid));
	$form->addField('taggroup', array('type' => 'hidden', 'value' => $tagInfo->taggroup));

	$form->addField('catid',
		array(
			'type' => 'text',
			'label' => 'เลขรหัสหมวด',
			'class' => '-fill',
			'require' => true,
			'readonly' => $tagInfo->catid ? true : false,
			'value' => $tagInfo->catid,
			'placeholder' => 'Ex. 1',
			'description' => $tagInfo->catid ? 'ข้อควรระวัง!!! : การเปลี่ยนเลขรหัสหมวดอาจทำให้ข้อมูลที่เกี่ยวข้องผิดพลาดได้' : '',
		)
	);

	$form->addField('name',
		array(
			'type' => 'text',
			'label' => 'ชื่อหมวด',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($tagInfo->name),
			'placeholder' => 'New Category Name',
		)
	);

	$form->addField('parent',
		array(
			'type' => 'select',
			'label' => 'Child of',
			'class' => '-fill',
			'options' => $optionsParent,
			'value' => $tagInfo->catparent,
		)
	);

	$form->addField('process',
		array(
			'type' => 'text',
			'label' => 'Process',
			'class' => '-fill',
			'value' => htmlspecialchars($tagInfo->process),
			'placeholder' => '',
		)
	);

	$form->addField('weight',
		array(
			'type' => 'text',
			'label' => 'Weight',
			'class' => '-fill',
			'options' => '-127..128',
			'value' => SG\getFirst($tagInfo->weight,0),
		)
	);

	$form->addField('tid',
		array(
			'type' => 'text',
			'label' => 'Tag ID',
			'class' => '-fill',
			'value' => $tagInfo->tid,
			'placeholder' => 'Ex. 1',
			'description' => 'ข้อควรระวัง!!! : '
				.($tagInfo->tid ? 'การเปลี่ยนเลข Tag ID อาจทำให้ข้อมูลที่เกี่ยวข้องผิดพลาดได้' : 'การกำหนดเลข Tag ID ใหม่ จะต้องไม่ซ้ำกับเลข Tag ID ที่มีอยู่แล้ว'),
		)
	);

	$form->addField('save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="#" data-rel="close"><i class="icon -material -gray">cancel</i>{tr:CANCEL}</a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($tagInfo,'$tagInfo');

	return $ret;
}
?>