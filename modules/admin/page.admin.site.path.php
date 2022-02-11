<?php
function admin_site_path($self) {
	$self->theme->title='URL aliases';
	$ret .= sg_client_convert('<div class="help">Softganz provides complete control over URLs through aliasing, which is often used to make URLs more readable or easy to remember. For example, the alias \'about\' may be mapped onto the post at the system path \'paper/1\', creating a more meaningful URL. Each system path can have multiple aliases.</div>');

	// add new module
	if (isset($_POST['add']) && $_POST['alias'] && $_POST['system']) {
		$add_alias=$_POST['alias'];
		$add_system=$_POST['system'];
		$message.='Add alias "'.$add_alias.'" to "'.$add_system.'"';

		$stmt='INSERT INTO %url_alias% (`alias`,`system`) VALUES (:alias,:system) ON DUPLICATE KEY UPDATE `system`=`system`';
		mydb::query($stmt,':alias',$add_alias, ':system',$add_system);

		$ret.=mydb()->_query;
		$message.=mydb()->_affected_rows ?' completed.':' but the alias or system path "'.$add_alias.'" is already inused.';
	}

	if ($message) $ret.=notify($message);

	$alias=mydb::select('SELECT * FROM %url_alias% ORDER BY `alias` ASC');

	$tables = new Table();
	$tables->thead=array('Alias path','System path','Operations');
	$ui=new Ui('span');
	foreach ($alias->items as $item) {
		$ui->clear();
		$ui->add('<a href="'.url('admin/site/path/edit/'.$item->pid).'" title="Module configuration"><i class="icon -edit"></i></a>');
		$ui->add('<a class="sg-action" href="'.url('admin/site/path/remove/'.$item->pid).'" data-rel="none" data-confirm="Are you sure you want to delete path alias?" data-removeparent="tr"><i class="icon -delete"></i></a>');
		$tables->rows[]=array(
										$item->alias,
										$item->system,
										$ui->build(),
										);
	}
	$tables->rows[]=array(
										'<input class="form-text -fill" type="text" size="20" name="alias" placeholder="Enter Alias Path">',
										'<input class="form-text -fill" type="text" size="20" name="system" placeholder="Enter Existing System Path">',
										'<button class="btn -primary" type="submit" name="add" value="Create new aias"><i class="icon -addbig -white"></i><span>Create new aias</span></button>',
										);

	$ret.='<form method="post" action="'.url('admin/site/path').'">';
	$ret.=$tables->build();
	$ret.='</form>';

	return $ret;
}
?>