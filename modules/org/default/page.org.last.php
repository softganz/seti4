<?php
/**
* Org :: Open My Last Create Organization
* Created 2021-08-13
* Modify  2021-08-13
*
* @return Widget
*
* @usage org/last
*/

$debug = true;

class OrgLast extends Page {
	function build() {
		$myLastCreateId = mydb::select('SELECT `orgid` FROM %db_org% WHERE `uid` = :uid ORDER BY `orgid` DESC LIMIT 1', ':uid', i()->uid)->orgid;
		if ($myLastCreateId) location('org/'.$myLastCreateId);
	}
}
?>