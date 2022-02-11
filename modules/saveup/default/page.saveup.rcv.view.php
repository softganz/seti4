<?php
/**
 * View recieve
 *
 * @param String $rcvId
 * @return String
 */
function saveup_rcv_view($self, $rcvId, $action = NULL) {
	$rcvInfo = is_object($rcvId) ? $rcvId : R::Model('saveup.rcv.get',$rcvId);
	$rcvId = $rcvInfo->rcvid;

	R::View('saveup.toolbar',$self,'ใบรับเงิน - '.$rcvInfo->rcvno,'rcv',$rcvInfo);

	if ( !$rcvInfo ) return message('error','ไม่มีใบรับเงินตามที่ระบุ');

	//$result = R::Model('saveup.rcv.update.total', $rcvId);
	//$ret .= print_o($result,'$result');

	$isEdit = $action == 'edit';

	$form = new Form([
		'variable' => 'rcv',
		'action' => url('saveup/rcv/'.$rcvId.'/tr.add'),
		'class' => 'sg-form -rcv-form',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'load:#main:'.url('saveup/rcv/'.$rcvId),
		'data-codeurl' => url('saveup/api/member/glcode'),
	]);


	if ($isEdit) {
		$form->addText('<div class="box -sg-flex">');
		$periodOption = array();
		for ($i=0; $i<=24; $i++) {
			$month = date('Y-m',strtotime('-'.$i.' month'));
			$periodOption[$month] = sg_date($month.'-01', 'm-ปปปป');
		}
		$form->addField(
			'period',
			array(
				'type' => 'select',
				'label' => 'สำหรับงวด:',
				'class' => '-fill',
				'require' => true,
				'options' => $periodOption,
				'value' => $rcvInfo->period,
			)
		);

		$form->addField(
			'rcvdate',
			array(
				'type' => 'text',
				'label' => 'วันที่รับเงิน',
				'class' => 'sg-datepicker -date -fill',
				'require' => true,
				'value' => sg_date($rcvInfo->rcvdate,'d/m/Y')
			)
		);

		$form->addField(
			'transamt',
			array(
				'type' => 'text',
				'label' => 'จำนวนเงินโอน(บาท)',
				'class' => '-money -fill',
				'value' => $rcvInfo->transamt,
				'placeholder' => '0.00'
			)
		);
		$form->addField(
			'transby',
			array(
				'type' => 'text',
				'label' => 'ชื่อผู้โอนเงิน',
				'class' => '-fill',
				'value' => $rcvInfo->transby,
				'placeholder' => 'ระบุชื่อผู้โอนเงิน'
			)
		);
		$form->addText('</div>');
	} else {
		$tables = new Table();
		$tables->addClass('box');
		$tables->id = 'rcvmast';
		$tables->rows[] = array(
			'เลขที่',$rcvInfo->rcvno,
			'ประจำงวด',sg_date($rcvInfo->period.'-01','ดด ปปปป'),
			'วันที่รับเงิน',sg_date($rcvInfo->rcvdate,'ว ดด ปปปป'),
			'รวมเงิน',number_format($rcvInfo->total,2).' บาท'
		);
		if ($rcvInfo->refno) {
			if (substr($rcvInfo->refno,0,3) == 'LON')
				$refurl = url('saveup/loan/view/'.$rcvInfo->refno);
			$tables->rows[] = array(
				'อ้างอิง',
				($refurl?'<a href="'.$refurl.'">':'').$rcvInfo->refno.($refurl?'</a>':'')
			);
		}
		$ret .= $tables->build();
	}



	$tables = new Table();
	$tables->addClass('-saveup-rcv');
	$tables->id = 'rcvtr';
	$tables->thead = array(
		'no'=>'ลำดับ',
		'ชื่อสมาชิก',
		'รายละเอียด',
		'amount -money -hover-parent'=>'จำนวนเงิน'
	);
	$no = 0;
	foreach ($rcvInfo->trans as $rs) {
		if ($isEdit) {
			$ui = new Ui('span');
			//$ui->add('<a href="'.url('saveup/rcv/'.$rcvId.'/view/edittr/'.$rs->aid).'"><i class="icon -edit"></i></a>');
			$ui->add('<a class="sg-action" href="'.url('saveup/rcv/'.$rcvId.'/tr.remove/'.$rs->aid).'" data-rel="#main" data-ret="'.url('saveup/rcv/'.$rcvId.'/view/edit').'" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel"></i></a>');
			$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
		}
		$tables->rows[] = array(
			++$no,
			'<a href="'.url('saveup/member/view/'.$rs->mid).'">'.$rs->name.'</a><br /><em class="-low-priority">'.$rs->mid.'</em>',
			$rs->desc.'<br /><em class="-low-priority">'.$rs->glcode
			. (preg_match('/^LON/i',$rs->refno) ? ' <a href="'.url('saveup/loan/view/'.$rs->refno).'">'.$rs->refno.'</a>' : '')
			.'</em>',
			number_format($rs->amt,2)
			.'<input class="saveup-rcvamt" type="hidden" value="'.$rs->amt.'" />'
			.$menu,
		);
		$total += $rs->amt;
	}


	if ($isEdit) {
		$tables->rows[]=array(
			'<td align="center">+</td>',
			'<input type="hidden" name=rcvtr[mid][] value="'.$post->rcvtr[$i]['mid'].'" /><input type="text" class="form-text saveup-name sg-autocomplete -fill" name=rcvtr[name][] value="'.$post->rcvtr[$i]['name'].'" placeholder="00-000" data-query="'.url('saveup/api/member').'" data-select="label" data-callback="saveupRcvMemberSelect" />',
			'<select class="form-select saveup-rcvtr-glcode -fill" name="rcvtr[glcode][]" >'.$sel_gl.'</select><input class="saveup-rcvtr-loanno" type="hidden" name="rcvtr[loanno][]" value="" />',
			'<input type="text" class="form-text saveup-rcvamt -fill -money" name="rcvtr[amt][]" value=""  placeholder="0.00" autocomplete="OFF" />'
		);
	}

	$totalError = number_format($total,2) != number_format($rcvInfo->total,2) ? '-error' : '';
	$tables->tfoot[] = array(
		'<td></td>',
		'',
		'รวมทั้งสิ้น',
		'<span id="rcv-total">'
		.'<strong class="'.$totalError.'">'
		.number_format($total,2).($totalError ? '! <a class="sg-action" href="'.url('saveup.rcv/'.$rcvId.'/recalculate').'" data-rel="#main" data-ret="'.url('saveup/rcv/'.$rcvId).'"><i class="icon -material">refresh</i></a>' : '')
		.'</strong>'
		.'</span>',
		'config' => array(
			'class' => $total != $rcvInfo->total ? '-error' : '',
		),
	);

	$form->addText($tables->build());

	if ($rcvInfo->memo) {
		$form->addText('<p>บันทึก '
			. ($rcvInfo->transferId ? '<a class="sg-action" href="'.url('saveup/payment/view/'.$rcvInfo->transferId).'" data-rel="box">รายละเอียดการโอนเงิน</a>' : '')
			. ': <br />'.nl2br($rcvInfo->memo)
			. '</p>');
	}

	if ($isEdit) {
		$form->addField('save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'container' => array('class' => '-sg-text-right'),
			)
		);
	} else {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="sg-action btn -floating -circle48" href="'.url('saveup/rcv/'.$rcvId.'/view/edit').'" data-rel="#main"><i class="icon -edit -white"></i></a>'
			.'</div>';
	}

	$ret .= $form->build();

	$ret .= '
	<script type="text/javascript">
	$(document).on("change",".saveup-rcvtr-glcode",function() {
		var $option = $(this).find(":selected")
		var $loanno = $(this).closest("tr")
									.find(".saveup-rcvtr-loanno")
									.val($option.data("loanno"))
		var $amount = $(this).closest("tr").find(".saveup-rcvamt").val($option.data("pay-amt")).focus()
	})

	$(document).on("change keyup", ".saveup-rcvamt, .saveup-rcvtr-glcode", function() {
		var itemTotal = 0.00
		$(".saveup-rcvamt").each(function(i){
			var itemValue = $(this).val().sgMoney(2)
			//console.log("item value = ",itemValue)
			if (isNaN(itemValue)) itemValue = 0
			itemTotal += itemValue
		});
		//console.log("Total = ",itemTotal, typeof itemTotal)

		$("#rcv-total").text(itemTotal.toFixed(2))
	})
	</script>
	';

	//$ret .= print_o($rcvInfo,'$rcvInfo');

	return $ret;
}
?>