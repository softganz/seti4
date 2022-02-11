<?php
/**
* Project :: Open My Last Create Planning
* Created 2021-08-13
* Modify  2021-08-13
*
* @return Widget
*
* @usage org/last
*/

$debug = true;

class ProjectPlanningLast extends Page {
	function build() {
		$myLastCreateId = mydb::select('SELECT t.`tpid` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype` = "แผนงาน" AND t.`uid` = :uid ORDER BY t.`tpid` DESC LIMIT 1', ':uid', i()->uid)->tpid;
		if ($myLastCreateId) location('project/planning/'.$myLastCreateId);
	}
}
?>