<?php
function saveup_treat_post($self) {
	if ($_POST['cancel']) location('saveup/treat/list');

	$payTypeList = saveup_var::$payType;

	R::View('saveup.toolbar',$self,'บันทึกรายการเบิกค่ารักษาพยาบาล','treat');

	$error=null;
	if (post('treat')) {
		$post=(object)post('treat',_TRIM+_STRIPTAG);
		if (empty($post->ref)) $error[]='กรุณาระบุ <em>เลขที่เอกสาร </em>';
		if ($post->ref && mydb::select('SELECT `tid` FROM %saveup_treat% WHERE `ref` = : ref LIMIT 1',':ref', $post->ref)->tid) $error[]='เลขที่เอกสาร '.$post->ref.' มีการบันทึกไปแล้ว';
		if (empty($post->date)) $error[]='กรุณาระบุ <em>วันทึ่อนุมัติ </em>';
		if (empty($post->mid)) $error[]='กรุณาระบุ <em>หมายเลขสมาชิก </em>';
		//if (empty($post->amount)) $error[]='กรุณาระบุ <em>จำนวนเงิน</em>';
		// start save new item
		$simulate=debug('simulate');
		if (!$error) {
			$post->amount = sg_strip_money($post->amount);
			$post->payfor = $payTypeList[$post->paytype];
			$post->uid=i()->uid;
			if (empty($post->paytype)) $post->paytype=NULL;
			$post->date=empty($post->date)?NULL:sg_date($post->date,'Y-m-d');
			$post->billdate=empty($post->billdate)?NULL:sg_date($post->billdate,'Y-m-d');
			$post->created='func.NOW()';

			$stmt=mydb::create_insert_cmd('saveup_treat',$post);
			mydb::query($stmt,$post);
			//$ret.=mydb()->_query;

			if ($simulate) {
				$ret.= '<p><strong>sql :</strong> '.db_query_cmd().'</p>';
				$ret.=print_o($post,'$post');
			} else if (mydb()->_error) {
				$ret.=message('error','มีความผิดพลาดในการเพิ่มข้อมูล กรุณาติดต่อผู้ดูแลเว็บไซท์ : error message<br />'.(user_access('access debugging program')?mydb()->_error:''));
			} else {
				$post->id=mydb()->insert_id;
				model::watch_log('saveup','SaveUp Treat Create','<a href="'.url('saveup/treat/view/'.$post->tid).'">member : '.$member->firstname.' '.$member->lastname.'</a> was created');
				location('saveup/treat/view/'.$post->id);
				return $ret;
			}
		}
	} else {
		$post=null;
		$last_ref=explode('-',mydb::select('SELECT MAX(ref) last_ref FROM %saveup_treat% LIMIT 1')->last_ref);
		$last_ref[1]=sprintf('%02d',$last_ref[1]+1);
		$post->ref=implode('-',$last_ref);
		$post->date=mydb::select('SELECT MAX(`date`) last_date FROM %saveup_treat% LIMIT 1')->last_date;
	}

	if ($error) $ret.=message('error',$error);
	$form=R::View('saveup.treat.form',$post);
	$ret .= $form->build();

	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-treat-name").focus()
	});
	</script>';

	return $ret;
}
?>