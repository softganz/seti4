<?php
/**
* Project Organization Home
*
* @param Object $self
* @return String
*/
function project_org_home($self) {
	R::View('project.toolbar', $self, 'บริหารงานโครงการขององค์กร', 'org');

	$ret = '';

	$orgid=post('org');//SG\getFirst(post('org'),1);
	$suborg=post('sorg');
	$govplan=post('gp');
	$southplan=post('sp');

	$stmt='SELECT
		o.`orgid`, o.`name`, IF(t.`tpid` IS NULL,0,COUNT(*)) amt
		FROM %db_org% o
			LEFT JOIN %topic% t USING(`orgid`)
		WHERE o.`parent` IS NULL
		GROUP BY `orgid`
		ORDER BY
			CASE
				WHEN `orgid`=1 THEN 0
				WHEN `orgid`!=1 THEN CONVERT(`name` USING tis620)
			END ASC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array('<a href="'.url('project/org/'.$rs->orgid).'">'.$rs->name.'</a>',$rs->amt);
	}
	$ret .= $tables->build();

	return $ret;
}
?>