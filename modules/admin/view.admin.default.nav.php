<?php
/**
* Admin submodule navigator
*
* @param Object $info
* @param Object $options
* @return String
*/
function view_admin_default_nav() {
	$active=q(1);

	$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('admin').'"><i class="icon -home"></i><span class="">Home</span></a>');
	//$ui->add('<a href="'.url('admin/task').'">'.tr('By task').'</a>');
	//$ui->add('<a href="'.url('admin/module').'">'.tr('By modules').'</a>');
	$ui->add('<a href="'.url('admin/content').'"><i class="icon -material">ballot</i><span>{tr:Content}</span></a>');
	$ui->add('<a href="'.url('admin/site').'"><i class="icon -material">build</i><span>Site</span></a>');
	$ui->add('<a href="'.url('admin/user').'"><i class="icon -material">people</i><span>Users</span></a>');
	$ui->add('<a href="'.url('admin/config').'"><i class="icon -material">web</i><span>Config</span></a>');
	$ui->add('<a href="'.url('admin/log').'"><i class="icon -material">check_box</i><span>Logs</span></a>');
	$ret.=$ui->build();

	if ($active=='site') {
		$ui=new Ui(NULL,'ui-nav -site');
		$ui->add('<a href="'.url('admin/site/info').'"><i class="icon -material">ballot</i><span>{tr:Site Information}</span></a>');
		$ui->add('<a href="'.url('admin/site/theme').'"><i class="icon -material">ballot</i><span>{tr:Theme}</span></a>');
		$ret.=$ui->build();
	} else if ($active=='user') {
		$ui=new Ui(NULL,'ui-nav -user');
		$ui->add('<a href="'.url('admin/user/list').'" title="All User"><i class="icon -material">ballot</i><span>All User</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=enable').'" title="Enabled User"><i class="icon -material">ballot</i><span>Enabled</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=disable').'" title="Disabled User"><i class="icon -material">ballot</i><span>Disabled</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=block').'" title="Blocked User"><i class="icon -material">ballot</i><span>Blocked</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=waiting').'" title="Waiting User"><i class="icon -material">ballot</i><span>Waiting</span></a>');
		$ui->add('<a href="'.url('admin/user/list','r=1').'" title="User have roles"><i class="icon -material">ballot</i><span>Roles</span></a>');
		$ret.=$ui->build();
	}
	return $ret;
}
?>