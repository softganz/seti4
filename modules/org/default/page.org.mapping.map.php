<?php
/**
* Organization Mapping
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_mapping_map($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self, 'Mapping', 'mapping', $orgInfo);

	$isEdit = $orgInfo->RIGHT & _IS_OFFICER;

	$ret = '';

	if (!mydb::table_exists('%map_name%')) return message('notify', 'Mapping Name not exists');

	mydb::where('m.`orgid` = :orgid', ':orgid', $orgId);
	$stmt = 'SELECT *
		FROM %map_name% m
		%WHERE%;';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;


	$cardUi = new Ui(NULL, 'ui-card -flex');
	foreach ($dbs->items as $rs) {
		$cardStr = '<a href="'.url('map/'.$rs->mapgroup).'"><span>';
		$cardStr .= '<h3>'.$rs->mapname.'</h3>';
		$cardStr .= '<img src="//img.softganz.com/img/map-1272165_640.png" width="100%" />';
		$cardStr .= '</span></a>';
		$cardStr .= '<nav class="nav -card"><a class="btn -link -fill" href="'.url('map/'.$rs->mapgroup).'"><i class="icon -pin"></i><span>View Mapping</span></a></nav>';
		$cardUi->add($cardStr);
	}
	$ret .= $cardUi->build();

	$ret .= '<style type="text/css">
	.ui-card h3 {font-size: 1.4em; position: absolute; top: 0;}
	.ui-card.-flex {display: flex; flex-wrap: wrap; justify-content: space-between;}
	.ui-card.-flex>.ui-item {width: 240px; height: 200px; overflow: hidden; margin: 16px; padding-top: 40px; padding-bottom: 0; position: relative;}
	.ui-card .nav.-card {margin:0; position: absolute; bottom: 0px; width: 100%}
	</style>';


	//$ret .= print_o($dbs,'$dbs');
	return $ret;
}
?>