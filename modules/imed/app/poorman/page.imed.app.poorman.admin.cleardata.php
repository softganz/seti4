<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_poorman_admin_cleardata($self) {
	$isAdmin = user_access('admin');

	if (!$isAdmin) return message('error', 'access denied');

	R::View('imed.toolbar',$self,'CLEAR EMPTY DATA!!!','app.poorman');

	$ret = '';

	$ret .= '<nav class="nav -page"><a class="btn -link" href="'.url('imed/app/poorman/admin/cleardata',array('clear'=>'yes')).'"><i class="icon -cancel"></i><span>CLEAR EMPTY DATA</span></a></nav>';
	if (post('clear')) {
		$stmt = 'DELETE FROM %qttran%  
						WHERE (
						`part` LIKE "POOR.TYPE.LIST.%" OR
						`part` LIKE "POOR.CAUSE.LIST.%" OR
						`part` LIKE "POOR.HEALTH.LIST.%" OR
						`part` LIKE "POOR.NEED.GOV.LIST.%" OR
						`part` LIKE "POOR.HELP.ORG.LIST.%" OR
						`part` LIKE "POOR.NEED.COMMUNITY.LIST.%"
						) AND `value` = "" ';
		mydb::query($stmt);
		$ret .= mydb()->_query;
	}


	$stmt = 'SELECT `part`, `value`, COUNT(*) FROM %qttran%  
						WHERE (
						`part` LIKE "POOR.TYPE.LIST.%" OR
						`part` LIKE "POOR.CAUSE.LIST.%" OR
						`part` LIKE "POOR.HEALTH.LIST.%" OR
						`part` LIKE "POOR.NEED.GOV.LIST.%" OR
						`part` LIKE "POOR.HELP.ORG.LIST.%" OR
						`part` LIKE "POOR.NEED.COMMUNITY.LIST.%"
						)
						GROUP BY `part`, `value`
						ORDER BY `value` ASC
						';
	$dbs = mydb::select($stmt);
	$ret .= mydb::printtable($dbs);


	/*
		POOR.TYPE.LIST.%
		POOR.CAUSE.LIST.%
		POOR.HEALTH.LIST.%
		POOR.NEED.GOV.LIST.%
		POOR.HELP.ORG.LIST.%
		POOR.NEED.COMMUNITY.LIST.%
	*/
	return $ret;
}
?>
