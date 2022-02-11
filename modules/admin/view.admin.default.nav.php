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
		$ui->add('<a href="'.url('admin').'"><i class="icon -home"></i><span class="">{tr:Home}</span></a>');
	//$ui->add('<a href="'.url('admin/task').'">'.tr('By task').'</a>');
	//$ui->add('<a href="'.url('admin/module').'">'.tr('By modules').'</a>');
	$ui->add('<a href="'.url('admin/content').'"><i class="icon -material">ballot</i><span>{tr:Content Management}</span></a>');
	$ui->add('<a href="'.url('admin/site').'"><i class="icon -material">build</i><span>{tr:Site Building}</span></a>');
	$ui->add('<a href="'.url('admin/user').'"><i class="icon -material">people</i><span>{tr:User Management}</span></a>');
	$ui->add('<a href="'.url('admin/config').'"><i class="icon -material">web</i><span>{tr:Site Configuration}</span></a>');
	$ui->add('<a href="'.url('admin/log').'"><i class="icon -material">check_box</i><span>{tr:Logs}</span></a>');
	$ret.=$ui->build();

	if ($active=='site') {
		$ui=new Ui(NULL,'ui-nav -site');
		$ui->add('<a href="'.url('admin/site/info').'"><i class="icon -material">ballot</i><span>{tr:Site Information}</span></a>');
		$ui->add('<a href="'.url('admin/site/theme').'"><i class="icon -material">ballot</i><span>{tr:Theme}</span></a>');
		$ret.=$ui->build();
	} else if ($active=='user') {
		$ui=new Ui(NULL,'ui-nav -user');
		$ui->add('<a href="'.url('admin/user/list').'" title="All User"><i class="icon -material">ballot</i><span>{tr:All User}</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=enable').'" title="Enabled User"><i class="icon -material">ballot</i><span>{tr:Enabled}</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=disable').'" title="Disabled User"><i class="icon -material">ballot</i><span>{tr:Disabled}</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=block').'" title="Blocked User"><i class="icon -material">ballot</i><span>{tr:Blocked}</span></a>');
		$ui->add('<a href="'.url('admin/user/list','s=waiting').'" title="Waiting User"><i class="icon -material">ballot</i><span>{tr:Waiting}</span></a>');
		$ui->add('<a href="'.url('admin/user/list','r=1').'" title="User have roles"><i class="icon -material">ballot</i><span>{tr:Roles}</span></a>');
		$ret.=$ui->build();
	}
	return $ret;
}
?>