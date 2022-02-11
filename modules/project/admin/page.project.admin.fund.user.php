<?php
function project_admin_fund_user($self) {
	R::View('project.toolbar',$self,'รายชื่อผู้ใช้งานกองทุน','admin');
	$self->theme->sidebar=R::View('project.admin.menu');
	$stmt='SELECT
					f.`fundid`
					,o.`name` `fundName`,
					u.`name` `officerName`
					,u.`email` `oficerEmail`
					, o.`address`
					, f.`nameampur`
					, f.`namechangwat`
					-- , o.`tambon`, o.`ampur`, o.`changwat`
					,of.`uid`,of.`membership`
				FROM %project_fund% f
					LEFT JOIN %db_org% o ON o.`shortname`=f.`fundid`
					LEFT JOIN %org_officer% of ON of.`orgid`=o.`orgid` AND of.`membership` IN ("owner","admin")
					LEFT JOIN %users% u ON u.`uid`=of.`uid`
				ORDER BY
					CONVERT(`namechangwat` USING tis620)
					, CONVERT(`nameampur` USING tis620)
				';
	$dbs=mydb::select($stmt);
	$ret.=mydb::printtable($dbs);
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>