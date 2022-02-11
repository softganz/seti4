<?php
/**
 * Rander note unit
 *
 * @param Record Set $needInfo
 * @return String
 */
function view_imed_need_render($needInfo, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, ref: "web"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$isEdit = $options->showEdit && (is_admin('imed') || i()->uid == $needInfo->uid);

	$needId = SG\getFirst($needInfo->needid);

	$urgencyList = array(1 => 'รอได้', 5 => 'เร่งด่วน', 9=> 'เร่งด่วนมาก');

	// $ret .= print_o($needInfo,'$needInfo');

	$headerUi = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action -disabled" href="'.url('imed/app/need/'.$needInfo->psnid.'/edit/'.$needId).'" data-rel="#noteUnit-'.$needId.' .summary"><i class="icon -edit"></i>แก้ไข</a>');
		$dropUi->add('<a class="sg-action" href="'.url('imed/api/visit/'.$needInfo->psnid.'/need.delete/'.$needInfo->seq, array('id'=>$needId)).'" title="ลบความต้องการทิ้ง" data-rel="notify" data-confirm="ลบความต้องการทิ้ง'._NL._NL.'กรุณายืนยัน?" data-removeparent="div.imed-my-note>.ui-item"><i class="icon -delete"></i>ลบรายการ</a>');
	}

	if ($dropUi->build()) $headerUi->add(sg_dropbox($dropUi->build()));

	switch ($options->ref) {
		case 'app':
			$patientUrl = '<a class="sg-action" href="'.url('imed/app/'.$needInfo->psnid).'" data-webview="'.$needInfo->patient_name.'">';
			$posterUrl = '<a class="sg-action" href="'.url('imed/u/'.$needInfo->uid,['ref' => 'app']).'" data-rel="box" data-webview="'.$needInfo->name.'" data-width="480" data-height="80%">';
			break;

		case 'psyc':
			$patientUrl = '<a class="sg-action" href="'.url('imed/psyc/'.$needInfo->psnid).'" data-webview="'.$needInfo->patient_name.'">';
			$posterUrl = '<a class="sg-action" href="'.url('imed/u/'.$needInfo->uid,['ref' => 'psyc']).'" data-rel="box" data-webview="'.$needInfo->name.'" data-width="480" data-height="80%">';
			break;

		default:
			$patientUrl = '<a class="sg-action" href="'.url('imed/patient/'.$needInfo->psnid.'/view').'" data-rel="box" data-width="480" data-max-height="80%" x-role="patient" data-pid="'.$needInfo->psnid.'">';
			$posterUrl = '<a class="sg-action" href="'.url('imed/u/'.$needInfo->uid).'" data-rel="box" data-width="480">';
			break;
	}

	$ret .= '<div class="header">'._NL
	 . '<span>'
	 . $posterUrl
	 . '<img class="poster-photo" src="'.model::user_photo($needInfo->username).'" width="32" height="32" alt="" />'
	 . '<span class="poster-name">'.$needInfo->name.'</span></a>'
	 . '<span class="timestamp"> เมื่อ '.sg_date($needInfo->created,'ว ดด ปป H:i').' น.</span>'
	 . '</span>';

	//$ret .= '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>';
	$ret.='</div><!-- header -->'._NL;

	/*
	if ($isEdit) {
		$ui=new Ui();
		$ui->add('<a class="sg-action -disabled" href="'.url('imed/app/need/'.$needInfo->psnid.'/edit/'.$needId).'" data-rel="#noteUnit-'.$needId.' .summary"><i class="icon -edit"></i>แก้ไข</a>');
		$ui->add('<a class="sg-action" href="'.url('imed/app/need/'.$needInfo->psnid.'/delete/'.$needId).'" title="ลบความต้องการทิ้ง" data-rel="notify" data-confirm="ลบความต้องการทิ้ง'._NL._NL.'กรุณายืนยัน?" data-removeparent="div.noteUnit"><i class="icon -delete"></i>ลบบันทึกข้อความ</a>');
		$ret.=sg_dropbox($ui->build(),'{class:"leftside noteUnitMenu"}');
	}
	*/

	/*
	$ret .= '<div class="imed-needtype -type-'.$needInfo->needtype.'">ต้องการ'.$needInfo->needTypeName.'<br />('.$urgencyList[$needInfo->urgency].')'.'</div>';
	$ret.='<div class="detail -sg-clearfix"><p>'.str_replace("\n",'<br />',SG\getFirst($needInfo->detail,$needInfo->rx)).'</p></div><!-- detail -->'._NL;


	$cardUi = new Ui();
	if ($isEdit) {
		$cardUi->add('<a class="sg-action" href="'.url('imed/api/visit/'.$needInfo->psnid.'/need.status/'.$needInfo->seq, array('id'=>$needInfo->needid)).'" data-rel="'._IMED_RESULT.'" data-ret="'.url('imed/visit/'.$needInfo->psnid.'/need/'.$needInfo->seq).'">'
			. '<i class="icon -material -circle '.($needInfo->status ? '-green' : '-gray').'">'.($needInfo->status ? 'done_all' : 'done').'</i> '
			. '<span>ช่วยเหลือ</span></a>');
	}

	$dropUi = new Ui();

	if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));

	if ($cardUi->count()) $ret .= '<nav class="nav -card">'.$cardUi->build().'</nav>';
	*/

	$ui = new Ui(NULL, 'ui-card -need');

	$ui->add('<div class="detail -hover-parent">'
			. ($needInfo->psnid?'<div style="padding: 0 0 16px 0;">ความต้องการของ '.$patientUrl.$needInfo->patient_name.'</a></div>':'')
			. '<a class="sg-action -status" '.($isEdit ? 'href="'.url('imed/api/visit/'.$needInfo->psnid.'/need.status/'.$needInfo->seq, array('id'=>$needInfo->needid)).'" data-rel="replace:#noteUnit-'.$needInfo->seq.'" data-ret="'.url('imed/visit/'.$needInfo->psnid.'/need.view/'.$needInfo->seq,array('id'=>$needInfo->needid,'ref'=>$options->ref)) : '').'">'
			. '<i class="icon -material">'.($needInfo->status ? 'done_all' : 'done').'</i> '
			. '</a> '
			. '<span>ต้องการ '.$needInfo->needTypeName.' '
			. ($needInfo->status ? 'ดำเนินการเรียบร้อย' : $urgencyList[$needInfo->urgency]).'</span>'
			. '<nav class="nav -icons -hover">'
		//	. ($isEdit ? '<a class="sg-action" href="'.url('imed/visit/'.$needInfo->psnid.'/form.need/'.$needInfo->seq, array('id'=>$needInfo->needid)).'" data-rel="box" data-width="640"><i class="icon -material -gray">edit</i></a>' : '')
		//	. ($isEdit ? '<a class="sg-action" href="'.url('imed/api/visit/'.$needInfo->psnid.'/need.delete/'.$needInfo->seq, array('id'=>$needInfo->needid)).'" data-rel="none" data-removeparent="li" data-title="ลบความต้องการ" data-confirm="ต้องการลบความต้องการ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
			. '</nav>'
			. ($needInfo->detail ? '<p>('.nl2br($needInfo->detail).')</p>' : '')
			. '</div>'
			, '{class: "urgency -level-'.$needInfo->urgency.' '.($needInfo->status ? '-done' : '-wait').'"}'
		);
	$ret .= $ui->build();
	//$ret .= print_o($needInfo,'$needInfo');
	return $ret;
}
?>