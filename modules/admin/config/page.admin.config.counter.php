<?php
/**
 * Admin Model :: Config Counter
 * Created 2017-07-29
 * Modify  2020-10-29
 *
 * @param Object $self
 * @param Int $var
 * @return String
 *
 * @usage admin/config/counter
 */

$debug = true;

function admin_config_counter($self) {
	$self->theme->title = 'Re-build counter';

	$ret =
		'Choose <a href="'
		. url('admin/config/counter')
		. '">Refresh</a> or <a href="'
		. url('admin/config/counter', 'action=rebuild')
		. '">Re-build</a> or <a href="'
		. url('admin/config/counter', 'action=make')
		. '">Make from log</a>?';

	$oldcounter = cfg('counter');
	if ($_GET['action'] == 'make') {
		$newcounter = CounterModel::make();
		cfg_db('counter', $newcounter);
		$ret .= message('status', 'Counter was re-make.');
	} elseif ($_GET['action'] == 'rebuild') {
		$newcounter = CounterModel::make(cfg('counter'));
		cfg_db('counter', $newcounter);
		$ret .= message('status', 'Counter was re-build.');
	} else {
		$newcounter = CounterModel::make(cfg('counter'));
	}

	$tables = new Table();
	$tables->caption = 'Counter information';
	$tables->header = ['Key', 'Old value', 'New value'];
	foreach ($newcounter as $key => $value) {
		$tables->children[] = [
			$key,
			$oldcounter->{$key},
			$value,
			'config' => ['attr' => 'align="center"']
		];
	}
	$ret .= $tables->build();
	return $ret;
}
?>
