<?php
/**
* Admin :: View User SignIn Cache
* Created 2018-03-07
* Modify  2021-02-06
*
* @param Object $self
* @return String
*
* @usage admin/log/cache
*/

$debug = true;

function admin_log_cache($self) {
	// Data model
	$order = SG\getFirst(post('o'),'expire');
	$clearCaches = post('cid');
	$showData = post('data');

	if (post('delete') && post('cid')) {
		mydb::query('DELETE FROM %cache% WHERE cid IN ( :cid )', ':cid', 'SET-STRING:'.implode(',', $clearCaches));
		//$ret .= mydb()->_query;
	}


	$ctime = time();
	$dbs = mydb::select('SELECT c.*, `expire` as `remain`, u.`name`, u.`last_login`, u.`roles` FROM %cache% c LEFT JOIN %users% u ON u.`username` = c.`headers` ORDER BY '.addslashes($order).' ASC');


	// View model
	$self->theme->title = 'Cache viewer '.number_format($dbs->count()).' sessions.';
	//mydb::query('OPTIMIZE TABLE %cache%');

	$ret .= '<form method="post" action="'.url(q()).'">';

	$ret .= '<nav class="nav -page">'
		. '<button class="btn" type="submit" name="delete" value="Delete"><i class="icon -delete"></i><span>DELETE SELECTED CACHES</span></button> '
		. '<a class="btn" href="'.url('admin/log/cache').'"><i class="icon -refresh"></i><span>REFRESH</span></a> '
		. '<a class="btn -danger" href="'.url('admin/log/cache/clear').'"><span>CLEAR ALL CACHES</span></a>'
		. '</nav>';

	$tables = new Table();
	$tables->caption = 'Cache viewer';

	$tables->thead = array('','<a href="?o=headers">header</a>','<a href="?o=roles">roles</a>','<a href="?o=expire">remain in sec.</a>','<a href="?o=created">created</a>','<a href="?o=last_login">last login</a>','<a href="?o=cid">cid</a>');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<input type="checkbox" name="cid[]" value="'.$rs->cid.'" />',
			'<strong>'.$rs->headers.'</strong><br />'.$rs->name,
			$rs->roles,
			sg_remain2day($rs->remain - $ctime),
			date('Y-m-d H:i:s', $rs->created),
			$rs->last_login,
			$rs->cid
		);

		if ($showData) $tables->rows[] = '<tr><td colspan="7">'.$rs->data.'</td></tr>';
	}
	$ret .= $tables->build();
	$ret .= '</form>';

	return $ret;
}
?>