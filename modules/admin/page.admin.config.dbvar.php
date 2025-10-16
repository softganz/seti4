<?php
function admin_config_dbvar($self) {
	$self->theme->title='DB variable list';

	$tables = new Table();
	$tables->caption='DB variable list';
	$tables->header=array('%variable%','table name');
	foreach (db() as $key=>$value) {
		$tables->rows[]=array('%'.$key.'%',$value);
	}
	$ret.=$tables->build();
	return $ret;
}
?>