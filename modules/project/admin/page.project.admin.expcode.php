<?php
/**
* project :: Manage Expense Code
* Created 2020-12-22
* Modify  2020-12-22
*
* @param Object $self
* @return String
*
* @usage project/admin/expcode
*/

$debug = true;

function project_admin_expcode($self, $expId = NULL, $action = NULL) {
	// Data Model
	$taggroup = 'project:expcode';

	/*
	Expense code use in table:
	project_tr formid=develop part=exptr => expense code = gallery
	project_tr formid=expense part=exptr => expense code = refid
	org_dopaidtr => catid
	*/

	// View Model
	$toolbar = new Toolbar($self, 'รหัสค่าใช้จ่าย');

	switch ($action) {
		case 'form':
			if ($expId != '*') {

			}
			$ret .= __project_admin_expense_form($taggroup, $data);
			break;

		case 'change':
			if ($newId = post('to')) {
				$ret .= 'Change to '.post('to');
				$isDup = mydb::select('SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catid` = :catid LIMIT 1', ':taggroup', $taggroup, ':catid', $newId)->catid;
				//$ret .= '$isDup = '.$isDup;
				if (!$isDup) {
					$stmt = 'UPDATE %project_tr% SET `refid` = :newId, `gallery` = :newId WHERE `formid` = "develop" AND `part` = "exptr" AND `gallery` = :oldId';
					mydb::query($stmt, ':oldId', $expId, ':newId', $newId);
					$ret .= '<br />'.mydb()->_query;

					$stmt = 'UPDATE %project_tr% SET `refid` = :newId, `gallery` = :newId WHERE `formid` = "expense" AND `part` = "exptr" AND (`gallery` = :oldId OR `refid` = :oldId )';
					mydb::query($stmt, ':oldId', $expId, ':newId', $newId);
					$ret .= '<br />'.mydb()->_query;

					$stmt = 'UPDATE %org_dopaidtr% SET `catid` = :newId WHERE `catid` = :oldId';
					mydb::query($stmt, ':oldId', $expId, ':newId', $newId);
					$ret .= '<br />'.mydb()->_query;

					$stmt = 'UPDATE %tag% SET `catid` = :newId WHERE `taggroup` = :taggroup AND `catid` = :oldId';
					mydb::query($stmt, ':oldId', $expId, ':newId', $newId, ':taggroup', $taggroup);
					$ret .= '<br />'.mydb()->_query;

				}
			} else {
				$ret .= __project_admin_expcode_change_form($expId);
			}
			break;

		default:
			$ret .= __project_admin_expcode_list($taggroup);
			break;
	}






	return $ret;
}

function __project_admin_expcode_list($taggroup) {
	$ret = '<h3>Tag Group Items : '.$taggroup.'</h3>';
	$stmt = 'SELECT
		*
		FROM %tag%
		WHERE `taggroup`=:taggroup
		ORDER BY `catparent`,`weight`, `catid`';

	$dbs = mydb::select($stmt,':taggroup',$taggroup);

	$tables = new Table();
	$tables->thead = array('i1 -center'=>'tid','i2 -center'=>'catid','i3 -center'=>'parent','name','i4 -center'=>'process','i5 -center'=>'weight','default -center -hover-parent'=>'default');
	foreach ($dbs->items as $rs) {
		$navUi = new Ui();
		$navUi->addConfig('container', '{tag: "nav", class: "nav -icons -hover"}');
		$navUi->add('<a class="sg-action" href="'.url('project/admin/expcode/'.$rs->catid.'/edit').'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a>');
		$navUi->add('<a class="sg-action" href="'.url('project/admin/expcode/'.$rs->catid.'/change').'" data-rel="box" data-width="480"><i class="icon -material">change_circle</i></a>');

		$tables->rows[] = array(
			$rs->tid,
			$rs->catid,
			is_null($rs->catparent) ? 'NULL' : $rs->catparent,
			$rs->name,
			$rs->process,
			$rs->weight,
			$rs->isdefault
			.$navUi->build(),
		);
	}

	$ret .= $tables->build();

	$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/admin/expcode/*/form').'" data-rel="box" data-width="480"><i class="icon -material">add</i></a></div>';
	return $ret;
}

function __project_admin_expense_form($taggroup, $data = NULL) {
	$optionsParent = array(''=>'== Select Parent ==');
	$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catparent` IS NULL AND `name` != ""';
	foreach (mydb::select($stmt,':taggroup',$taggroup)->items as $v) {
		$optionsParent[$v->catid] = $v->name;
	}
	$optionsParent[$data->catparent] = '=== ไม่เปลี่ยนแปลง ===';
	$optionsParent['remove'] = '=== ยกเลิก ===';

	$ret = '<header class="header">'._HEADER_BACK.'<h3>ค่าใช้จ่าย</h3></header>';
	$form = new Form(NULL, url('project/admin/expcode/*/save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load');
	$form->addData('checkValid', true);

	//$form->addField('tid', array('type'=>'hidden','value'=>$data->tid));

	$form->addField('catid',
		array(
			'type' => 'text',
			'label' => 'เลขรหัสหมวด',
			'class' => '-fill',
			'require' => true,
			'value' => $data->catid,
			'placeholder' => 'Ex. 1',
			'description' => $data->catid ? 'ข้อควรระวัง!!! : การเปลี่ยนเลขรหัสหมวดอาจทำให้ข้อมูลที่เกี่ยวข้องผิดพลาดได้' : '',
		)
	);

	$form->addField('name',
		array(
			'type' => 'text',
			'label' => 'ชื่อหมวด',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->name),
			'placeholder' => 'New Category Name',
		)
	);

	$form->addField('parent',
		array(
			'type' => 'select',
			'label' => 'Child of',
			'class' => '-fill',
			'options' => $optionsParent,
			'value' => $data->catparent,
		)
	);

	$form->addField('process',
		array(
			'type' => 'text',
			'label' => 'Process',
			'class' => '-fill',
			'value' => htmlspecialchars($data->process),
			'placeholder' => '',
		)
	);

	$form->addField('weight',
		array(
			'type' => 'text',
			'label' => 'Weight',
			'class' => '-fill',
			'options' => '-127..128',
			'value' => SG\getFirst($data->weight,0),
		)
	);

	$form->addField('tid',
		array(
			'type' => 'text',
			'label' => 'Tag ID',
			'class' => '-fill',
			'value' => $data->tid,
			'placeholder' => 'Ex. 1',
			'description' => 'ข้อควรระวัง!!! : '
				.($data->tid ? 'การเปลี่ยนเลข Tag ID อาจทำให้ข้อมูลที่เกี่ยวข้องผิดพลาดได้' : 'การกำหนดเลข Tag ID ใหม่ จะต้องไม่ซ้ำกับเลข Tag ID ที่มีอยู่แล้ว'),
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

	return $ret;
}

function __project_admin_expcode_change_form($expId) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>เปลี่ยนรหัสค่าใช้จ่าย</h3></header>';

	$form = new Form(NULL, url('project/admin/expcode/'.$expId.'/change'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load');

	$form->addField(
		'to',
		array(
			'type' => 'text',
			'label' => 'รหัสใหม่',
			'require' => true,
			'placeholder' => 'Ex. 1001',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();
	return $ret;
}
?>
