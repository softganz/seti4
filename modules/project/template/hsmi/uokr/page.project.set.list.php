<?php
/**
* List Project Co-Commune
* Created 2019-09-02
* Modify  2019-09-04
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_set_list($self, $projectInfo) {
	$tpid = $projectInfo->tpid;

	$ret = '';

	R::View('project.toolbar', $self, $projectInfo->title, NULL, (Object)array('set'=>$tpid));


	$stmt = 'SELECT
		t.`tpid`, t.`title`
		, (SELECT COUNT(*) FROM %project_tr% i WHERE i.`tpid` = p.`tpid` AND i.`formid` = "valuation") `totalInno`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE t.`parent` = :coset';

	$dbs = mydb::select($stmt, ':coset', $tpid);

	$tables = new Table();
	$tables->thead = array('โครงการ', 'inno -amt' => 'นวัตกรรม');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>',
			$rs->totalInno,
		);
	}
	$ret .= $tables->build();

	$isCreateProject = user_access('create project content')
		&&  in_array('set/list', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

	if ($isCreateProject) {
		$ret.='<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/create/'.$tpid, array('rel' => 'box')).'" data-rel="box" data-width="640" title="Create New Project"><i class="icon -addbig -white"></i></a></nav>';
	}

	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>