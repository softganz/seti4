<?php
/**
* Care Plan Transaction
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function view_imed_care_plan_tran($psnInfo, $careInfo, $orgInfo) {
	$psnId = $psnInfo->psnId;
	$right = R::Model('imed.care.plan.right',$careInfo, $orgInfo);
	
	$isAccess = $right->RIGHT & _IS_ACCESS;
	$isEdit = $right->RIGHT & _IS_EDITABLE;
	$isDelete = $right->RIGHT & _IS_DELETABLE;
	$isEditTran = $right->is->tran;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	$ret .= '<div id="imed-care-plan-tran">';
	$ret .= '<header class="header -box"><h3>แผนการดูแล</h3></header>';
	$tables = new Table();
	$tables->thead = array('atdate -date'=>'วันที่', 'time -center' => 'เวลา', 'แผนการดูแล','ผลการดูแล','status -center' => 'สถานะ','');

	$cardUi = new Ui('div','ui-card -card');
	foreach ($careInfo->plan as $rs) {
		$ui = new Ui();
		if ($isEditTran) {
			$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/plan.tran.edit/'.$rs->cpid, array('tr'=>$rs->cptrid)).'" data-rel="box" data-width="640"><i class="icon -material'.($rs->seq ? ' -gray' : '').'">edit</i></a>');
			$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/plan.tran.done/'.$rs->cpid, array('tr'=>$rs->cptrid)).'" data-rel="box" data-width="640"><i class="icon -material '.($rs->seq ? '-green' : '-gray').'">'.($rs->seq ? 'done_all' : 'done').'</i></a>');
			if (empty($rs->seq)) {
				$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId.'/plan.tran.delete/'.$rs->cpid, array('tr'=>$rs->cptrid)).'" data-rel="notify" data-done="remove:parent .ui-card>.ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการ กรุณายืนยัน?" data-done=""><i class="icon -material">cancel</i></a>');

			}
		}

		$menu = '<nav class="nav -icons">'.$ui->build().'</nav>';

		$cardStr = '<div class="header"><b>@'.sg_date($rs->plandate,'ว ดด ปปปป').' '.$rs->plantime.' น.</b>';
		$cardStr .= '<nav class="nav -icons -header -sg-text-right">'.$ui->build().'</nav>';
		$cardStr .= '</div>';

		$cardStr .= '<div class="detail -plan">'._NL;
		$cardStr .= '<b>แผนการดูแล : '.$rs->careName.'</b><p>'.nl2br($rs->detail).'</p>';
		$cardStr .= '</div>';
		if ($rs->doneDetail) {
			$cardStr .= '<div class="detail -result">ผลการดูแล : '.nl2br($rs->doneDetail).'</div>';
		}
		$cardUi->add($cardStr);

		$tables->rows[] = array(
												sg_date($rs->plandate,'ว ดด ปปปป'),
												$rs->plantime,
												$rs->careName.'<p>'.nl2br($rs->detail).'</p>',
												nl2br($rs->doneDetail),
												$rs->status,
												$menu
											);
	}
	$ret .= $cardUi->build();

	//$ret .= $tables->build();

	$ret .= '</div>';
	//$ret .= print_o($careInfo, '$careInfo');

	return $ret;
}
?>