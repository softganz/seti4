<?php
/**
* Project :: Fund Board Letter
* Created 2018-12-19
* Modify  2020-06-11
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @call project/fund/$orgId/board.letter
*/

$debug = true;

function project_fund_board_letter($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>หนังสือแต่งตั้งกรรมการ</h3></header>';

	$stmt = 'SELECT COUNT(*) `totals`
		FROM %org_board% b
		WHERE b.`orgid`=:orgid AND `status` = 1 AND `appointed` IS NULL';

	$hasNotAppoint = mydb::count_rows('%org_board%','`orgid` = '.$orgId.' AND `status` = 1 AND `appointed` IS NULL');

	if ($isEdit && $hasNotAppoint) {
		//$ret .= '<h3 class="title notify">รายชื่อกรรมการที่ยังไม่แต่งตั้ง</h3><div class="box">';
		$ret .= R::Page('project.fund.board.letter.appoint',NULL, $fundInfo);
		$ret .= '</div>';
	}



	mydb::where('tr.`formid` = "fund" AND tr.`part` = "boardletter" AND `refid` = :orgid', ':orgid',$orgId);

	$stmt = 'SELECT
			tr.`trid`, tr.`refid`, tr.`refcode`
		, tr.`flag`, tr.`date1` `noticeDate`
		, f.`fundid`, f.`fundname`, f.`nameampur`, f.`namechangwat`
		, tr.`detail1` `orgName`
		, tr.`detail2` `nayokName`
		, tr.`detail3` `positionName`
		, tr.`detail4` `docNo`
		, tr.`text1` `docDate`
		FROM %project_tr% tr
			LEFT JOIN %project_fund% f ON tr.`refid` = f.`orgid`
		%WHERE%';
	$letterInfo = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('no'=>'','ประเภทหนังสือ','เลขที่หนังสือ', 'dateapr' =>'วันที่แต่งตั้ง','flag  -hover-parent'=>'แจ้ง');
	$no = 0;
	$letterTypes = array('new'=>'แต่งตั้งกรรมการชุดใหม่','add'=>'เพิ่มเติมกรรมการใหม่','change'=>'เปลี่ยนแปลงกรรมการ');
	foreach ($letterInfo->items as $rs) {
		$ui = new Ui('span');
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/board.letter.'.$rs->refcode.'/'.$rs->trid).'"><i class="icon -viewdoc"></i></a>');
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/board.letter.del/'.$rs->trid).'" data-rel="notify" data-title="ลบหนังสือแต่งตั้ง" data-confirm="ต้องการลบหนังสือแต่งตั้ง กรุณายืนยัน?" data-done="remove:parent tr | load:#main:'.url('project/fund/'.$orgId.'/board').'"><i class="icon -cancel -gray"></i></a>');
		}
		if ($ui->count()) $menu.='<nav class="nav iconset -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
			++$no,
			$letterTypes[$rs->refcode],
			$rs->docNo,
			$rs->docDate,
			($rs->flag ? '<span class="-disabled">แจ้ง สปสช.เขต แล้ว</span>' : ($isEdit ? '<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/info/board.letter.sendnotice/'.$rs->trid).'" data-rel="notify" data-done="load:#main:'.url('project/fund/'.$orgId.'/board').' | load:box:'.url('project/fund/'.$orgId.'/board.letter').'" title="คลิกเพื่อแจ้ง">' : '').'<i class="icon -save"></i><span>แจ้ง สปสช.เขต</span>'.($isEdit ? '</a>':''))
			.$menu,
		);
	}
	$ret .= $tables->build();
	//$ret .= print_o($letterInfo,'$letterInfo');

	return $ret;
}
?>