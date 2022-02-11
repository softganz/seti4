<?php
/**
 * Recieve money from member
 *
 * @return String
 */
function saveup_rcv_money($self) {
	R::View('saveup.toolbar',$self,'บันทึกการรับเงิน','rcv');

	$paymentId = post('payment');

	//$balanceResult = R::Model('saveup.loan.update.balance', 'LON0000068');
	//$ret .= print_o($balanceResult, '$balanceResult');

	$post = (object) post('rcv');
	$post->rcvtr = post('rcvtr');

	if ($post && !empty($post->rcvtr)) {
		if (empty($post->rcvdate)) $missing[] = 'วันที่รับเงิน';
		if ($missing) $error = 'กรุณาป้อนข้อมูลต่อไปในี้ให้ครบถ้วน : '.implode(',',$missing);

		if (!$error) {
			$total = 0;
			$rcvtrList = array();
			foreach ($post->rcvtr['mid'] as $key => $mid) {
				if ($mid && $post->rcvtr['name'][$key] && $post->rcvtr['glcode'][$key] && $post->rcvtr['amt'][$key]) {
					$amt = sg_strip_money($post->rcvtr['amt'][$key]);
					$rcvtrList[] = array(
										'period'=>$postRcvtr['period'][$key],
										'mid'=>$mid,
										'name'=>$post->rcvtr['name'][$key],
										'glcode'=>$post->rcvtr['glcode'][$key],
										'loanno'=>$post->rcvtr['loanno'][$key],
										'amt'=>$amt,
										);
					$total += $amt;
				}
			}

			if (empty($rcvtrList)) return message('error','No Transaction');
			//$ret .= 'Total = '.number_format($total,2).' บาท<br />'.print_o($rcvtrList,'$rcvtrList');


			// Save transaction master
			$rcvmast->rcvno = saveup_model::get_next_no('RCV');
			$rcvmast->rcvdate = sg_date($post->rcvdate,'Y-m-d');
			$rcvmast->uid = i()->uid;
			$rcvmast->dbac = '';
			$rcvmast->period = $post->period;
			$rcvmast->total = $total;
			$rcvmast->memo = $post->memo;
			$rcvmast->transamt = sg_strip_money($post->transamt);
			$rcvmast->transby = $post->transby;
			$rcvmast->created = date('U');
			$stmt = 'INSERT INTO %saveup_rcvmast%
							(`rcvno`, `rcvdate`, `period`, `total`, `uid`, `dbac`, `transamt`, `transby`, `created`, `memo` )
							VALUES
							(:rcvno, :rcvdate, :period, :total, :uid, :dbac, :transamt, :transby, :created, :memo)';
			mydb::query($stmt,$rcvmast);

			$rcvmast->rcvid = mydb()->insert_id;

			$ret .= '<h3>SAVE Recieve Master</h3>'.print_o($rcvmast,'$rcvmast').'<p>'.mydb()->_query.'</p>';

			if ($post->payment) {
				$stmt = 'UPDATE %saveup_log% SET `process` = :rcvid WHERE `lid` = :lid LIMIT 1';
				mydb::query($stmt, ':lid', $post->payment, ':rcvid', $rcvmast->rcvid);
				$ret .= '<p>'.mydb()->_query.'</p>';
			}

			$rcvInfo = R::Model('saveup.rcv.get', $rcvmast->rcvid);
			$result = R::Model('saveup.rcv.tran.add', $rcvInfo, $rcvtrList);

			/*
			$tranNo = 0;
			foreach ($rcvtrList as $rcvtr) {
				$rcvtr = (object) $rcvtr;

				++$tranNo;


				// Save recieve transaction
				$rcvtr->rcvid = $rcvmast->rcvid;
				$rcvtr->rcvno = $rcvmast->rcvno;
				if (empty($rcvtr->loanno)) $rcvtr->loanno = NULL;
				$stmt = 'INSERT INTO %saveup_rcvtr%
								(`rcvid`, `rcvno`, `refno`, `mid`, `glcode`, `amt`)
								VALUES
								(:rcvid, :rcvno, :loanno, :mid, :glcode, :amt )';
				mydb::query($stmt, $rcvtr);
				$rcvtr->aid = mydb()->insert_id;

				$ret .= '<h3>SAVE Recieve Transaction #'.$tranNo.'</h3>'.print_o($rcvtr, '$rcvtr').'<p>'.mydb()->_query.'</p>';

				// Check card
				$glcode = mydb::select('SELECT `gltype`, `card` FROM %saveup_glcode% WHERE `glcode` = :glcode LIMIT 1', ':glcode', $rcvtr->glcode);


				// Save transaction to member card
				if ($glcode->card) {
					$memcard->mid = $rcvtr->mid;
					$memcard->card = $glcode->card;
					$memcard->trno = $rcvtr->aid;
					$memcard->date = $rcvmast->rcvdate;
					$memcard->refno = $rcvmast->rcvno;
					switch ($glcode->gltype) {
						case 'ASSEST' : $memcard->amt = -$rcvtr->amt; break;
						case 'DEBT' : $memcard->amt = $rcvtr->amt; break;
						default : $memcard->amt = $rcvtr->amt;
					}
					$stmt = 'INSERT INTO %saveup_memcard%
									(`mid`, `card`, `trno`, `date`, `refno`, `amt`)
									VALUES
									(:mid, :card, :trno, :date, :refno, :amt)';
					mydb::query($stmt,$memcard);
					$ret .= '<p>'.mydb()->_query.'</p>';
				}

				// Update loan balance
				if ($rcvtr->loanno AND substr($rcvtr->glcode,0,5) == 'LOAN-') {
					$balanceResult = R::Model('saveup.loan.update.balance', $rcvtr->loanno);
					$ret .= '<p><b>Calculate Loan Balance of '.$rcvtr->loanno.'</b></p>'.print_o($balanceResult, '$balanceResult');
				}
			}
			*/
			//$ret .= print_o($post,'post');
			location('saveup/rcv/'.$rcvmast->rcvid);
			return $ret;
		}
	} else {
		$post->rcvdate = date('Y-m-d');
		if ($paymentId) {
			$stmt = 'SELECT * FROM %saveup_log% WHERE `lid` = :lid LIMIT 1';
			$paymentInfo = mydb::select($stmt, ':lid', $paymentId);
			if ($paymentInfo->process > 0) return message('error', 'รายการแจ้งโอนเงินนี้ได้ทำบันทึกการเรับเงินเรียบร้อยแล้ว : <a href="'.url('saveup/rcv/'.$paymentInfo->process).'">รายละเอียดใบรับเงิน</a>');
			$post->total = $paymentInfo->amt;
			$post->transby = $paymentInfo->poster;
			$post->memo = $paymentInfo->detail;
			$post->paymentId = $paymentInfo->lid;
		}
	}

	$ret .= message('error', $error);

	$form = new Form([
		'variable' => 'rcv',
		'action' => url(q()),
		'id' => 'edit-rcv',
		'class' => 'sg-form -rcv-form -sg-flex',
		'checkValid' => true,
		'data-codeurl' => url('saveup/api/member/glcode'),
	]);

	$form->addField('payment', array('type'=>'hidden', 'value'=>$post->paymentId));

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
			'value' => htmlspecialchars(SG\getFirst($post->period,date('Y-m')))
		)
	);

	$form->addField(
		'rcvdate',
		array(
			'type' => 'text',
			'label' => 'วันที่รับเงิน',
			'class' => 'sg-datepicker -date -fill',
			'require' => true,
			'value' => htmlspecialchars(sg_date($post->rcvdate,'d/m/Y'))
		)
	);

	$form->addField(
		'transamt',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงินโอน(บาท)',
			'class' => '-money -fill',
			'value' => htmlspecialchars($post->total),
			'placeholder' => '0.00'
		)
	);
	$form->addField(
		'transby',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้โอนเงิน',
			'class' => '-fill',
			'value' => htmlspecialchars($post->transby),
			'placeholder' => 'ระบุชื่อผู้โอนเงิน'
		)
	);

	/*
	$stmt='SELECT *,
					CASE `gltype`
						WHEN "DEBT" THEN 0
						ELSE 1
					END AS `orderLabel`
				FROM %saveup_glcode%
				WHERE `gltype` IN ("ASSEST","DEBT","REVENUE")
					AND (`glcode` LIKE "LOAN-%" OR `glcode` LIKE "SAVING-%" OR `glcode` LIKE "REVENUE-%")
					AND `parent` IS NOT NULL
				ORDER BY `orderLabel`';
	$glcodes=mydb::select($stmt);
	//$ret.=print_o($glcodes,'$glcodes');
	foreach ($glcodes->items as $rs) {
		$glCodeLists[$rs->gltype][$rs->glcode]=$rs;
	}
	//$ret.=print_o($glCodeLists,'$glCodeLists');
	*/

	$tables = new Table();
	$tables->addClass('-saveup-rcv');
	$tables->thead = array(
		'mid' => 'รหัส/ชื่อสมาชิก',
		'glcode' => 'ชื่อบัญชี',
		'money' => 'จำนวนเงิน',
	);

	$tables->rows[] = array(
		'<input type="hidden" name=rcvtr[mid][] value="'.$post->rcvtr[$i]['mid'].'" /><input type="text" class="form-text saveup-name sg-autocomplete -fill" name=rcvtr[name][] value="'.$post->rcvtr[$i]['name'].'" placeholder="00-000" data-query="'.url('saveup/api/member').'" data-select="label" data-callback="saveupRcvMemberSelect" />',
		'<select class="form-select saveup-rcvtr-glcode -fill" name="rcvtr[glcode][]" >'.$sel_gl.'</select><input class="saveup-rcvtr-loanno" type="hidden" name="rcvtr[loanno][]" value="" />',
		'<input type="text" class="form-text saveup-rcvamt -fill -money" name="rcvtr[amt][]" value=""  placeholder="0.00" autocomplete="OFF" />'
	);

	/*
	for ($i=0;$i<10;$i++) {
		$sel_gl='<option value="-1">--- เลือกบัญชี ---</option>';
		foreach ($glCodeLists as $glCodeGroupKey => $glCodeGroupList) {
			$sel_gl.='<optgroup label="'.$glCodeGroupKey.'">';
			foreach ($glCodeGroupList as $j=>$crs) {
				$sel_gl.='<option value="'.$crs->glcode.'"'.($post->rcvtr[$i]['glcode']==$crs->glcode?' selected="selected"':'').'>'.$crs->desc.' ['.$crs->glcode.'] </option>';
			}
			$sel_gl.='</optgroup>';
		}
		*/
		/*
		foreach ($glcodes->items as $j=>$crs) {
			$sel_gl.='<option value="'.$crs->glcode.'"'.($post->rcvtr[$i]['glcode']==$crs->glcode?' selected="selected"':'').'>'.$crs->desc.' ['.$crs->glcode.'] </option>';
		}
		*/
		/*
		$amt=preg_replace('/[^0-9\.]/','',$post->rcvtr[$i]['amt']);
		$tables->rows[]=array(
											'<input type="hidden" name=rcvtr['.$i.'][mid] value="'.$post->rcvtr[$i]['mid'].'" /><input type="text" class="form-text saveup-name sg-autocomplete -fill" name=rcvtr['.$i.'][name] value="'.$post->rcvtr[$i]['name'].'" placeholder="00-000" data-query="'.url('saveup/api/member').'" data-callback="saveupRcvMemberSelect" />',
											'<select class="form-select saveup-rcvtr -fill" name=rcvtr['.$i.'][glcode]>'.$sel_gl.'</select>',
											'<input type="text" class="form-text saveup-rcvamt -fill -money" name=rcvtr['.$i.'][amt] value="'.number_format($amt,2).'"  placeholder="0.00" />'
											);
	}
	*/

	$tables->tfoot[] = array('','รวม','<span id="rcv-total">'.number_format(0,2).'</span>');

	$form->addText($tables->build());

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="btn -link -cancel" href="'.url('saveup/rcv').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-full -sg-text-right"}',
		)
	);

	$form->addField(
		'memo',
		array(
			'type' => 'textarea',
			'label' => 'หมายเหตุ',
			'class' => '-fill',
			'rows' => $post->memo ? 14 : 4,
			'value' => htmlspecialchars($post->memo),
			'container' => '{class: "-full"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<ul><li>การเพิ่มแถวใหม่ ให้เพิ่มเฉพาะกรณีที่รายการสุดท้ายไม่ว่างเท่านั้น</li><li>ช่องรวมเงิน ให้รวมเงินทั้งหมด</li><li>เลือกอ้างอิงจากบันทึกการโอนเงิน</li><li>บันทึกข้อมูลเสร็จให้เคลียร์ข้อมูลหรือเปลี่ยน page</li></ul>';


	$ret .= '<div id="result"></div>';


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
	//		$ret.=print_o($post,'$post').print_o($rcvtr,'$rcvtr');
	//		$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>