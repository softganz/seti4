<?php
function admin_config_session($self) {
	$self->theme->title='View session value';
	$ret.=print_o($_SESSION,'$_SESSION');
	return $ret;
	$session=$_SESSION;
	
	$tables = new Table();
	$tables->caption='Session value';
	$tables->header=array('variable','value');
	foreach ($session as $key=>$value) {
		$tables->rows[]=array($key.' <font color=gray>['.GetType($value).']</font>',
											is_array($value)||is_object($value)?print_o($value):str_replace('&nbsp;',' ',highlight_string($value,1)),
											'config'=>array('attr'=>'valign="baseline"')
										);
	}
	$ret.=$tables->build();
	return $ret;
}
?>