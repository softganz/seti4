<?php
function admin_content_taxonomy($self) {
	$para=para(func_get_args());
	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Taxonomy</h2>
<ul class="tabs primary">
<li class="-active"><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li><a href="'.url('admin/content/vocabulary/add').'">Add vocabulary</a></li>
</ul>
</div><div class="help"></div>';

	$stmt='SELECT
		  v.`vid`, v.`name`
		, GROUP_CONCAT(t.`name`) `type`
		FROM %vocabulary% v
			LEFT JOIN %vocabulary_types% vt USING(`vid`)
			LEFT JOIN %topic_types% t USING(`type`)
		GROUP BY v.`vid`
		ORDER BY v.`weight` ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->id='taxonomy';
	$tables->thead=array('amt'=>'Id','Vocabulary Name','Content Type','Operations');
	$ui=new Ui('span');
	foreach ($dbs->items as $rs) {
		$ui->clear();
		$ui->add('<a href="'.url('admin/content/vocabulary/edit/'.$rs->vid).'"><i class="icon -edit"></i><span class="-hidden">Edit vocabulary</span></a>');
		$ui->add('<a href="'.url('admin/content/taxonomy/list/'.$rs->vid).'"><i class="icon -list"></i><span class="-hidden">List tags</span></a>');
		$ui->add('<a href="'.url('admin/content/taxonomy/add/'.$rs->vid).'"><i class="icon -add"></i><span class="-hidden">Add tags</span></a>');
		$tables->rows[]=array(
			$rs->vid,
			$rs->name,
			$rs->type,
			 $ui->build(),
		);
	}
	$ret.=$tables->build();

	return $ret;
}
?>