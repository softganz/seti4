<?php
/**
* admin :: Manage Category
* Created 2018-11-19
* Modify  2020-12-22
*
* @param Object $self
* @return String
*
* @usage admin/category[/{id}/{action}]
*/

$debug = true;

function admin_category($self, $tagId = NULL, $action = NULL) {
	$ret = '';

	$tagInfo = NULL;
	if (is_numeric($tagId)) {
		$stmt = 'SELECT * FROM %tag% WHERE `tid` = :tagId LIMIT 1';
		$tagInfo = mydb::select($stmt, ':tagId', $tagId);
	} else {
		$tagInfo = $tagId;
	}

	switch ($action) {
		case 'save':
			if (post('name')) {
				$data = new stdClass();
				$data->tid = \SG\getFirst($data->tid,post('tid'));
				$data->taggroup = post('taggroup');
				$data->name = post('name');
				$data->catid = post('catid');
				$data->process = \SG\getFirst(post('process'), NULL);
				if (empty($data->catid)) {
					$data->catid = mydb::select('SELECT MAX(`catid`) `lastid` FROM %tag% WHERE `taggroup` = :taggroup LIMIT 1', ':taggroup',$data->taggroup)->lastid + 1;
					$rs = mydb::select('SELECT MAX(`catid`) `lastid` FROM %tag% WHERE `taggroup` = :taggroup LIMIT 1', ':taggroup',$data->taggroup);
				}
				$data->catparent = \SG\getFirst(post('parent'),NULL);
				if ($data->catparent == 'remove') $data->catparent = NULL;
				$data->weight = post('weight');

				$stmt = 'INSERT INTO %tag%
					(`tid`, `taggroup`, `catid`, `name`, `catparent`, `process`, `weight`)
					VALUES
					(:tid, :taggroup, :catid, :name, :catparent, :process, :weight)
					ON DUPLICATE KEY UPDATE
					`catid` = :catid, `name` = :name, `catparent` = :catparent
					, `process` = :process, `weight` = :weight
					';
				mydb::query($stmt, $data);
				$ret .= mydb()->_query;
			}
			$ret .= print_o(post(),'post()');
			break;

		default:
			if (empty($action) && empty($tagId)) $action = 'home';
			else if (empty($action) && $tagId) $action = 'view';

			$argIndex = 3; // Start argument

			//debugMsg('PAGE CONTROLLER Id = '.$tagId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//debugMsg(func_get_args(), '$args');

			$ret = R::Page(
				'admin.category.'.$action,
				$self,
				$tagInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	return $ret;







	$ret = '<div id="admin-category" class="admin-category">';

	$taggroup = post('g');

	$stmt = 'SELECT `taggroup`,COUNT(*) `total` FROM %tag% WHERE `taggroup` IS NOT NULL GROUP BY `taggroup`';
	$dbs = mydb::select($stmt);

	$navbar = '<nav class="nav -page"><header class="-header"><h3>Tag Group Management</h3></header>'._NL;
	$ui = new Ui(NULL,'ui-nav');
	foreach ($dbs->items as $rs) {
		if (in_array($rs->taggroup, array('project:expcode'))) continue;
		$ui->add('<a class="sg-action btn" href="'.url('admin/category',array('g'=>$rs->taggroup)).'" data-rel="replace:#admin-category">'.$rs->taggroup.' ('.$rs->total.')'.'</a>&nbsp;');
	}
	$navbar .= $ui->build();

	if ($taggroup) {
		//$navbar .= '<a class="sg-action btn -floating -circle48 -fixed -at-bottom -at-right" href="'.url('admin/category/create',array('g'=>$taggroup)).'" title="สร้างคำถามใหม่" data-rel="replace:#admin-category"><i class="icon -material">add</i></a>';
	}
	$navbar .= '</nav><!--navbar-->'._NL;
	//$self->theme->navbar=$navbar;

	$ret .= $navbar;


	$ret .= '<div id="info" class="admin-category-info">';
	switch ($action) {
		case 'create':
			if (post('name')) {
				$data = new stdClass();
				$data->tid = \SG\getFirst($data->tid,post('tid'));
				$data->taggroup = $taggroup;
				$data->name = post('name');
				$data->catid = post('catid');
				$data->process = \SG\getFirst(post('process'), NULL);
				if (empty($data->catid)) {
					$data->catid = mydb::select('SELECT MAX(`catid`) `lastid` FROM %tag% WHERE `taggroup` = :taggroup LIMIT 1', ':taggroup',$taggroup)->lastid + 1;
					$rs = mydb::select('SELECT MAX(`catid`) `lastid` FROM %tag% WHERE `taggroup` = :taggroup LIMIT 1', ':taggroup',$taggroup);
				}
				$data->catparent = \SG\getFirst(post('parent'),NULL);
				if ($data->catparent == 'remove') $data->catparent = NULL;
				$data->weight = post('weight');

				$stmt = 'INSERT INTO %tag%
					(`tid`, `taggroup`, `catid`, `name`, `catparent`, `process`, `weight`)
					VALUES
					(:tid, :taggroup, :catid, :name, :catparent, :process, :weight)
					ON DUPLICATE KEY UPDATE
					`catid` = :catid, `name` = :name, `catparent` = :catparent
					, `process` = :process, `weight` = :weight
					';
				mydb::query($stmt, $data);
				$ret .= mydb()->_query;
				$ret .= __admin_category_list($taggroup);
			} else {
				$ret .= __admin_category_form($taggroup);
			}
			break;

		case 'edit':
			$stmt = 'SELECT * FROM %tag% WHERE `tid` = :tid LIMIT 1';
			$data = mydb::select($stmt, ':tid', post('id'));
			$ret .= __admin_category_form($taggroup, $data);
			//$ret .= print_o($data,'$data');
			break;

		default:
			if ($taggroup) {
				$ret .= __admin_category_list($taggroup);
			}

			break;
	}

	$ret .= '</div>';
	$ret .= '</div>';


	return $ret;
}

function __admin_category_form($taggroup, $data = NULL) {
	$optionsParent = array(''=>'== Select Parent ==');
	$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catparent` IS NULL AND `name` != ""';
	foreach (mydb::select($stmt,':taggroup',$taggroup)->items as $v) {
		$optionsParent[$v->catid] = $v->name;
	}
	$optionsParent[$data->catparent] = '=== ไม่เปลี่ยนแปลง ===';
	$optionsParent['remove'] = '=== ยกเลิก ===';

	$form = new Form(NULL, url('admin/category/create', array('g' => $taggroup)), NULL, 'sg-form');
	$form->addData('rel', 'replace:#admin-category');
	$form->addData('checkValid', true);

	//$form->addField('tid', array('type'=>'hidden','value'=>$data->tid));

	$form->addField(
		'catid',
		array(
			'type' => 'text',
			'label' => 'เลขรหัสหมวด',
			'class' => '-fill',
			'value' => $data->catid,
			'placeholder' => 'Ex. 1',
			'description' => $data->catid ? 'ข้อควรระวัง!!! : การเปลี่ยนเลขรหัสหมวดอาจทำให้ข้อมูลที่เกี่ยวข้องผิดพลาดได้' : '',
		)
	);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อหมวด',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->name),
			'placeholder' => 'New Category Name',
		)
	);

	$form->addField(
		'parent',
		array(
			'type' => 'select',
			'label' => 'Child of',
			'class' => '-fill',
			'options' => $optionsParent,
			'value' => $data->catparent,
		)
	);

	$form->addField(
		'process',
		array(
			'type' => 'text',
			'label' => 'Process',
			'class' => '-fill',
			'value' => htmlspecialchars($data->process),
			'placeholder' => '',
		)
	);

	$form->addField(
		'weight',
		array(
			'type' => 'text',
			'label' => 'Weight',
			'class' => '-fill',
			'options' => '-127..128',
			'value' => \SG\getFirst($data->weight,0),
		)
	);

	$form->addField(
		'tid',
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

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>บันทึก</span>',
			'pretext' => '<a class="sg-action btn -link" href="'.url('admin/category',array('g'=>$taggroup)).'" data-rel="#main"><i class="icon -material -gray">cancel</i>{tr:CANCEL}</a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	return $ret;
}

function __admin_category_list($taggroup) {
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
		$menu = '<nav class="nav iconset -hover"><a class="sg-action" href="'.url('admin/category/edit', array('g'=>$rs->taggroup, 'id'=>$rs->tid)).'" data-rel="replace:#admin-category"><i class="icon -material">edit</i></a></nav>">';
		$tables->rows[] = array(
			$rs->tid,
			$rs->catid,
			is_null($rs->catparent) ? 'NULL' : $rs->catparent,
			$rs->name,
			$rs->process,
			$rs->weight,
			$rs->isdefault
			.$menu,
		);
	}
	$ret .= $tables->build();
	$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('admin/category/create',array('g'=>$taggroup)).'" data-rel="replace:#admin-category"><i class="icon -material">add</i></a></div>';
	return $ret;
}
?>