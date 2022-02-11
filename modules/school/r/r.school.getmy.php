<?php
function r_school_getmy($uid = NULL) {
	if (empty($uid)) $uid=i()->uid;
	$result=NULL;

	$stmt='SELECT
					o.*
					FROM %school% s
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE s.`uid`=:uid';
	$dbs=mydb::select($stmt,':uid',$uid);

	$result=$dbs->items;
	return $result;
}
?>