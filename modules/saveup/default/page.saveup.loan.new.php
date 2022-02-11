<?php
/**
 * Recieve money from member
 *
 * @return String
 */
function saveup_loan_new($self) {
	R::View('saveup.toolbar',$self,'บันทึกกู้เงิน','loan');

	$post=(object)post('loan');
	if ($_POST) {
		if (empty($post->loandate)) $missing[]='วันที่';
		if (empty($post->mid)) $missing[]='รหัสสมาชิก/ชื่อสมาชิก';
		if ($post->glcode==-1) $missing[]='ประเภทเงินกู้';
		if (empty($post->total)) $missing[]='จำนวนเงิน';
		if ($missing) $error='กรุณาป้อนข้อมูลต่อไปในี้ให้ครบถ้วน : '.implode(' , ',$missing);
		if (!$error) {
			$loan->loanno=saveup_model::get_next_no('LON');
			$loan->loandate=$post->loandate;
			$loan->mid=$post->mid;
			$loan->uid=SG\getFirst(i()->uid,'FUNC.NULL');
			$loan->glcode=$post->glcode;
			$loan->total=$post->total;
			$loan->open_balance=$loan->balance=SG\getFirst($post->balance,$post->total);
			$loan->feerate=$post->feerate;
			$loan->period=$post->period;
			$loan->pay_per_period=$post->pay_per_period;
			$loan->created=date('U');
			$loan->memo=$post->memo;
			$stmt='INSERT INTO %saveup_loan% (`loanno`, `loandate`, `mid`, `uid`, `glcode`, `total`, `feerate`, `period`, `pay_per_period`, `open_balance`, `balance`, `created`, `memo` )
							VALUES
							(:loanno, :loandate, :mid, :uid, :glcode, :total, :feerate, :period, :pay_per_period, :open_balance, :balance, :created, :memo)';
			mydb::query($stmt,$loan);
//				$ret.='<p>'.mydb()->_query.'</p>';

			// Check card
			$glcode=mydb::select('SELECT `gltype`, `card` FROM %saveup_glcode% WHERE `glcode`=:glcode LIMIT 1',':glcode',$loan->glcode);
			// Save transaction to member card
			if ($glcode->card) {
				$memcard->mid=$loan->mid;
				$memcard->card=$glcode->card;
				$memcard->trno = NULL;
				$memcard->date=$loan->loandate;
				$memcard->refno=$loan->loanno;
				$memcard->amt=$loan->total;
				$stmt='INSERT INTO %saveup_memcard% (`mid`, `card`, `trno`, `date`, `refno`, `amt`) VALUES (:mid, :card, :trno, :date, :refno, :amt)';
				mydb::query($stmt,$memcard);
//					$ret.='<p>'.mydb()->_query.'</p>';
			}
			location('saveup/loan/view/'.$loan->loanno);
		}
	} else {
		$post->loandate=date('Y-m-d');
	}

	$ret.=message('error',$error);

	$form = new Form([
		'variable' => 'loan',
		'action' => url(q()),
		'id' => 'edit-loan',
		'children' => [
			'mid' => ['type' => 'hidden', 'value' => htmlspecialchars($post->mid)],
			/*
				$form->loanno->type='text';
				$form->loanno->label='เอกสารสัญญากู้ที่';
				$form->loanno->size=10;
				$form->loanno->require=true;
				$form->loanno->value=htmlspecialchars($post->loanno);
			*/
			'name' => [
				'type' => 'text',
				'label' => 'รหัสสมาชิก/ชื่อสมาชิก',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->name),
			],
			'glcode' => [
				'type' => 'select',
				'label' => 'ประเภทเงินกู้',
				'require' => true,
				'options' => (function() {
					$options = [-1 => '--- กรุณาเลือกประเภทเงินกู้ ---'];
					$glcodes = mydb::select('SELECT * FROM %saveup_glcode% WHERE `gltype` IN ("ASSEST","DEBT","REVENUE") AND `parent`="LOAN"');
					foreach ($glcodes->items as $crs) {
						$options[$crs->glcode] = $crs->desc;
					}
					return $options;
				})(),
				'value' => $post->glcode,
			],
			'total' => [
				'type' => 'text',
				'label' => 'ได้รับอนุมัติเงินกู้เป็นจำนวนเงิน (บาท)',
				'clas' => '-fill',
				'require' => true,
				'autocomplete' => 'OFF',
				'value' => htmlspecialchars($post->total),
			],
			'balance' => [
				'type' => 'text',
				'label' => 'คงเหลือค้างชำระ (บาท)',
				'class' => '-fill',
				'require' => true,
				'autocomplete' => 'OFF',
				'value' => htmlspecialchars($post->balance),
			],
			'loandate' => [
				'type' => 'text',
				'label' => 'อนุมัติเมื่อวันที่',
				'size' => 10,
				'require' => true,
				'value' => htmlspecialchars($post->loandate),
			],
			'feerate' => [
				'type' => 'text',
				'label' => 'อัตราค่าบำรุง (% ต่อ ปี)',
				'size' => 10,
				'maxlength' => 2,
				'autocomplete' => 'OFF',
				'value' => htmlspecialchars($post->feerate),
			],
			'period' => [
				'type' => 'text',
				'label' => 'กำหนดชำระคืน (งวด)',
				'size' => 10,
				'maxlength' => 3,
				'autocomplete' => 'OFF',
				'value' => htmlspecialchars($post->period),
			],
			'pay_per_period' => [
				'type' => 'text',
				'label' => 'ชำระคืนงวดละ (บาท)',
				'size' => 10,
				'autocomplete' => 'OFF',
				'value' => htmlspecialchars($post->pay_per_period),
			],
			'memo' => [
				'type' => 'textarea',
				'label' => 'บันทึก',
				'class' => '-fill',
				'rows' => 2,
				'value' => $post->memo,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>บันทึก</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

$ret.='
<script type="text/javascript">
$(document).ready(function() {
$("#edit-loan-loandate").datepicker({
	dateFormat: "yy-mm-dd",
	disabled: false,
	monthNames: thaiMonthName
});

$("#edit-loan-period").keyup(function() {
	var payPerPeriod=Math.ceil($("#edit-loan-total").val()/$("#edit-loan-period").val()).toFixed(2);
	notify(payPerPeriod);
	$("#edit-loan-pay_per_period").val(payPerPeriod);
});

$("#edit-loan-name").autocomplete({
		source: function(request, response){
			$.get(url+"saveup/api/member?n=50&q="+encodeURIComponent(request.term), function(data){
				response($.map(data, function(item){
				return {
					label: item.label,
					value: item.value
				}
				}))
			}, "json");
		},
		minLength: 2,
		dataType: "json",
		cache: false,
		select: function(event, ui) {
			this.value = ui.item.label;
			// Do something with id
			$("#edit-loan-mid").val(ui.item.value);
			return false;
		}
	});
});
</script>
<style>
#edit-loan-total,#edit-loan-period,#edit-loan-pay_per_period {text-align:center;}
</style>';
//		$ret.=print_o($post,'$post').print_o($rcvtr,'$rcvtr');
//		$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>