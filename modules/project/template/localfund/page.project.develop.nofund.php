<?php
/**
* Project develope create new
*
* @param Object $self
* @return String
*/

function project_develop_nofund($self) {
	$proposalWaitReply = post('wait');

	R::View('project.toolbar',$self,'พัฒนาโครงการ/'.($proposalWaitReply ? 'รอตอบรับ' : 'รอส่ง'),'develop');


	$ret = '';


	$ret .= '<div class="sg-view -co-2">';

	$ret .= '<div class="-sg-view">';

	if ($proposalWaitReply) {
		$ret .= __project_develop_nofund_wait();
	} else {
		$ret .= __project_develop_nofund_notsend();
	}

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';
	$menuUi = new Ui(NULL, 'ui-menu');
	$menuUi->add('<a href="'.url('project/develop/nofund').'">โครงการรอส่ง</a>');
	$menuUi->add('<a href="'.url('project/develop/nofund', array('wait' => 'yes')).'">โครงการรอตอบรับ</a>');

	$ret .= $menuUi->build();
	$ret .= '</div>';
	$ret .= '</div><!-- sg-view -->';

	//$ret .= print_o($dbs,'$dbs');
	return $ret;
}

function __project_develop_nofund_wait() {
	$stmt = 'SELECT
		  d.`tpid`, d.`toorg`, t.`title`
		, d.`budget`
		, o.`name`
		, t.`uid`
		, t.`created`
		, u.`name` `ownerName`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_org% o ON o.`orgid` = d.`toorg`
		WHERE d.`toorg` IS NOT NULL AND t.`orgid` IS NULL';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('no' => '', 'ชื่อโครงการ', 'ผู้พัฒนา', 'ชื่อหน่วยงาน', 'budget -money' => 'งบประมาณ', 'create -date' => 'วันที่เริ่มพัฒนา');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>',
			$rs->ownerName,
			'<a href="'.url('project/fund/'.$rs->toorg.'/proposal').'">'.$rs->name.'</a>',
			number_format($rs->budget,2),
			sg_date($rs->created, 'ว ดด ปปปป'),
		);
	}

	$ret .= $tables->build();

	return $ret;
}

function __project_develop_nofund_notsend() {
	$stmt = 'SELECT
		  d.`tpid`, d.`toorg`
		, t.`title`, d.`budget`
		, t.`uid`
		, u.`name` `ownerName`
		, t.`created`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u USING(`uid`)
		WHERE t.`tpid` IS NOT NULL AND d.`toorg` IS NULL AND t.`orgid` IS NULL AND d.`fundid` IS NULL';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('no' => '', 'ชื่อโครงการ', 'ผู้พัฒนา', 'budget -money' => 'งบประมาณ', 'create -date' => 'วันที่เริ่มพัฒนา');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>',
			$rs->ownerName,
			number_format($rs->budget,2),
			sg_date($rs->created, 'ว ดด ปปปป'),
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>