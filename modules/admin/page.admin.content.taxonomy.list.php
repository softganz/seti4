<?php
function admin_content_taxonomy_list($self,$vid) {
	$vocab = model::get_vocabulary($vid);
	$tree = model::get_taxonomy_tree($vid);

	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Tags in <em>'.$vocab->name.'</em></h2>
<ul class="tabs primary">
<li><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li class="-active"><a href="'.url('admin/content/taxonomy/list/'.$vid).'">List</a></li>
<li><a href="'.url('admin/content/taxonomy/add/'.$vid).'">Add tag</a></li>
</ul>
</div><div class="help"></div>';

	$tables = new Table();
	$tables->id='taxonomy';
	$tables->thead=array('Tag ID','Name','amt'=>'Weight','List style','List class','Operations');
	foreach ($tree as $term) {
		$tables->rows[]=array($term->tid,
			str_repeat('--', $term->depth).$term->name,
			$term->weight,
			$term->liststyle,
			$term->listclass,
			'<a href="'.url('admin/content/taxonomy/edit/'.$term->tid).'"><i class="icon -material">edit</i><span class="-hidden">Edit</span></a> <a href="'.url('tags/'.$term->tid).'">topics</a>'
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>