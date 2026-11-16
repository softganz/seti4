<?php
function admin_config_dbvar($self) {
	$self->theme->title = 'DB variable list';

	$tables = new Table();
	$tables->caption = 'DB variable list';
	$tables->header = ['%variable%', 'table name'];
	foreach (db() as $key => $value) {
		$tables->children[] = ['%' . $key . '%', $value];
	}
	return $tables;
}
?>
