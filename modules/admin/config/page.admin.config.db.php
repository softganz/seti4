<?php
function admin_config_db($self) {
	$self->theme->title = 'View db variable';
	$conf = cfg_db();
	ksort($conf);

	$not_show = ['db', 'encrypt_key', 'online'];

	$tables = new Table();
	$tables->caption = 'Db Variable value';
	$tables->header = ['variable', 'value'];
	foreach ($conf as $key => $value) {
		if (in_array($key, $not_show)) {
			continue;
		}
		$tables->children[] = [
			$key . ' <font color=gray>[' . GetType($value) . ']</font>',
			is_array($value) || is_object($value)
				? print_o($value)
				: str_replace('&nbsp;', ' ', highlight_string($value, 1)),
			'config' => ['attr' => 'valign="baseline"']
		];
	}
	return $tables;
}
?>
