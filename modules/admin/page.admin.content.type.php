<?php
function admin_content_type($self) {
	$para=para(func_get_args());
	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Content types</h2>
<ul class="tabs primary">
<li class="-active"><a href="'.url('admin/content/type').'">List</a></li>
<li><a href="'.url('admin/content/type/add').'">Add content type</a></li>
</ul>
<div class="help">Below is a list of all the content types on your site. All posts that exist on your site are instances of one of these content types.</div>';
	$dbs=mydb::select('SELECT * FROM %topic_types% ORDER BY name');

	$tables = new Table();
	$tables->id='content_type';
	$tables->thead=array('Name','Type','Module','Description','Operations');
	$ui=new Ui('span');
	foreach ($dbs->items as $type) {
		$ui->clear();
		$ui->add('<a href="'.url('admin/content/type/edit/'.$type->type).'"><i class="icon -edit"></i></a>');
		if (!$type->locked) $ui->add('<a class="sg-action" href="'.url('admin/content/type/delete/'.$type->type).'" data-confirm="Delete content type. Are you sure?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i></a>');
		$tables->rows[]=array(
			'<a href="'.url('admin/content/type/edit/'.$type->type).'">'.$type->name.'</a>',
			$type->type,
			$type->module,
			$type->description,
			$ui->build(),
		);
	}
	$ret.=$tables->build();
	$ret.='</div>';
	return $ret;
}
?>