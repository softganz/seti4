<?php
/**
 * Order and claim monitor
 *
 * @param String $action
 * @param Integer $id
 * @return String
 */
function ibuy_status_monitor($self, $action = NULL, $id = NULL) {
	$self->theme->title='Order monitor @ '.date('Y-m-d H:i:s');
	if ($action=='transfer') {
		$ret.=__ibuy_status_monitor_confirm_transfer($id);
	}

	$ret.='<div id="ibuy-monitor-order">'._NL;
	$ret.='<h3>'.$self->theme->title.'</h3>';

	$ret.=R::View('ibuy.status.monitor.list');

	$ret.='<h3>'.$self->theme->title.'</h3>';
	$ret.='</div>'._NL;



	head('<script type="text/javascript">
$(document).ready(function() {
  (function request() {
		if($("#ibuy-monitor-order").attr("id")=="ibuy-monitor-order") {
			notify("Loading....");
			$.get("'.url('ibuy/status/monitor','t="+Date.UTC()').',function(html) {
				notify();
				$("#ibuy-monitor-order").html(html);
				$("h2.title").html($("h3").html());
			});
		}
    //calling the anonymous function after refresh time in milli seconds
    setTimeout(request, '.cfg('ibuy.monitor.time').'*1000);  //second
  })(); //self Executing anonymous function
});
</script>'
	);
	return $ret;
}

/**
 * Confirm money transfer
 *
 * @param Integer $lid log id
 * @return String
 */
function __ibuy_status_monitor_confirm_transfer($lid) {
	if (empty($lid)) return;

	$rs = mydb::select('SELECT l.*,f.custname FROM %ibuy_log% l LEFT JOIN %ibuy_customer% f ON f.uid=l.uid WHERE lid=:lid LIMIT 1',':lid',$lid);
	if (post('log')) {
		$post = (Object) post('log');
		$post->amt=str_replace(',','',$post->amt);

		ibuy_model::log(
			'keyword=order','kid='.$rs->kid,
			'detail='.ibuy_define::status_text(__IBUY_STATUS_TRANSFER).($post->detail?' '.$post->detail:''),
			'amt=-'.$post->amt,'status=20','process=1'
		);

		mydb::query('UPDATE %ibuy_log% SET process=1,amt=NULL WHERE lid=:lid LIMIT 1',':lid',$lid);
		mydb::query('UPDATE %ibuy_order% SET balance=IF(balance-:amt>0,balance-:amt,0) , status='.__IBUY_STATUS_TRANSFER.' WHERE oid='.$rs->kid.' LIMIT 1',':amt',$post->amt);

		location('ibuy/status/monitor');
	} else {
		$ret .= '<header class="header -box"><h3>บันทึกยืนยันการโอนเงิน</h3></header>';

		$ret.='<p>ได้รับเงินโอนจาก <strong>'.$rs->custname.'</strong> จำนวน <strong>'.number_format(abs($rs->amt),2).'</strong> บาท '.$rs->detail.'</p>';

		$form = new Form('log', url(q()), 'ibuy-confirm');

		$form->addField(
			'amt',
			array(
				'type' => 'text',
				'label' => 'จำนวนเงินที่ได้รับโอน (บาท):',
				'class' => '-money',
				'require' => true,
				'value' => number_format(abs($rs->amt),2),
			)
		);

		$form->addField(
			'detail',
			array(
				'type' => 'text',
				'label' => 'บันทึกข้อความ :',
				'class' => '-fill',
			)
		);

		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>บันทึกรับทราบการโอนเงิน</span>',
			'pretext' => '<a class="btn -link -cancel" href="'.url('ibuy/status/monitor').'"><i class="icon -material -gray">keyboard_arrow_left</i>'.tr('Back').'</a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);
		$ret .= $form->build();
	}
	return $ret;
}

?>