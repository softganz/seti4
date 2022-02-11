<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function view_admin_menu_config() {
	$ret = '<header><h3><a href="'.url('admin/config').'">Site Configuration</a></h3></header>';


	$ui = new Ui(NULL, 'ui-card');

	$ui->add('<a href="'.url('admin/config/online').'">Clear user online</a><p>Remove all user online item from database.</p>');
	$ui->add('<a href="'.url('admin/config/session/clear').'">Clear empty session</a><p>Remove all empty from database.</p>');

	if (R()->appAgent) {
		$ui->add('<a class="sg-action" href="'.url('',array('setting:app' => '{}')).'" data-rel="none" data-done="reload"><i class="icon -material">web</i><span>Setting Agent to Desktop Web</span></a><p>Set configuration to be as Web</p>');
	} else {
		$ui->add('<a class="sg-action" href="'.url('',array('setting:app' => '{OS:%22Android%22,ver:%220.20.0%22,type:%22App%22,dev:%22Softganz%22}')).'" data-rel="none" data-done="reload"><i class="icon -material">android</i><span>Setting Agent to App</span></a><p>Set configuration to be as App</p>');
	}

	$ui->add('<a href="'.url('admin/config/daykey/clear').'">Clear daykey</a><p>Remove all daykey from database.</p>');
	$ui->add('<a href="'.url('admin/config/counter').'">Re-build counter</a><p>Re-build counter and write to database.</p>');
	$ui->add('<a href="'.url('admin/config/dbvar').'">DB variable list</a><p>List of DB config</p>');
	$ui->add('<a href="'.url('admin/config/phpinfo').'">Server information</a><p>Information for web server and php environment.</p>');
	$ui->add('<a href="'.url('admin/config/view').'">View configuration</a><p>Display all software configuration.</p>');
	$ui->add('<a href="'.url('admin/config/cookie').'">View cookies value</a><p>Display all $_COOKIES variable.</p>');
	$ui->add('<a href="'.url('admin/config/db').'">View db variable</a><p>Display only configuration stroe in table.</p>');
	$ui->add('<a href="'.url('admin/config/session').'">View session value</a><p>Display all $_SESSION variable.</p>');

//setting:app={OS:"Android",ver:"0.20",type:"icar",dev:"Softganz"}
	$ret .= $ui->build();

	return $ret;
}
?>