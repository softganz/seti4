<?php
function admin_config_daykey($self) {
	$self->theme->title='Clear daykey';
	mydb::query('DELETE FROM %block_daykey%');
	$ret.=message('status','Daykey was clear.');
	return $ret;
}
?>