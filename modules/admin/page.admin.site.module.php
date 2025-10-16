<?php
function admin_site_module($self) {
	$self->theme->title='Site Modules';
	//$this->nav.=$this->sep.' <a href="'.url('admin/site/module').'">Site module</a> ';
	$ret .= '<div class="help">Add / Remove / Configuration site modules</div>';

	// Add new module
	if (post('add') && $module=post('module')) {
		$message=process_install_module($module);
		$ret.=notify('Module "'.$module.'" '.($message===false ? 'not found.':'install completed.'));
	}

	$ret.='<form method="post" action="'.url('admin/site/module').'">';
	$tables = new Table();
	$tables->thead=array('Modules','Permissions','Operations');
	$tables->colgroup=array(array('width'=>'20%'),array('width'=>'70%'), array('width'=>'10%'));
	foreach (cfg('perm') as $module => $perm) {
		$ui=new Ui('span');
		$ui->add('<a href="'.url($module.'/admin').'" title="Module configuration"><i class="icon -material">settings</i><span class="-hidden">Configuration</span></a>');
		if ($module != 'system') $ui->add('<a href="'.url('admin/site/module/remove/'.$module).'" class="sg-action" data-confirm="Remove this module?" data-rel="#main"><i class="icon -material">cancel</i><span class="-hidden">Remove</span></a>');
		$tables->rows[]=array('<strong>'.$module.'</strong>',$perm,$ui->build());
	}
	$tables->rows[]=array(
										'<input class="form-text -fill" type="text" size="20" name="module" placeholder="Enter module name">',
										'<td colspan="2"><button class="btn -primary" type="submit" name="add" value="add"><i class="icon -material">add</i><span>Add new module</span></button></td>'
											);

	$ret.=$tables->build();
	$ret.='</form>';
	$ret.=$message;
	return $ret;
}
?>