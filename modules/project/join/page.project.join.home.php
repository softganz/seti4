<?php
/**
* Project Action Join Home
* Created 2019-04-09
* Modify  2019-07-30
*
* @param Object $self
* @return String
*/

function project_join_home($self) {
	R::View('project.toolbar', $self, $projectInfo->title, 'join');
	$stmt = 'SELECT
		d.*, t.`title`
		, COUNT(*) `registered`
		, COUNT(IF(`isjoin`>0,1,NULL)) `joined`
		FROM %org_doings% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %org_dos% ds USING(`doid`)
		GROUP BY `doid`
		ORDER BY d.`atdate` DESC
		';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('date' => 'วันที่', 'กิจกรรม', 'amt -register -nowrap'=>'ลงทะเบียน', 'amt -joined -nowrap -hover-parent'=>'เข้าร่วม');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->atdate ? sg_date($rs->atdate, 'ว ดด ปปปป') : '',
			'<a href="'.url('project/'.$rs->tpid.'/join/'.$rs->calid).'">'.$rs->doings.'</a>'
			.($tpid ? '' : '<br /><a href="'.url('project/'.$rs->tpid.'/join').'"><em>'.$rs->title.'</em></a>'),
			$rs->registered,
			$rs->joined
			.'<nav class="nav iconset -hover"><a href="'.url('project/'.$rs->tpid.'/join/'.$rs->calid).'"><i class="icon -viewdoc"></i></a></nav>',
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>