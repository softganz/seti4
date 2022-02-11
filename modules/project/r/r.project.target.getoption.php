<?php
function r_project_target_getoption($taggroup = 'project:targetgroup', $tagname = 'project:target', $options = '{}') {
	$defaults = '{debug: false, process: "all"}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result=array();

	mydb::where('t.`taggroup`=:tagname:',':tagname:',$tagname, ':taggroup:',$taggroup );
	if ($options->process != 'all') mydb::where('t.`process` = :process', ':process',$options->process);
	$stmt='SELECT
					t.`catid` `tgtid`,t.`catparent` `tagetgroupid`, t.`name`
					, p.`name` `targetgroup`
					FROM %tag% t
						LEFT JOIN %tag% p ON p.`taggroup`=:taggroup: AND p.`catid`=t.`catparent`
					%WHERE%
					ORDER BY `tgtid`';
	$dbs=mydb::select($stmt);


	foreach ($dbs->items as $rs) {
		$result[$rs->targetgroup][$rs->tgtid]=$rs->name;
	}
	return $result;
}
?>