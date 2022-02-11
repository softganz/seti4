<?php
/**
* Module :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function admin_category_home($self) {
	// Data Model
	$taggroup = post('g');
	$stmt = 'SELECT `taggroup`,COUNT(*) `total` FROM %tag% WHERE `taggroup` IS NOT NULL GROUP BY `taggroup`';
	$categoryDbs = mydb::select($stmt);

	$stmt = 'SELECT
		*
		FROM %tag%
		WHERE `taggroup` = :taggroup
		ORDER BY `catparent`,`weight`, `catid`';

	$tagDbs = mydb::select($stmt,':taggroup',$taggroup);

	$ret = '';


	// View Model
	$navBar = new Ui(NULL, 'ui-menu');
	$navBar->addConfig('container', '{tag: "nav"}');
	foreach ($categoryDbs->items as $rs) {
		$navBar->add('<a class="" href="'.url('admin/category',array('g'=>$rs->taggroup)).'">'.$rs->taggroup.' ('.$rs->total.')'.'</a>');
	}

	$self->theme->sidebar = $navBar->build();

	$ret = '<section id="admin-category-list" data-url="'.url('admin/category', array('g' => $taggroup)).'">';

	$ret .= '<h3>Tag Group Items : '.$taggroup.'</h3>';

	$tables = new Table();
	$tables->thead = array('i1 -center'=>'tid','i2 -center'=>'catid','i3 -center'=>'parent','name','i4 -center'=>'process','i5 -center'=>'weight','default -center -hover-parent'=>'default');
	foreach ($tagDbs->items as $rs) {
		$navUi = new Ui();
		$navUi->addConfig('container', '{tag: "nav", class: "nav -icons -hover"}');
		$navUi->add('<a class="sg-action" href="'.url('admin/category/'.$rs->tid.'/form').'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a>');
		$navUi->add('<a class="sg-action" href="'.url('admin/category/'.$rs->tid.'/change').'" data-rel="box" data-width="480"><i class="icon -material">change_circle</i></a>');

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

	$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('admin/category/*/form', array('g' => $taggroup)).'" data-rel="box" data-width="480"><i class="icon -material">add</i></a></div>';

	$ret .= '</section>';

	return $ret;
}
?>