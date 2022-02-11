<?php
/**
* Project :: Fund Upgrade
* Created 2017-09-26
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/upgrade
*/

$debug = true;

function project_fund_upgrade($self) {
	R::view('project.toolbar',$self,'Upgrade - กองทุนตำบล','fund');

}
/*
-- Copy data from fund name to db_org
DELETE FROM %db_org% WHERE orgid>=6;
ALTER TABLE %db_org% auto_increment = 6;

INSERT INTO %db_org% (`uid`, `parent`, `sector`, `name`, `shortname`, `created`)
SELECT u.`uid`,5,8, CONCAT("กองทุนสุขภาพตำบล",f.`fundname`),f.`fundid`,UNIX_TIMESTAMP()
	FROM `sgz_project_fund` f
    LEFT JOIN %users% u ON u.`username`=f.`fundid`;

-- Copy user to org_officer
DELETE FROM %org_officer% WHERE `orgid`>=6;
INSERT INTO %org_officer% (`orgid`, `uid`, `membership`)
SELECT o.`orgid`, u.`uid`, "admin"
	FROM %project_fund% f
    LEFT JOIN %users% u ON u.`username`=f.`fundid`
    LEFT JOIN %db_org% o ON o.`shortname`=f.`fundid`;

*/
?>