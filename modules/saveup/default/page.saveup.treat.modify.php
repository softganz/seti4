<?php
function saveup_treat_modify($self,$tid) {
	if ($_POST['cancel']) location('saveup/treat/view/'.$tid);

	$rs=saveup_model::get_treat_by_id($tid);

	if ($rs->_empty) return message('error',$this->theme->title='รายการเบิกค่ารักษาพยาบาล #'.$tid.' ไม่มี.');
	R::View('saveup.toolbar',$self,$rs->mid.' : '.$rs->name.' - แก้ไขรายการเบิกค่ารักษาพยาบาล','treat',$rs);

	$error=null;
	if (post('treat')) {
		$post=(object)post('treat',_TRIM+_STRIPTAG);
		if (empty($post->ref)) $error[]='field <em>เลขที่เอกสาร </em> require';
		if (empty($post->mid)) $error[]='field <em>หมายเลขสมาชิก </em> require';
		if (empty($post->date)) $error[]='field <em>วันที่อนุมัติ </em> require';
		//if (empty($post->amount)) $error[]='field <em>จำนวนเงิน</em> require';

		// start save new item
		$simulate=debug('simulate');
		if (!$error) {
			$post->amount = sg_strip_money($post->amount);
			$post->date=empty($post->date)?NULL:sg_date($post->date,'Y-m-d');
			$post->billdate=empty($post->billdate)?NULL:sg_date($post->billdate,'Y-m-d');

			$stmt=mydb::create_update_cmd('%saveup_treat%',$post,'`tid`=:tid');
			mydb::query($stmt,$post);
			//$ret.=mydb()->_query;

			if ($simulate) {
				$ret.= '<p><strong>sql :</strong> '.mydb()->_query.'</p>';
				return $ret;
			} else {
				model::watch_log('saveup','SaveUp Treat Modify','<a href="'.url('saveup/treat/view/'.$rs->tid).'">member : '.$rs->name.'</a> was modified');
				location('saveup/treat/view/'.$tid);
			}
		}
	} else {
		$post=clone($rs);
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