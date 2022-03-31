<?php
/**
* Module :: Description
* Created 2022-03-31
* Modify  2022-03-31
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class AdminConfig extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Site Configuration',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					R::View('admin.menu.config'),
				], // children
			]), // Widget
		]);
	}
}
?>

<?php
function admin_config($self) {
	$self->theme->title='Site Configuration';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.config');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>