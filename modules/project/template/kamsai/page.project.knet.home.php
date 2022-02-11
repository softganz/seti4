<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_knet_home($self) {
	$getSortBy = SG\getFirst(post('s'),'name');

	R::View('project.toolbar', $self, 'เครือข่ายศูนย์เรียนรู้ต้นแบบ', 'knet');

	if ($getSortBy == 'prov') $sortBy = 'CONVERT(`changwat` USING tis620) ASC';
	else $sortBy = 'CONVERT(`name` USING tis620) ASC';

	mydb::value('$ORDERBY$', $sortBy);

	$stmt = 'SELECT
		  o.`orgid`,  o.`name`
		, o.`areacode`, cop.`provname` `changwat`
		, (SELECT COUNT(*) FROM %db_org% c WHERE c.`parent` = o.`orgid`) `childs`
		FROM %school% s
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = SUBSTR(o.`areacode`,1,2)
		WHERE s.`networktype` = 1
		ORDER BY $ORDERBY$';
	$dbs = mydb::select($stmt);


	$tables = new Table();
	$tables->thead = array(
		'no'=>'',
		'โรงเรียน <a href="'.url('project/knet',array('s'=>'name')).'"><i class="icon -material'.($getSortBy != 'name' ? ' -gray' : '').'">unfold_more</i></a>',
		'changwat -center'=>'จังหวัด <a href="'.url('project/knet',array('s'=>'prov')).'"><i class="icon -material'.($getSortBy != 'prov' ? ' -gray' : '').'">unfold_more</i></a>',
		'childs -amt'=>'โรงเรียนเครือข่าย',
	);
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			// '<a href="'.url('project/knet/'.$rs->orgid).'">'.$rs->name.'</a>',
			'<a href="'.url('org/'.$rs->orgid).'">'.$rs->name.'</a>',
			$rs->changwat,
			$rs->childs > 0 ? $rs->childs : '-',
		);
	}

	$ret .= $tables->build();
	//$ret .= print_o($dbs);
	return $ret;
}
?>