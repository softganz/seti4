<?php
/**
* Project Action Join Action
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_action($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$isEdit = $projectInfo->info->isEdit;

	// Show Doing Calendar List
	R::View('project.toolbar', $self, $projectInfo->title, 'join', $projectInfo);

	if (!$projectInfo->orgid) return message('error', 'โครงการต้องอยู่ภายใต้องค์กร จึงจะสามารถสร้างใบลงทะเบียนได้');

	/*
	if ($tpid) mydb::where('`tpid` = :tpid', ':tpid', $tpid);
	$stmt = 'SELECT
		d.*, t.`title`
		, COUNT(*) `registered`
		, COUNT(IF(`isjoin`>0,1,NULL)) `joined`
		FROM %org_doings% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %org_dos% ds USING(`doid`)
		%WHERE%
		GROUP BY `doid`
		ORDER BY `doid` DESC
		LIMIT 100';
		*/


	if ($tpid) mydb::where('c.`tpid` = :tpid', ':tpid', $tpid);

	$stmt = 'SELECT
		  c.`id` `calid`
		, d.`doid`
		, IFNULL(FROM_UNIXTIME(d.`atdate`, "%Y-%m-%d"), c.`from_date`) `meetdate`
		, IFNULL(d.`doings`,c.`title`) `title`
		, COUNT(ds.`psnid`) `registered`
		, COUNT(IF(`isjoin`>0,1,NULL)) `joined`
		FROM %calendar% c
			LEFT JOIN %org_doings% d ON d.`calid` = c.`id`
			LEFT JOIN %org_dos% ds USING(`doid`)
		%WHERE%
		GROUP BY c.`id`
		ORDER BY `meetdate` ASC
		';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs, '$dbs');

	$tables = new Table();
	$tables->thead = array('date' => 'วันที่', 'กิจกรรม', 'amt -register'=>'ลงทะเบียน', 'amt -joined -hover-parent'=>'เข้าร่วม');
	//$ret .= '<ul>';
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		if ($rs->doid) {
			$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$rs->calid).'"><i class="icon -viewdoc"></i></a>');
		} else if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/join.create/'.$rs->calid).'" title="สร้างบันทึกผู้ลงทะเบียน" data-rel="none" data-callback="'.url('project/join/'.$tpid.'/'.$rs->calid).'" data-title="สร้างบันทึกผู้ลงทะเบียน" data-confirm="ต้องการสร้างบันทึกผู้ลงทะเบียน กรุณายืนยัน?"><i class="icon -material -white -primary -circle">add</i></a>');

		}
		$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
			$rs->meetdate ? sg_date($rs->meetdate, 'ว ดด ปปปป') : '',
			$rs->title
			.($tpid ? '' : '<br /><a href="'.url('project/join/'.$rs->tpid).'"><em>'.$rs->title.'</em></a>'),
			$rs->registered,
			$rs->joined
			. $menu,
		);
		//$ret .= '<li class="-hover-parent">'.$rs->doings.'<nav class="nav iconset -hover"><a href="'.url('project/join/'.$rs->tpid.'/'.$rs->calid).'"><i class="icon -viewdoc"></i></a></nav>'.'<br />'.$rs->atdate.'</li>';
		//$ret .= '<div class="-hover-parent">'.$rs->doings.'<nav class="nav iconset -hover"><a href="'.url('project/join/'.$rs->tpid.'/'.$rs->calid).'"><i class="icon -viewdoc"></i></a></nav>'.'<br />'.$rs->atdate.'</div>';
	}
	//$ret .= '</ul>';
	$ret .= $tables->build();
	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>