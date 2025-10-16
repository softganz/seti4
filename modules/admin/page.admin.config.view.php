<?php
function admin_config_view($self, $key = NULL) {
	$self->theme->title = 'View configuration';
	if ($key) {
		$cfg = cfg($key);
	} else {
		$cfg = cfg();
	}
	ksort($cfg);
	
	$not_show = array('db','encrypt_key','counter','online');

	$tables = new Table();
	$tables->caption = 'Configuration value'.($para->view?' of <em>'.$para->view.'</em>':'');
	$tables->header=array('variable','value');
	foreach ($cfg as $key => $value) {
		if (in_array($key,$not_show)) continue;
		if (is_array($value) || is_object($value)) {
			$valueShow = print_o($value);
		} else if (is_bool($value)) {
			$valueShow = $value ? 'True' : 'False';
		} else {
			$valueShow = str_replace('&nbsp;', ' ', highlight_string($value,1));
		}

		$tables->rows[] = array(
											'<a class="sg-action" href="'.url('admin/config/edit',array('name'=>$key)).'" data-rel="box" data-width="480">'.$key.'</a> <font color=gray>['.GetType($value).']</font>',
											$valueShow,
											'config' => array('attr'=>'valign="baseline"')
										);
	}
	$ret .= $tables->build();
	return $ret;
}
?>