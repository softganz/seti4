<?php
/**
* iCar User
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_admin_user($self) {
	$self->theme->title = 'User Listing';
	R::View('icar.toolbar', $self);

	$ret = '';

	$stmt = 'SELECT
		  iu.*
		, u.`username`, u.`name`, u.`status`
		, u.`datein`, u.`last_login`
		, s.`shopname`
		FROM %icarusr% iu
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %icarshop% s USING(`shopid`)
		ORDER BY `shopid` ASC, `username` ASC
		';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('no' => '', 'Shop', 'Username', 'Name', 'Member Ship', 'Status', 'Date In', 'logdate -date -hover-parent' => 'Last Login');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->shopname,
			$rs->username.'('.$rs->uid.')',
			$rs->name,
			$rs->membership,
			$rs->status,
			$rs->datein,
			$rs->last_login
			.'<nav class="nav iconset -hover"><a href="'.url('admin/user/edit/'.$rs->uid).'"><i class="icon -edit"></i></a></nav>',
		);
	}
	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>