<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_stock_card($self, $orgId = NULL, $stockId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	R::View('imed.toolbar', $self, $orgInfo->name.' @ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	$ret = '';

	$isAdmin = user_access('administer imeds') || $orgInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $orgInfo->is->officer
		|| in_array($orgInfo->officers[i()->uid], ['ADMIN','MODERATOR']);

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action -back" href="javascript:void(0)" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>รายการรับ-จ่าย</h3><nav class="nav"></nav></header>';


	$stmt = 'SELECT * FROM %po_stktr% WHERE `stkid` = :stkid AND `orgid` = :orgid ORDER BY `stkdate` ASC';
	$dbs = mydb::select($stmt, ':orgid', $orgId, ':stkid', $stockId);

	$balance = 0;

	$tables = new Table();
	$tables->thead = array('stkdate -date'=>'วันที่', 'รายละเอียด', 'in -amt'=>'รับ','out -amt'=>'จ่าย','balance -amt -hover-parent'=>'คงเหลือ');
	foreach ($dbs->items as $rs) {
		if ($isEditable) {
			$ui = new Ui();
			$ui->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.tr.edit/'.$rs->stktrid).'" data-rel="box"><i class="icon -edit"></i></a>');
			$ui->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.tr.remove/'.$rs->stktrid).'" data-rel="notify" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel"></i></a>');
			$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		}
		$balance += $rs->qty;
		$tables->rows[] = array(
			sg_date($rs->stkdate,'d/m/Y'),
			$rs->refname,
			$rs->qty>0 ? number_format($rs->qty,2) : '',
			$rs->qty<0 ? number_format(abs($rs->qty),2) : '',
			number_format($balance,2)
			.$menu,
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>