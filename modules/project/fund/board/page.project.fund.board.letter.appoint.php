<?php
/**
* Project :: Fund Board Letter Appoint
* Created 2019-01-24
* Modify  2020-06-11
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @call project/fund/$orgId/board.letter.appoint
*/

$debug = true;

function project_fund_board_letter_appoint($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');

	$ret = '<header class="header">'._HEADER_BACK.'<h3 class="title -box">ออกหนังสือแต่งตั้งกรรมการ</h3></header>';

	// มีการเพิ่มกรรมการ แต่ยังไม่เคยมีการแต่งตั้ง
	$isCreateAll = mydb::select('SELECT * FROM %org_board% b WHERE b.`orgid` = :orgid AND b.`status` = 1 AND b.`appointed` IS NOT NULL', ':orgid', $orgId)->_empty;

	// เพิ่มเติมกรรมการ เมื่อ position 19=ผู้แทนศูนย์ประสานงานหลักประกันสุขภาพประชาชน, 25 = ผู้แทนหน่วยรับเรื่องร้องเรียนอิสระ
	$isAddPosition = mydb::select('SELECT * FROM %org_board% b WHERE b.`orgid` = :orgid AND b.`status` = 1 AND b.`appointed` IS NOT NULL AND b.`position` IN (19,25)', ':orgid', $orgId)->count();


	$ui = new Ui();
	if ($isCreateAll) {
		$ui->add('<a class="sg-action btn -primary" href="'.url('project/fund/'.$orgId.'/info/board.letter.create', array('type'=>'new')).'" data-done="close" data-title="ออกหนังสือแต่งตั้งกรรมการ" data-confirm="ต้องการทำหนังสือแต่งตั้งกรรมการ กรุณายืนยัน?"><i class="icon -material -white -sg-32 -sg-block-center">people</i><span>แต่งตั้งกรรมการชุดใหม่</span></a> ');
	} else {
		if ($isAddPosition) {
			$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/info/board.letter.create', array('type'=>'add')).'" data-done="close" data-title="ออกหนังสือเพิ่มเติมกรรมการ" data-confirm="ต้องการทำหนังสือเพิ่มเติมกรรมการ กรุณายืนยัน?"><i class="icon -material -sg-32 -sg-block-center">group_add</i><span>เพิ่มเติมกรรมการตามข้อ ๑๒(๗)</span></a> ');
		}

		$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/info/board.letter.create', array('type'=>'change')).'" data-rel="box" data-done="close | load" data-title="ออกหนังสือเปลี่ยนแปลงกรรมการ" data-confirm="ต้องการทำหนังสือเปลี่ยนแปลงกรรมการ กรุณายืนยัน?"><i class="icon -material -sg-32 -sg-block-center">sync</i><span>เปลี่ยนแปลงกรรมการ</span></a> ');
	}

	$ret .= '<nav class="nav -page -sg-text-center" style="margin: 32px 0;">'.$ui->build().'</nav>';

	$stmt = 'SELECT b.*, bp.`name` `boardName`, p.`name` `positionName`, p.`weight`
		FROM %org_board% b
			LEFT JOIN %tag% bp ON bp.`catid` = b.`boardposition` AND bp.`taggroup` = "project:board"
			LEFT JOIN %tag% p ON p.`taggroup` = "project:boardpos" AND p.`catid` = b.`position`
		WHERE b.`orgid`=:orgid AND `status` = 1 AND `appointed` IS NULL
		ORDER BY `boardposition`, `weight`, `posno`';

	$dbs = mydb::select($stmt,':orgid',$orgId);




	$tables = new Table('item -board');
	$tables->caption = 'รายชื่อกรรมการที่ยังไม่แต่งตั้ง';
	$tables->thead = array(
		'no' => '',
		'',
		'name -nowrap' => 'ชื่อ-นามสกุล',
		'position -nowrap' => 'ตำแหน่ง',
		'องค์ประกอบของคณะกรรมการ',
		'datein -date' => 'เริ่มดำรงตำแหน่ง',
		'datedue -date' => 'ครบวาระ',
		'status -nowrap' => 'สถานะ'
	);

	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a class="btn -link -circle32"><i class="icon -person -circle32"></i></a>',
			$rs->prename.$rs->name,
			$rs->boardName,
			$rs->positionName
			. ($rs->posno>=1 ? ' คนที่  '.$rs->posno : '')
			. ($rs->fromorg ? '<br />'.$rs->fromorg : ''),
			($rs->datein?sg_date($rs->datein,'ว ดด ปปปป'):''),
			($rs->datedue?sg_date($rs->datedue,'ว ดด ปปปป'):''),
			($rs->appointed ? 'แต่งตั้ง' : 'ยังไม่แต่งตั้ง')
		);
	}
	$ret.=$tables->build();

	return $ret;
}
?>