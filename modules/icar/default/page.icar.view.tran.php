<?php
/**
* Show cost transaction with interest
*
* @param Record Set $carInfo
* @return String
*/

$debug = true;

function icar_view_tran($self, $carId, $newid = NULL) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$currentUid = i()->uid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = ($isAdmin || ($isShopOfficer && $carInfo->iam != 'VIEWER') ) && empty($carInfo->sold);
	$isEditable = ($isAdmin || $isShopOfficer) && empty($carInfo->sold);
	$isDeletable = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER','OFFICER'))) && empty($carInfo->sold);
	//$isDeletable = $isShopOfficer && ;


	if ($isEditable) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('icar/edit/cost');
		$inlineAttr['data-tpid'] = $carInfo->tpid;
		//$inlineAttr['data-refresh-url'] = url('icar/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$ret .= '<div id="icar-tran" '.sg_implode_attr($inlineAttr).'>'._NL;
	

	$tables = new Table();
	$tables->addId('icar-cost-tran');
	$tables->addClass('icar-cost-tr');
	$tables->caption='{tr:Transaction}';
	$tables->thead='<thead>'
	.'<tr><th rowspan="2"></th><th rowspan="2">{tr:Date}</th><th rowspan="2">{tr:Detail}</th><th colspan="3">{tr:Interest}</th><th colspan="2">{tr:Amount} ({tr:THB})</th></tr>'
		.'<tr><th>%</th><th>{tr:Day}</th><th>{tr:THB}</th><th>{tr:Receive}</th><th>{tr:Paid}</th></tr>'
		.'</thead>';
	$tables->colgroup = array('no'=>' ','trdate -date'=>'','','','','','','paid -hover-parent'=>'');
	$no=0;
	$show_saledate=false;

	foreach ($carInfo->tr as $irs) {
		$isItemEdit = $isEdit || $irs->uid == $currentUid;
		if ($carInfo->saledate && !$show_saledate && $carInfo->saledate<$irs->itemdate) {
			$tables->rows[] = array(
				'<td></td>',
				sg_date($carInfo->saledate,'d/m/Y'),
				'<td colspan="6"><strong>ลงบันทึกวันที่ขายเมื่อวันที่ '.sg_date($carInfo->saledate,'d/m/Y').'</strong></td>',
				'config'=>array('class'=>'saledate')
			);
			$show_saledate=true;
		}
		$config=array();
		$config['class']='-cost icar-tr-'.$irs->taggroup;
		if ($irs->process) $config['class'].=' icar-cost-process-'.$irs->process;
		$config['class'].=' icar-tr-'.$irs->costcode;

		$isNewTran = $newid && $newid == $irs->costid;

		if ($isNewTran) $config['class'].=' -newtran';

		$dr=$cr=0;
		if (in_array($irs->taggroup,array('icar:tr:rcv','icar:tr:down','icar:tr:finance'))) {
			$dr=abs($irs->amt);
		} else if (in_array($irs->taggroup,array('icar:tr:cost','icar:tr:notcost','icar:tr:exp'))) {
			$cr=abs($irs->amt);
		} else if ($irs->taggroup=='icar:tr:info' && $irs->amt>=0) {
			$dr=abs($irs->amt);
		} else if ($irs->taggroup=='icar:tr:info' && $irs->amt<0) {
			$cr=abs($irs->amt);
		}
		$tables->rows[] = array(
			++$no,
			view::inlineedit(array('group'=>'cost','fld'=>'itemdate','tr'=>$irs->costid),sg_date($irs->itemdate,'d/m/Y'),$isItemEdit,'datepicker'),
			view::inlineedit(
				array(
					'group'=>'cost',
					'fld'=>'costcode',
					'tr'=>$irs->costid,
					'value'=>$irs->costcode,
					'options' => '{class: "-fill", loadurl: "'.url('icar/edit/cost',array('action'=>'get','fld'=>'costcode','tr'=>$irs->costid)).'"}',
			),
				$irs->costname,
				$isItemEdit,
				'select'),
			view::inlineedit(
				array('group'=>'cost','fld'=>'interest','tr'=>$irs->costid,'options'=>'{class: "-interest", placeholder: "-"}'),
				$irs->interest > 0 ? $irs->interest : '',
				$isItemEdit,
				'text'
			),
			$irs->interestday > 0 ? $irs->interestday : '',
			($irs->interestamt>0?number_format($irs->interestamt,2):''),
			$dr?view::inlineedit(array('group'=>'cost','fld'=>'amt','tr'=>$irs->costid,'ret'=>'numeric'),number_format($dr,2),$isItemEdit,'text'):'',
			($cr ? view::inlineedit(
							array('group'=>'cost','fld'=>'amt','tr'=>$irs->costid,'ret'=>'numeric','options'=>'{class: "-money", placeholder: "-"}'),
							number_format($cr,2),
							$isItemEdit,
							'text')
					: '')
			. ($isDeletable ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('icar/cost/'.$carId.'/delcost/'.$irs->costid).'" data-rel="replace:#icar-tran" data-ret="'.url('icar/view/tran/'.$carInfo->tpid).'" data-confirm="ลบรายการนี้"><i class="icon -cancel -gray"></i></a></nav>' : ''),
			'config'=>$config,
		);

		$config['class'].=' -detail'.($isNewTran ? ' -newtran':'');

		$tables->rows[] = array(
			'<td></td>',
			'',
			'<td colspan="6">'
			.view::inlineedit(
				array(
					'group'=>'cost',
					'fld'=>'detail',
					'tr'=>$irs->costid,
					'options' => '{class: "-fill -wrap"}',
				),
				$irs->detail,
				$isItemEdit,
				'text'
			)
			.'</td>',
			'config' => $config,
		);
		//			$tables->rows[]=array('','','<td colspan="6">'.view::inlineedit(array('group'=>'cost','fld'=>'detail','tr'=>$irs->costid),$irs->detail,$isItemEdit,'text').'</td>');
		$costtotal+=$irs->amt;
		$interesttotal+=$interest;
	}
	if ($carInfo->saledate && !$show_saledate) {
		$tables->rows[]=array(
			'<td></td>',
			sg_date($carInfo->saledate,'d/m/Y'),
			'<td colspan="6"><strong>ลงบันทึกวันที่ขายเมื่อวันที่ '.sg_date($carInfo->saledate,'d/m/Y').'</strong></td>',
			'config'=>array('class'=>'saledate')
		);
		$show_saledate=true;
	}

	$tables->tfoot[]=array(
		'<td colspan="5">{tr:Total Cost}</td>',
		number_format($carInfo->interest,2),
		'',
		number_format($carInfo->costprice,2),
	);

	if ($carInfo->notcost && $carInfo->partner) {
		$tables->tfoot[]=array(
			'<td colspan="5">(หัก)ไม่คำนวณต้นทุน</td>',
			'',
			'',
			'('.number_format($carInfo->notcost,2).')'
		);

		$costtotal=$carInfo->costprice-$carInfo->notcost;

		$tables->tfoot[]=array(
			'<td colspan="5">{tr:Total Cost}</td>',
			number_format($carInfo->interest,2),
			'',
			number_format($costtotal,2)
		);
	}

	$ret.= $tables->build();
	$ret.= '<p>หมายเหตุ : คำนวณดอกเบี้ยตั้งแต่วันที่เกิดรายการค่าใช้จ่าย จนถึง วันที่ '.sg_date($carInfo->saledate,'ว ดด ปปปป').' ('.($carInfo->saledate?'วันที่ขาย':'วันนี้').')</p>';

	$ret .= '</div><!-- icar-tran -->';

	$ret.='<style text/css">
	.-wrap {white-space:normal;}
	</style>';
	//		$ret.=print_o($carInfo,'$carInfo');
	return $ret;
}
?>