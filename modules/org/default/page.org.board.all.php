<?php
/**
* Project :: Fund Board Series
* Created 2018-12-26
* Modify  2020-06-10
*
* @param Object $self
* @param Object $orgInfo
* @param Int $series
* @return String
*
* @call org/{orgId}/board.all[/{series}]
*/

$debug = true;

function org_board_all($self, $orgInfo, $series = NULL) {
	// Data Model
	if (!($orgId = $orgInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEditable = $orgInfo->is->editable;

	mydb::where('b.`orgid` = :orgid', ':orgid',$orgId);
	$stmt = 'SELECT DISTINCT b.`series` FROM %org_board% b %WHERE% ORDER BY b.`series` DESC';
	$seriesDbs = mydb::select($stmt);

	if ($seriesDbs->_num_rows>1) {
		$pageUi = new Ui();
		$pageUi->add('รอบปี');
		foreach ($seriesDbs->items as $rs) {
			$pageUi->add('<a class="btn -link" href="'.url('project/fund/'.$orgId.'/board.all/'.$rs->series).'">'.($rs->series+543).'</a>');
		}
		$ret .= '<nav class="nav -page">'.$pageUi->build().'</nav>';
	}

	mydb::where('b.`orgid` = :orgid', ':orgid',$orgId);
	if ($series) mydb::where('b.`series` = :series', ':series', $series);

	$stmt = 'SELECT b.*, p.`name` `boardName`
		-- , p.`name` `positionName`, bs.`name` `boardStatus`
		, p.`weight`
		FROM %org_board% b
			LEFT JOIN %tag% p ON p.`catid` = b.`position` AND p.`taggroup` = "board:position"
			LEFT JOIN %tag% bs ON bs.`taggroup` = "board:status" AND bs.`catid` = b.`status`
		%WHERE%
		ORDER BY b.`series`,b.`status`, IF(`boardposition` = 2,0,1),`boardposition`, `weight`, `posno`';
	$dbs = mydb::select($stmt);


	// View Model
	$tables = new Table();
	$tables->addClass('-board');
	$tables->caption = 'ทำเนียบกรรมการ';
	$tables->thead = array('no'=>'','','name -nowrap' => 'ชื่อ-นามสกุล','ตำแหน่ง','องค์ประกอบของคณะกรรมการ', 'status -center' => 'สถานะ','datein -date' => 'วันที่เริ่ม', 'dateout -date -hover-parent' => 'วันที่ออก');
	$tables->addConfig('showHeader',false);
	$currentSeries = NULL;

	foreach ($dbs->items as $rs) {
		if ($currentSeries != $rs->series) {
			$currentSeries = $rs->series;
			$no = 0;
			$tables->rows[] = array('<td colspan="8">'.($currentSeries+543).'</td>','config'=>array('class'=>'subheader'));
			$tables->rows[] = '<header>';
		}
		if ($isEditable) {
			$ui = new Ui('span');
			$ui->add('<a class="sg-action" href="'.url('org/'.$orgInfo->orgid.'/board.form/'.$rs->brdid).'" data-rel="box" data-width="470px"><i class="icon -edit"></i></a>');
			$ui->add('<a class="sg-action" href="'.url('org/info/api/'.$orgInfo->orgid.'/board.delete/'.$rs->brdid).'" data-rel="notify" data-title="ลบกรรมการ" data-confirm="ต้องการลบกรรมการ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material -gray">cancel</i></a>');
			$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
		}
		$tables->rows[] = array(
			++$no,
			'<a class="btn -link -circle32"><i class="icon -person -circle32"></i></a>',
			$rs->name,
			$rs->boardName,
			$rs->positionName
			.($rs->posno>=1 ? ' คนที่  '.$rs->posno : '')
			.($rs->fromorg ? '<br />'.$rs->fromorg : ''),
			$rs->boardStatus,
			$rs->datein?sg_date($rs->datein,'ว ดด ปปปป'):'',
			($rs->dateout?sg_date($rs->dateout,'ว ดด ปปปป'):'')
			.$menu,
		);
	}
	$ret .= $tables->build();

	return $ret;
}
?>