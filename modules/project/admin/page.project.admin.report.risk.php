<?php
/**
 * Order report
 *
 * @param Integer $oid
 * @return String
 */
function project_admin_report_risk($self) {
	R::View('project.toolbar',$self,'ระดับความเสี่ยงของโครงการ','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');


	mydb::where('p.`risk` > 0');
	if ($year) mydb::where('p.`pryear` = :year',':year',$year);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		p.*
		, t.`title`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY `pryear` DESC, CONVERT(`title` USING tis620) ASC';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	if ($dbs->_empty) {
		$ret.='<p class="notify">ไม่มีข้อมูลตามเงื่อนไขที่ระบุ</p>';
	} else {
		$tables = new Table();
		$tables->header=array('ปี','ชื่อโครงการ','ระดับความเสี่ยง','สถานะโครงการ');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				$rs->pryear+543,
				'<a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',
				$rs->risk<10?'ความเสี่ยงระดับ '.$rs->risk : 'รอยุติโครงการ',
				$rs->project_status,
			);
		}


		$ret .= $tables->build();
	}
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>