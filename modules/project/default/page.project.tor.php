<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_tor($self) {
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	R::View('project.toolbar',$self,'Project TOR');

	mydb::where('tr.`formid` = "tor"');

	if ($getItemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage - 1) * $getItemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$getItemPerPage);
	}

	mydb::value('$ORDER$', 'ORDER BY '.$orders[$getOrder][1].' '.(strtoupper($getSort) == 'A' ? 'ASC' : 'DESC'));

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		tr.`tpid`, t.`title`, o.`name` `orgname`, tr.`created`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
		%WHERE%
		ORDER BY tr.`trid` DESC
		$LIMIT$';

	$dbs = mydb::select($stmt);

	$pagePara['q'] = $getSearch;
	$pagePara['order'] = $getOrder;
	$pagePara['sort'] = $getSort;
	$pagePara['item'] = $getItemPerPage != 100 ? $getItemPerPage : NULL;
	$pagePara['page'] = $getPage;

	$pageNav = new PageNavigator($getItemPerPage, $getPage, $dbs->_found_rows, q(), false, $pagePara);
	$itemNo = $pageNav ? $pageNav->FirstItem() : 0;

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	$tables = new Table();
	$tables->thead = array('ชื่อโครงการ','หน่วยงาน','date'=>'วันที่สร้าง');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="'.url('project/'.$rs->tpid.'/info.tor').'">'.$rs->title.'</a>',
			$rs->orgname,
			sg_date($rs->created,'d-m-Y')
		);
	}

	$ret .= $tables->build();

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	return $ret;
}
?>